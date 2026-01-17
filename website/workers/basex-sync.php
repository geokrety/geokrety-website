<?php

declare(strict_types=1);

/**
 * GeoKrety BaseX Sync Worker.
 *
 * RabbitMQ consumer listening to the 'geokrety' exchange and updating the BaseX
 * exports database using a XQuery script when GeoKret related entities change.
 */
require_once __DIR__.'/../init-f3.php';

use Caxy\BaseX\Session as BaseXSession;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\Move;
use GeoKrety\Model\MoveComment;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPConnectionException;
use PhpAmqpLib\Message\AMQPMessage;

class BaseXSyncWorker {
    private $connection;
    private $channel;
    private $retryDelay = 1;
    private $maxRetryDelay = 60;
    private $logLevel;
    private $basexSession;
    private $queryScript;
    private $gkApiBaseUrl;
    private $rateLimitsBypass;
    private $shortLivedSessionToken;
    private $processedCache = [];
    private $cacheTtl = 1; // seconds

    public const LOG_DEBUG = 100;
    public const LOG_INFO = 200;
    public const LOG_WARNING = 300;
    public const LOG_ERROR = 400;
    public const LOG_CRITICAL = 500;

    public function __construct() {
        $this->logLevel = $this->getLogLevelFromEnv(getenv('GK_NOTIFICATION_LOG_LEVEL') ?: 'INFO');
        $this->log(self::LOG_INFO, 'BaseX Sync Worker starting...');
        $queryPath = __DIR__.'/xquery/update.xq';
        if (!file_exists($queryPath)) {
            $this->log(self::LOG_CRITICAL, 'BaseX XQuery script not found', ['path' => $queryPath]);
            throw new RuntimeException('BaseX XQuery script not found: '.$queryPath);
        }
        $this->queryScript = file_get_contents($queryPath);
        $this->gkApiBaseUrl = GK_SITE_API_SERVER_FQDN;
        $this->rateLimitsBypass = GK_RATE_LIMITS_BYPASS;
        $this->shortLivedSessionToken = GK_SITE_SESSION_SHORT_LIVED_TOKEN;
    }

    private function getLogLevelFromEnv(string $level): int {
        $levels = [
            'DEBUG' => self::LOG_DEBUG,
            'INFO' => self::LOG_INFO,
            'WARNING' => self::LOG_WARNING,
            'ERROR' => self::LOG_ERROR,
            'CRITICAL' => self::LOG_CRITICAL,
        ];

        return $levels[strtoupper($level)] ?? self::LOG_INFO;
    }

    private function log(int $level, string $message, array $context = []): void {
        if ($level < $this->logLevel) {
            return;
        }

        $levelNames = [
            self::LOG_DEBUG => 'DEBUG',
            self::LOG_INFO => 'INFO',
            self::LOG_WARNING => 'WARNING',
            self::LOG_ERROR => 'ERROR',
            self::LOG_CRITICAL => 'CRITICAL',
        ];

        $timestamp = date('Y-m-d H:i:s');
        $levelName = $levelNames[$level] ?? 'UNKNOWN';
        $contextStr = !empty($context) ? ' '.json_encode($context) : '';

        echo "[{$timestamp}] [{$levelName}] {$message}{$contextStr}\n";
        flush();
    }

    private function connect(): void {
        if (!GK_RABBITMQ_HOST || !GK_RABBITMQ_PORT) {
            throw new RuntimeException('RabbitMQ configuration not found. Check GK_RABBITMQ_* environment variables.');
        }

        $this->connection = new AMQPStreamConnection(
            GK_RABBITMQ_HOST,
            (int) GK_RABBITMQ_PORT,
            GK_RABBITMQ_USER,
            GK_RABBITMQ_PASS,
            GK_RABBITMQ_VHOST ?: '/'
        );

        $this->channel = $this->connection->channel();

        // Declare exchange idempotent
        $this->channel->exchange_declare('geokrety', 'fanout', false, true, false);

        // Declare durable queue for workers
        $queueName = 'basex_sync_worker_queue';
        $this->channel->queue_declare(
            $queueName,
            false,
            true,
            false,
            false
        );
        $this->channel->queue_bind($queueName, 'geokrety');

        // Prefetch and consume
        $this->channel->basic_qos(0, 1, false);
        $this->channel->basic_consume($queueName, '', false, false, false, false, [$this, 'processMessage']);

        $this->log(self::LOG_INFO, 'Connected to RabbitMQ', ['host' => GK_RABBITMQ_HOST, 'port' => GK_RABBITMQ_PORT, 'queue' => $queueName]);
    }

    public function processMessage(AMQPMessage $msg): void {
        try {
            $data = json_decode($msg->body, true);
            if (!$data || !isset($data['id'], $data['op'], $data['kind'])) {
                $this->log(self::LOG_WARNING, 'Invalid message format', ['body' => $msg->body]);
                $msg->ack();

                return;
            }
            $this->log(self::LOG_DEBUG, 'Message received', $data);
            if ($data['op'] !== 'INSERT') {
                $this->log(self::LOG_DEBUG, 'Ignoring non-INSERT operation', ['op' => $data['op']]);
                $msg->ack();

                return;
            }

            switch ($data['kind']) {
                // case 'gk_geokrety':
                //     $this->handleGeokret((int) $data['id']);
                //     break;
                case 'gk_moves':
                    $this->handleMove((int) $data['id']);
                    break;
                    // case 'gk_moves_comments':
                    //     $this->handleMoveComment((int) $data['id']);
                    //     break;
                default:
                    $this->log(self::LOG_DEBUG, 'Unhandled entity kind', ['kind' => $data['kind']]);
            }

            $msg->ack();
        } catch (Exception $e) {
            $this->log(self::LOG_ERROR, 'Error processing message: '.$e->getMessage(), ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            $msg->nack(true);
        }
    }

    private function shouldSkipGk(int $gkid): bool {
        // Avoid rapid duplicate updates
        $now = time();
        if (isset($this->processedCache[$gkid]) && ($now - $this->processedCache[$gkid]) < $this->cacheTtl) {
            return true;
        }
        $this->processedCache[$gkid] = $now;

        return false;
    }

    private function handleGeokret(int $gkid): void {
        if ($this->shouldSkipGk($gkid)) {
            $this->log(self::LOG_DEBUG, 'Skipping duplicate geokret update', ['gkid' => $gkid]);

            return;
        }

        $this->log(self::LOG_INFO, 'Sending BaseX update for geokret', ['gkid' => $gkid]);
        try {
            $this->executeBaseXUpdate($gkid);
            $this->log(self::LOG_INFO, 'BaseX updated', ['gkid' => $gkid]);
        } catch (Exception $e) {
            $this->log(self::LOG_ERROR, 'Failed to update BaseX: '.$e->getMessage(), ['gkid' => $gkid]);
        }
    }

    private function handleMove(int $moveId): void {
        $this->log(self::LOG_DEBUG, 'Querying database for move', ['move_id' => $moveId]);
        $move = new Move();
        $move->load(['id = ?', $moveId]);
        if ($move->dry()) {
            $this->log(self::LOG_WARNING, 'Move not found', ['move_id' => $moveId]);

            return;
        }
        $gkid = is_object($move->geokret) ? $move->geokret->id : (int) $move->geokret;
        if (!$gkid) {
            $this->log(self::LOG_WARNING, 'Move has no geokret relation', ['move_id' => $moveId]);

            return;
        }
        $this->handleGeokret((int) $gkid);
    }

    private function handleMoveComment(int $commentId): void {
        $this->log(self::LOG_DEBUG, 'Querying database for comment', ['comment_id' => $commentId]);
        $comment = new MoveComment();
        $comment->load(['id = ?', $commentId]);
        if ($comment->dry()) {
            $this->log(self::LOG_WARNING, 'Comment not found', ['comment_id' => $commentId]);

            return;
        }
        $move = new Move();
        $move->load(['id = ?', $comment->move]);
        if ($move->dry()) {
            $this->log(self::LOG_WARNING, 'Move for comment not found', ['comment_id' => $commentId]);

            return;
        }
        $gkid = is_object($move->geokret) ? $move->geokret->id : (int) $move->geokret;
        if (!$gkid) {
            $this->log(self::LOG_WARNING, 'Move has no geokret relation', ['move_id' => $move->id]);

            return;
        }
        $this->handleGeokret((int) $gkid);
    }

    private function executeBaseXUpdate(int $gkid): void {
        if (!GK_BASEX_HOST || !GK_BASEX_PORT) {
            throw new RuntimeException('BaseX configuration not found (GK_BASEX_*)');
        }
        if (!is_object($this->basexSession)) {
            $this->basexSession = new BaseXSession(GK_BASEX_HOST, (int) GK_BASEX_PORT, GK_BASEX_USER, GK_BASEX_PASSWORD);
        }

        $q = $this->basexSession->query($this->queryScript);
        $q->bind('gk_api_base_url', $this->gkApiBaseUrl);
        $q->bind('rate_limits_bypass', $this->rateLimitsBypass);
        $q->bind('short_lived_session_token', $this->shortLivedSessionToken);
        $q->bind('gkid', (string) $gkid);
        $q->execute();
        $q->close();
    }

    public function run(): void {
        $this->log(self::LOG_INFO, 'Starting BaseX consumer loop...');
        while (true) {
            try {
                $this->connect();
                $this->retryDelay = 1;
                $this->log(self::LOG_INFO, 'loop A');
                while ($this->channel->is_consuming()) {
                    $this->channel->wait(timeout: 10);
                    $this->log(self::LOG_INFO, 'loop 1');
                }
            } catch (AMQPConnectionException $e) {
                $this->log(self::LOG_ERROR, 'Connection lost: '.$e->getMessage());
                $this->cleanup();
                $this->log(self::LOG_INFO, "Reconnecting in {$this->retryDelay}s...");
                sleep($this->retryDelay);
                $this->retryDelay = min($this->retryDelay * 2, $this->maxRetryDelay);
            } catch (Exception $e) {
                $this->log(self::LOG_CRITICAL, 'Unexpected error: '.$e->getMessage(), ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                $this->cleanup();
                sleep($this->retryDelay);
                $this->retryDelay = min($this->retryDelay * 2, $this->maxRetryDelay);
            }
        }
    }

    private function cleanup(): void {
        try {
            if ($this->channel) {
                $this->channel->close();
            }
            if ($this->connection) {
                $this->connection->close();
            }
            if ($this->basexSession) {
                $this->basexSession->close();
            }
        } catch (Exception $e) {
            $this->log(self::LOG_WARNING, 'Error during cleanup: '.$e->getMessage());
        }
    }

    public function __destruct() {
        $this->cleanup();
    }
}

// Graceful shutdown handlers
pcntl_async_signals(true);
pcntl_signal(SIGTERM, function () {
    echo "Received SIGTERM, shutting down gracefully...\n";
    exit(0);
});
pcntl_signal(SIGINT, function () {
    echo "Received SIGINT, shutting down gracefully...\n";
    exit(0);
});

try {
    $worker = new BaseXSyncWorker();
    $worker->run();
} catch (Exception $e) {
    echo 'Fatal error: '.$e->getMessage()."\n";
    exit(1);
}

<?php

declare(strict_types=1);

/**
 * GeoKrety BaseX Sync Worker.
 *
 * RabbitMQ consumer listening to the 'geokrety' exchange and updating the BaseX
 * exports database using a XQuery script when GeoKret related entities change.
 */
require_once __DIR__.'/../init-f3.php';
require_once __DIR__.'/WorkerBase.php';

use Caxy\BaseX\Session as BaseXSession;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\Move;
use GeoKrety\Model\MoveComment;
use PhpAmqpLib\Message\AMQPMessage;

class BaseXSyncWorker extends WorkerBase {
    private $basexSession;
    private $queryScript;
    private $gkApiBaseUrl;
    private $rateLimitsBypass;
    private $shortLivedSessionToken;
    private $processedCache = [];
    private $cacheTtl = 1; // seconds

    public function __construct() {
        parent::__construct();
        $queryPath = __DIR__.'/xquery/update.xq';
        if (!file_exists($queryPath)) {
            $this->log(self::LOG_CRITICAL, 'BaseX XQuery script not found', ['path' => $queryPath]);
            throw new RuntimeException('BaseX XQuery script not found: '.$queryPath);
        }
        $this->queryScript = file_get_contents($queryPath);
        $this->rateLimitsBypass = GK_RATE_LIMITS_BYPASS;
        $this->shortLivedSessionToken = GK_SITE_SESSION_SHORT_LIVED_TOKEN;
    }

    protected function getWorkerName(): string {
        return 'BaseX Sync Worker';
    }

    protected function getQueueName(): string {
        return 'basex_sync_worker_queue';
    }

    protected function processMessage(AMQPMessage $msg): void {
        try {
            $data = json_decode($msg->body, true);
            if (!$data || !isset($data['id'], $data['op'], $data['kind'])) {
                $this->log(self::LOG_WARNING, 'Invalid message format', ['body' => $msg->body]);
                $msg->ack();

                return;
            }
            $this->log(self::LOG_DEBUG, 'Message received', $data);

            switch ($data['kind']) {
                case 'gk_geokrety':
                    $this->handleGeokret((int) $data['id']);
                    break;
                case 'gk_moves_comments':
                    $this->handleMoveComment((int) $data['id']);
                    break;
                case 'gk_moves':
                    $this->handleMove((int) $data['id']);
                    break;
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
        $q->bind('rate_limits_bypass', $this->rateLimitsBypass);
        $q->bind('short_lived_session_token', $this->shortLivedSessionToken);
        $q->bind('gkid', (string) $gkid);
        $q->execute();
        $q->close();
    }

    protected function cleanup(): void {
        parent::cleanup();
        try {
            if ($this->basexSession) {
                $this->basexSession->close();
            }
        } catch (Exception $e) {
            $this->log(self::LOG_WARNING, 'Error during cleanup: '.$e->getMessage());
        }
    }
}

// Graceful shutdown handlers
WorkerBase::registerSignalHandlers();

try {
    $worker = new BaseXSyncWorker();
    $worker->run();
} catch (Exception $e) {
    echo 'Fatal error: '.$e->getMessage()."\n";
    exit(1);
}

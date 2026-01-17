<?php

declare(strict_types=1);

/**
 * GeoKrety Instant Notification Worker.
 *
 * RabbitMQ consumer for processing instant email notifications.
 * Listens to the 'geokrety' exchange and sends immediate email notifications
 * for GeoKret activities (moves, comments) based on user preferences.
 */
require_once __DIR__.'/../init-f3.php';

use GeoKrety\Email\InstantNotification;
use GeoKrety\Model\Move;
use GeoKrety\Model\MoveComment;
use GeoKrety\Model\User;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPConnectionException;
use PhpAmqpLib\Message\AMQPMessage;

class MailNotificationWorker {
    private $connection;
    private $channel;
    private $retryDelay = 1;
    private $maxRetryDelay = 60;
    private $logLevel;

    // PSR-3 log levels
    public const LOG_DEBUG = 100;
    public const LOG_INFO = 200;
    public const LOG_WARNING = 300;
    public const LOG_ERROR = 400;
    public const LOG_CRITICAL = 500;

    public function __construct() {
        $this->logLevel = $this->getLogLevelFromEnv(getenv('GK_NOTIFICATION_LOG_LEVEL') ?: 'INFO');
        $this->log(self::LOG_INFO, 'Mail Notification Worker starting...');
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
            GK_RABBITMQ_PORT,
            GK_RABBITMQ_USER,
            GK_RABBITMQ_PASS,
            GK_RABBITMQ_VHOST ?: '/'
        );

        $this->channel = $this->connection->channel();

        // Declare exchange (idempotent - won't recreate if exists)
        $this->channel->exchange_declare('geokrety', 'fanout', false, true, false);

        // Declare named durable queue for multiple replicas
        // Queue flags: durable=true, exclusive=false, auto_delete=false
        // Message TTL: 3 hours (10,800,000 ms)
        $queueName = 'mail_notification_worker_queue';
        $this->channel->queue_declare(
            $queueName,
            false,        // passive
            true,         // durable - survive broker restart
            false,        // exclusive - allow multiple replicas
            false,        // auto_delete - persist even if container restarts
            false,        // nowait
            ['x-message-ttl' => ['I', 10800000]] // 3 hours TTL
        );

        // Bind queue to exchange
        $this->channel->queue_bind($queueName, 'geokrety');

        $this->log(self::LOG_INFO, 'Connected to RabbitMQ', [
            'host' => GK_RABBITMQ_HOST,
            'port' => GK_RABBITMQ_PORT,
            'queue' => $queueName,
        ]);

        // Set QoS: prefetch_count=1 ensures fair distribution among replicas
        // Each replica gets one message at a time (competing consumers pattern)
        $this->channel->basic_qos(
            0,     // prefetch_size - 0 means no limit
            1,     // prefetch_count - process one message at a time
            false  // global - apply per-consumer
        );

        // Setup consumer
        $this->channel->basic_consume(
            $queueName,
            '',
            false,
            false,
            false,
            false,
            [$this, 'processMessage']
        );
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

            // Only process INSERT operations
            if ($data['op'] !== 'INSERT') {
                $this->log(self::LOG_DEBUG, 'Ignoring non-INSERT operation', ['op' => $data['op']]);
                $msg->ack();

                return;
            }

            // Route to appropriate handler
            switch ($data['kind']) {
                case 'gk_moves':
                    $this->handleMove((int) $data['id']);
                    break;
                case 'gk_moves_comments':
                    $this->handleMoveComment((int) $data['id']);
                    break;
                default:
                    $this->log(self::LOG_DEBUG, 'Unhandled entity kind', ['kind' => $data['kind']]);
            }

            $msg->ack();
        } catch (Exception $e) {
            $this->log(self::LOG_ERROR, 'Error processing message: '.$e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $msg->nack(true); // Requeue on error
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

        $this->log(self::LOG_DEBUG, 'Move loaded', [
            'move_id' => $moveId,
            'geokret_id' => $move->geokret,
            'author_id' => $move->author,
        ]);

        // Validate required relations
        if (!$move->geokret || !$move->author) {
            $this->log(self::LOG_WARNING, 'Move missing required relations', [
                'move_id' => $moveId,
                'has_geokret' => $move->geokret !== null,
                'has_author' => $move->author !== null,
            ]);

            return;
        }

        // Find users who should be notified about this move
        $notifyUsers = $this->getUsersToNotifyForMove($move);

        foreach ($notifyUsers as $userId) {
            $this->sendMoveNotification($userId, $move);
        }
    }

    private function handleMoveComment(int $commentId): void {
        $this->log(self::LOG_DEBUG, 'Querying database for comment', ['comment_id' => $commentId]);

        $comment = new MoveComment();
        $comment->load(['id = ?', $commentId]);

        if ($comment->dry()) {
            $this->log(self::LOG_WARNING, 'Comment not found', ['comment_id' => $commentId]);

            return;
        }

        $this->log(self::LOG_DEBUG, 'Comment loaded', [
            'comment_id' => $commentId,
            'move_id' => $comment->move,
            'author_id' => $comment->author,
        ]);

        // Validate required relations
        if (!$comment->move || !$comment->author) {
            $this->log(self::LOG_WARNING, 'Comment missing required relations', [
                'comment_id' => $commentId,
                'has_move' => $comment->move !== null,
                'has_author' => $comment->author !== null,
            ]);

            return;
        }

        // Find users who should be notified about this comment
        $notifyUsers = $this->getUsersToNotifyForComment($comment);

        foreach ($notifyUsers as $userId) {
            $this->sendCommentNotification($userId, $comment);
        }
    }

    private function getUsersToNotifyForMove(Move $move): array {
        // Find GeoKret owner and watchers who have instant notifications enabled
        $f3 = Base::instance();
        $db = $f3->get('DB');

        $sql = <<<'SQL'
SELECT DISTINCT u.id
FROM geokrety.gk_users u
WHERE u.id IN (
    -- GeoKret owner
    SELECT gk.owner
    FROM geokrety.gk_geokrety gk
    WHERE gk.id = ?
    AND gk.owner IS NOT NULL

    UNION

    -- Watchers
    SELECT w.user
    FROM geokrety.gk_watched w
    WHERE w.geokret = ?
)
AND u.email_invalid = 0
AND EXISTS (
    SELECT 1
    FROM geokrety.gk_users_settings s
    WHERE s.user = u.id
    AND s.name = 'INSTANT_NOTIFICATIONS'
    AND s.value = 'true'
)
AND u.id != ?  -- Don't notify the move author
SQL;

        $result = $db->exec($sql, [$move->geokret->id, $move->geokret->id, $move->author->id]);

        return array_column($result, 'id');
    }

    private function getUsersToNotifyForComment(MoveComment $comment): array {
        // Find move author and GeoKret owner/watchers who have instant notifications enabled
        $f3 = Base::instance();
        $db = $f3->get('DB');

        $sql = <<<'SQL'
SELECT DISTINCT u.id
FROM geokrety.gk_users u
WHERE u.id IN (
    -- Move author
    SELECT m.author
    FROM geokrety.gk_moves m
    WHERE m.id = ?
    AND m.author IS NOT NULL

    UNION

    -- GeoKret owner
    SELECT gk.owner
    FROM geokrety.gk_geokrety gk
    INNER JOIN geokrety.gk_moves m ON m.geokret = gk.id
    WHERE m.id = ?
    AND gk.owner IS NOT NULL

    UNION

    -- Watchers
    SELECT w.user
    FROM geokrety.gk_watched w
    INNER JOIN geokrety.gk_moves m ON m.geokret = w.geokret
    WHERE m.id = ?
)
AND u.email_invalid = 0
AND EXISTS (
    SELECT 1
    FROM geokrety.gk_users_settings s
    WHERE s.user = u.id
    AND s.name = 'INSTANT_NOTIFICATIONS'
    AND s.value = 'true'
)
AND u.id != ?  -- Don't notify the comment author
SQL;

        $result = $db->exec($sql, [$comment->move->id, $comment->move->id, $comment->move->id, $comment->author->id]);

        return array_column($result, 'id');
    }

    private function sendMoveNotification(int $userId, Move $move): void {
        try {
            $user = new User();
            $user->load(['id = ?', $userId]);

            if ($user->dry()) {
                $this->log(self::LOG_WARNING, 'User not found', ['user_id' => $userId]);

                return;
            }

            $this->log(self::LOG_INFO, 'Sending move notification', [
                'user_id' => $userId,
                'email' => $user->email,
                'move_id' => $move->id,
            ]);

            $email = new InstantNotification();
            $email->sendMoveNotification($user, $move);

            $this->log(self::LOG_INFO, 'Move notification sent successfully', [
                'user_id' => $userId,
                'move_id' => $move->id,
            ]);
        } catch (Exception $e) {
            $this->log(self::LOG_ERROR, 'Failed to send move notification: '.$e->getMessage(), [
                'user_id' => $userId,
                'move_id' => $move->id,
                'exception' => get_class($e),
            ]);
        }
    }

    private function sendCommentNotification(int $userId, MoveComment $comment): void {
        $user = new User();
        $user->load(['id = ?', $userId]);

        if ($user->dry()) {
            $this->log(self::LOG_WARNING, 'User not found', ['user_id' => $userId]);

            return;
        }

        $this->log(self::LOG_INFO, 'Sending comment notification', [
            'user_id' => $userId,
            'email' => $user->email,
            'comment_id' => $comment->id,
        ]);

        $email = new InstantNotification();
        $email->sendCommentNotification($user, $comment);

        $this->log(self::LOG_INFO, 'Comment notification sent successfully', [
            'user_id' => $userId,
            'comment_id' => $comment->id,
        ]);

        try {
        } catch (Exception $e) {
            $this->log(self::LOG_ERROR, 'Failed to send comment notification: '.$e->getMessage(), [
                'user_id' => $userId,
                'comment_id' => $comment->id,
                'exception' => get_class($e),
            ]);
        }
    }

    public function run(): void {
        $this->log(self::LOG_INFO, 'Starting consumer loop...');

        while (true) {
            try {
                $this->connect();
                $this->retryDelay = 1; // Reset on successful connection

                // Process messages
                while ($this->channel->is_consuming()) {
                    $this->channel->wait();
                }
            } catch (AMQPConnectionException $e) {
                $this->log(self::LOG_ERROR, 'Connection lost: '.$e->getMessage());
                $this->cleanup();

                $this->log(self::LOG_INFO, "Reconnecting in {$this->retryDelay}s...");
                sleep($this->retryDelay);

                // Exponential backoff
                $this->retryDelay = min($this->retryDelay * 2, $this->maxRetryDelay);
            } catch (Exception $e) {
                $this->log(self::LOG_CRITICAL, 'Unexpected error: '.$e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
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
        } catch (Exception $e) {
            $this->log(self::LOG_WARNING, 'Error during cleanup: '.$e->getMessage());
        }
    }

    public function __destruct() {
        $this->cleanup();
    }
}

// Handle graceful shutdown
pcntl_async_signals(true);
pcntl_signal(SIGTERM, function () {
    echo "Received SIGTERM, shutting down gracefully...\n";
    exit(0);
});
pcntl_signal(SIGINT, function () {
    echo "Received SIGINT, shutting down gracefully...\n";
    exit(0);
});

// Run the worker
try {
    $worker = new MailNotificationWorker();
    $worker->run();
} catch (Exception $e) {
    echo 'Fatal error: '.$e->getMessage()."\n";
    exit(1);
}

<?php

declare(strict_types=1);

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPConnectionException;
use PhpAmqpLib\Message\AMQPMessage;

abstract class WorkerBase {
    protected $connection;
    protected $channel;
    protected int $retryDelay = 1;
    protected int $maxRetryDelay = 60;
    protected int $logLevel;

    public const LOG_DEBUG = 100;
    public const LOG_INFO = 200;
    public const LOG_WARNING = 300;
    public const LOG_ERROR = 400;
    public const LOG_CRITICAL = 500;

    public function __construct() {
        $this->logLevel = $this->getLogLevelFromEnv(getenv('GK_NOTIFICATION_LOG_LEVEL') ?: 'INFO');
    }

    abstract protected function getWorkerName(): string;

    abstract protected function processMessage(AMQPMessage $msg): void;

    abstract protected function getQueueName(): string;

    protected function getQueueArguments(): array {
        return [];
    }

    protected function getPrefetchCount(): int {
        return 1;
    }

    protected function getWaitTimeout(): ?int {
        return null;
    }

    protected function getLogLevelFromEnv(string $level): int {
        $levels = [
            'DEBUG' => self::LOG_DEBUG,
            'INFO' => self::LOG_INFO,
            'WARNING' => self::LOG_WARNING,
            'ERROR' => self::LOG_ERROR,
            'CRITICAL' => self::LOG_CRITICAL,
        ];

        return $levels[strtoupper($level)] ?? self::LOG_INFO;
    }

    protected function log(int $level, string $message, array $context = []): void {
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

    protected function connect(): void {
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

        $queueName = $this->getQueueName();
        $this->channel->queue_declare(
            $queueName,
            false,
            true,
            false,
            false,
            false,
            $this->getQueueArguments()
        );
        $this->channel->queue_bind($queueName, 'geokrety');

        $this->channel->basic_qos(0, $this->getPrefetchCount(), false);
        $this->channel->basic_consume(
            $queueName,
            '',
            false,
            false,
            false,
            false,
            Closure::fromCallable([$this, 'processMessage'])
        );

        $this->log(self::LOG_INFO, 'Connected to RabbitMQ', [
            'host' => GK_RABBITMQ_HOST,
            'port' => GK_RABBITMQ_PORT,
            'queue' => $queueName,
        ]);
    }

    protected function waitForMessage(): void {
        $timeout = $this->getWaitTimeout();
        if ($timeout === null) {
            $this->channel->wait();

            return;
        }

        $this->channel->wait(timeout: $timeout);
    }

    public function run(): void {
        $this->log(self::LOG_INFO, $this->getWorkerName().' starting...');

        while (true) {
            try {
                $this->connect();
                $this->retryDelay = 1; // Reset on successful connection

                while ($this->channel->is_consuming()) {
                    $this->waitForMessage();
                }
            } catch (AMQPConnectionException $e) {
                $this->log(self::LOG_ERROR, 'Connection lost: '.$e->getMessage());
                $this->cleanup();
                $this->log(self::LOG_INFO, "Reconnecting in {$this->retryDelay}s...");
                sleep($this->retryDelay);
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

    protected function cleanup(): void {
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

    public static function registerSignalHandlers(): void {
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, function () {
            echo "Received SIGTERM, shutting down gracefully...\n";
            exit(0);
        });
        pcntl_signal(SIGINT, function () {
            echo "Received SIGINT, shutting down gracefully...\n";
            exit(0);
        });
    }
}

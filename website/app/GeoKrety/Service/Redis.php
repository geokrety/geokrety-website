<?php

// Extracted some code from Prometheus/Storage/Redis

namespace GeoKrety\Service;

use Exception;

/**
 * Exception thrown if an error occurs during metrics storage.
 */
class StorageException extends \Exception {
}

class Redis extends \Prefab {
    private \Redis $redis;

    private array $options = [
        'host' => GK_REDIS_HOST,
        'port' => GK_REDIS_PORT,
        'timeout' => 0.1,
        'read_timeout' => '10',
        'persistent_connections' => false,
        'password' => null,
    ];

    private bool $connectionInitialized = false;

    /**
     * Redis constructor.
     */
    public function __construct() {
        $this->redis = new \Redis();
    }

    /**
     * @throws \GeoKrety\Service\StorageException
     */
    public function getRedis(): \Redis {
        $this->ensureOpenConnection();

        return $this->redis;
    }

    /**
     * @throws \GeoKrety\Service\StorageException
     */
    public function ensureOpenConnection(): void {
        if ($this->connectionInitialized === true) {
            return;
        }

        $this->connectToServer();

        if ($this->options['password'] !== null) {
            $this->redis->auth($this->options['password']);
        }

        if (isset($this->options['database'])) {
            $this->redis->select($this->options['database']);
        }

        $this->redis->setOption(\Redis::OPT_READ_TIMEOUT, $this->options['read_timeout']);
    }

    /**
     * @throws StorageException
     */
    private function connectToServer(): void {
        if ($this->options['persistent_connections'] !== false) {
            $connection_successful = $this->redis->pconnect(
                $this->options['host'],
                (int) $this->options['port'],
                (float) $this->options['timeout']
            );
        } else {
            $connection_successful = $this->redis->connect($this->options['host'], (int) $this->options['port'], (float) $this->options['timeout']);
        }
        if (!$connection_successful) {
            throw new StorageException("Can't connect to Redis server", 0);
        }
    }

    /**
     * Verify if the specified key/keys exists.
     *
     * @param string|string[] $key
     *
     * @return bool|int|\Redis
     */
    public function exists($key) {
        return $this->redis->exists($key);
    }

    /**
     * Increment the number stored at key by one.
     *
     * @param string $key
     *
     * @return int|Redis the new value or Redis if in multi mode
     */
    public function incr($key) {
        return $this->redis->incr($key);
    }

    /**
     * Sets an expiration date (a timeout) on an item.
     *
     * @param string $key The key that will disappear
     * @param int    $ttl The key's remaining Time To Live, in seconds
     *
     * @return bool|Redis TRUE in case of success, FALSE in case of failure or Redis if in multi mode
     */
    public function expire($key, $ttl) {
        return $this->redis->expire($key, $ttl);
    }

    /**
     * Set the string value in argument as value of the key.
     *
     * @param string       $key
     * @param string|mixed $value   string if not used serializer
     * @param int|array    $timeout [optional] Calling setEx() is preferred if you want a timeout
     *
     * @return bool|Redis TRUE if the command is successful or Redis if in multi mode
     */
    public function set($key, $value, $timeout = null) {
        return $this->redis->set($key, $value, $timeout);
    }

    /**
     * Get the value related to the specified key.
     *
     * @param string $key
     *
     * @return string|mixed|false|Redis If key didn't exist, FALSE is returned or Redis if in multi mode
     *                                  Otherwise, the value related to this key is returned
     */
    public function get($key) {
        return $this->redis->get($key);
    }

    /**
     * Returns the keys that match a certain pattern.
     *
     * @param string $pattern pattern, using '*' as a wildcard
     *
     * @return array|Redis string[] The keys that match a certain pattern or Redis if in multi mode
     */
    public function keys($pattern) {
        return $this->redis->keys($pattern);
    }

    /**
     * Remove specified keys.
     *
     * @param int|string|array $key1         An array of keys, or an undefined number of parameters, each a key: key1 key2 key3 ... keyN
     * @param int|string       ...$otherKeys
     *
     * @return int|Redis Number of keys deleted or Redis if in multi mode
     */
    public function del($key1, ...$otherKeys) {
        return $this->redis->del($key1, ...$otherKeys);
    }
}

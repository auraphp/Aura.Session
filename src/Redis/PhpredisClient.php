<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 *
 */
namespace Aura\Session\Redis;

use Redis;

/**
 *
 * Adapts the phpredis `\Redis` client to RedisClientInterface.
 *
 * Requires the `redis` (phpredis) extension.
 *
 * @package Aura.Session
 *
 */
class PhpredisClient implements RedisClientInterface
{
    /**
     *
     * The phpredis client.
     *
     * @var Redis
     *
     */
    protected $redis;

    /**
     *
     * Constructor.
     *
     * @param Redis $redis A connected phpredis client.
     *
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function hGetAll(string $key): array
    {
        $result = $this->redis->hGetAll($key);
        return is_array($result) ? $result : array();
    }

    public function hSet(string $key, string $field, string $value): void
    {
        $this->redis->hSet($key, $field, $value);
    }

    public function del(string $key): void
    {
        $this->redis->del($key);
    }

    public function expire(string $key, int $ttl): void
    {
        $this->redis->expire($key, $ttl);
    }

    public function exists(string $key): bool
    {
        return (bool) $this->redis->exists($key);
    }
}

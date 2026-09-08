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

    public function get(string $key): ?string
    {
        $value = $this->redis->get($key);
        return $value === false ? null : $value;
    }

    public function setEx(string $key, int $ttl, string $value): void
    {
        $this->redis->setEx($key, $ttl, $value);
    }

    public function del(string $key): void
    {
        // UNLINK reclaims memory in a background thread; fall back to DEL on
        // servers older than Redis 4.0.
        if (method_exists($this->redis, 'unlink')) {
            $this->redis->unlink($key);
            return;
        }

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

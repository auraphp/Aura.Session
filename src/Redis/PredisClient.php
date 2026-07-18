<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 *
 */
namespace Aura\Session\Redis;

use Predis\ClientInterface;

/**
 *
 * Adapts the `predis/predis` client to RedisClientInterface.
 *
 * Requires the `predis/predis` package.
 *
 * @package Aura.Session
 *
 */
class PredisClient implements RedisClientInterface
{
    /**
     *
     * The Predis client.
     *
     * @var ClientInterface
     *
     */
    protected $redis;

    /**
     *
     * Constructor.
     *
     * @param ClientInterface $redis A Predis client.
     *
     */
    public function __construct(ClientInterface $redis)
    {
        $this->redis = $redis;
    }

    public function hGetAll(string $key): array
    {
        return $this->redis->hgetall($key) ?: array();
    }

    public function hSet(string $key, string $field, string $value): void
    {
        $this->redis->hset($key, $field, $value);
    }

    public function del(string $key): void
    {
        $this->redis->del([$key]);
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

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

    public function get(string $key): ?string
    {
        return $this->redis->get($key);
    }

    public function setEx(string $key, int $ttl, string $value): void
    {
        $this->redis->setex($key, $ttl, $value);
    }

    public function del(string $key): void
    {
        // DEL, not UNLINK: predis only registered the UNLINK command in
        // 3.5.0 (and only accepted an array of keys from 3.5.1), and calling
        // it on anything older throws Predis\ClientException before the
        // command ever reaches the server. Unlike ext-redis, predis is pure
        // PHP, so requiring PHP 8.4 says nothing about which predis release
        // is installed -- predis 2.x runs on 8.4 perfectly well.
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

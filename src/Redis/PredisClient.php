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
        // UNLINK reclaims memory in a background thread. It needs Redis 4.0,
        // which is older than the PHP version this package requires.
        $this->redis->unlink([$key]);
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

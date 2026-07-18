<?php
namespace Aura\Session;

use Aura\Session\Redis\RedisClientInterface;

// An in-memory RedisClientInterface implementation for testing. No extension or
// server required.
class FakeRedisClient implements RedisClientInterface
{
    /** @var array<string, array<string, string>> */
    public array $hashes = array();

    /** @var array<string, int> */
    public array $ttls = array();

    public function hGetAll(string $key): array
    {
        return $this->hashes[$key] ?? array();
    }

    public function hSet(string $key, string $field, string $value): void
    {
        $this->hashes[$key][$field] = $value;
    }

    public function del(string $key): void
    {
        unset($this->hashes[$key], $this->ttls[$key]);
    }

    public function expire(string $key, int $ttl): void
    {
        if (isset($this->hashes[$key])) {
            $this->ttls[$key] = $ttl;
        }
    }

    public function exists(string $key): bool
    {
        return isset($this->hashes[$key]);
    }
}

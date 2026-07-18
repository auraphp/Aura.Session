<?php
namespace Aura\Session;

use Aura\Session\Redis\RedisClientInterface;

// An in-memory RedisClientInterface implementation for testing. No extension or
// server required.
class FakeRedisClient implements RedisClientInterface
{
    /** @var array<string, string> */
    public array $values = array();

    /** @var array<string, int> */
    public array $ttls = array();

    public function get(string $key): ?string
    {
        return $this->values[$key] ?? null;
    }

    public function setEx(string $key, int $ttl, string $value): void
    {
        $this->values[$key] = $value;
        $this->ttls[$key] = $ttl;
    }

    public function del(string $key): void
    {
        unset($this->values[$key], $this->ttls[$key]);
    }

    public function expire(string $key, int $ttl): void
    {
        if (isset($this->values[$key])) {
            $this->ttls[$key] = $ttl;
        }
    }

    public function exists(string $key): bool
    {
        return isset($this->values[$key]);
    }
}

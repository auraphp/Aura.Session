<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 *
 */
namespace Aura\Session\Redis;

/**
 *
 * A minimal, client-neutral contract for the Redis operations used by
 * RedisSessionHandler. Implement this to back the handler with phpredis,
 * Predis, or any other client.
 *
 * @package Aura.Session
 *
 */
interface RedisClientInterface
{
    /**
     *
     * Returns the string value stored at $key, or null when it does not exist.
     *
     * @param string $key The Redis key.
     *
     * @return string|null
     *
     */
    public function get(string $key): ?string;

    /**
     *
     * Stores $value at $key with a time-to-live of $ttl seconds, in a single
     * atomic operation.
     *
     * @param string $key The Redis key.
     *
     * @param int $ttl The time-to-live in seconds.
     *
     * @param string $value The value to store.
     *
     * @return void
     *
     */
    public function setEx(string $key, int $ttl, string $value): void;

    /**
     *
     * Deletes $key.
     *
     * @param string $key The Redis key.
     *
     * @return void
     *
     */
    public function del(string $key): void;

    /**
     *
     * Sets the time-to-live, in seconds, on $key.
     *
     * @param string $key The Redis key.
     *
     * @param int $ttl The time-to-live in seconds.
     *
     * @return void
     *
     */
    public function expire(string $key, int $ttl): void;

    /**
     *
     * Reports whether $key exists.
     *
     * @param string $key The Redis key.
     *
     * @return bool
     *
     */
    public function exists(string $key): bool;
}

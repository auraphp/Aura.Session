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
 * A minimal, client-neutral contract for the Redis hash operations used by
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
     * Returns all fields and values of the hash stored at $key.
     *
     * @param string $key The Redis key.
     *
     * @return array<string, string> A map of field => raw stored value; an
     * empty array when the key does not exist.
     *
     */
    public function hGetAll(string $key): array;

    /**
     *
     * Sets $field to $value in the hash stored at $key.
     *
     * @param string $key The Redis key.
     *
     * @param string $field The hash field.
     *
     * @param string $value The raw value to store.
     *
     * @return void
     *
     */
    public function hSet(string $key, string $field, string $value): void;

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

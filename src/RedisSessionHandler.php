<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 *
 */
namespace Aura\Session;

use Aura\Session\Redis\RedisClientInterface;
use RuntimeException;
use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;

/**
 *
 * A session save handler that stores each session as a Redis hash, with one
 * hash field per top-level session key (i.e. per Segment), instead of a single
 * serialized string.
 *
 * The handler is decoupled from any specific Redis client through
 * RedisClientInterface; use the bundled PhpredisClient or PredisClient
 * adapters, or provide your own implementation.
 *
 * It requires the `php_serialize` session serialize handler, so that the
 * encoded session data is a plain `serialize()` of the `$_SESSION` array that
 * can be split into, and rebuilt from, individual hash fields:
 *
 *      ini_set('session.serialize_handler', 'php_serialize');
 *
 *      $redis = new \Redis();
 *      $redis->connect('127.0.0.1', 6379);
 *      $handler = new \Aura\Session\RedisSessionHandler(
 *          new \Aura\Session\Redis\PhpredisClient($redis)
 *      );
 *      session_set_save_handler($handler, true);
 *
 * @package Aura.Session
 *
 */
class RedisSessionHandler implements
    SessionHandlerInterface,
    SessionUpdateTimestampHandlerInterface
{
    /**
     *
     * The Redis client.
     *
     * @var RedisClientInterface
     *
     */
    protected $redis;

    /**
     *
     * The session lifetime in seconds, used as the Redis key TTL. When null,
     * the value of `session.gc_maxlifetime` is used at write time.
     *
     * @var int|null
     *
     */
    protected $ttl;

    /**
     *
     * A prefix applied to every Redis key.
     *
     * @var string
     *
     */
    protected $prefix;

    /**
     *
     * Constructor.
     *
     * @param RedisClientInterface $redis A Redis client adapter.
     *
     * @param int|null $ttl The session lifetime in seconds. When null, the
     * value of `session.gc_maxlifetime` is used at write time.
     *
     * @param string $prefix A prefix applied to every Redis key.
     *
     */
    public function __construct(
        RedisClientInterface $redis,
        ?int $ttl = null,
        string $prefix = 'aura-session:'
    ) {
        $this->redis = $redis;
        $this->ttl = $ttl;
        $this->prefix = $prefix;
    }

    /**
     *
     * Opens the session; asserts that the serialize handler is compatible.
     *
     * @param string $save_path The session save path (unused).
     *
     * @param string $session_name The session name (unused).
     *
     * @return bool
     *
     */
    public function open(string $save_path, string $session_name): bool
    {
        $this->assertSerializeHandler();
        return true;
    }

    /**
     *
     * Closes the session.
     *
     * @return bool
     *
     */
    public function close(): bool
    {
        return true;
    }

    /**
     *
     * Reads the session data by reassembling the hash fields into the encoded
     * session string PHP expects.
     *
     * @param string $session_id The session id.
     *
     * @return string|false The encoded session data, or an empty string.
     *
     */
    public function read(string $session_id): string|false
    {
        $this->assertSerializeHandler();

        $fields = $this->redis->hGetAll($this->key($session_id));
        if ($fields === array()) {
            return '';
        }

        $session = array();
        foreach ($fields as $name => $value) {
            $session[$name] = unserialize($value);
        }

        return serialize($session);
    }

    /**
     *
     * Writes the session data, storing each top-level session key as its own
     * hash field and (re)setting the key's TTL.
     *
     * @param string $session_id The session id.
     *
     * @param string $session_data The encoded session data.
     *
     * @return bool
     *
     */
    public function write(string $session_id, string $session_data): bool
    {
        $this->assertSerializeHandler();

        $session = $session_data === '' ? array() : unserialize($session_data);
        if (! is_array($session)) {
            throw new RuntimeException(
                'Could not decode session data; is '
                . "session.serialize_handler set to 'php_serialize'?"
            );
        }

        $key = $this->key($session_id);

        // remove any fields that no longer exist, then write current fields
        $this->redis->del($key);

        if ($session !== array()) {
            foreach ($session as $name => $value) {
                $this->redis->hSet($key, (string) $name, serialize($value));
            }
            $this->redis->expire($key, $this->ttl());
        }

        return true;
    }

    /**
     *
     * Destroys the session.
     *
     * @param string $session_id The session id.
     *
     * @return bool
     *
     */
    public function destroy(string $session_id): bool
    {
        $this->redis->del($this->key($session_id));
        return true;
    }

    /**
     *
     * Garbage collection is handled by Redis key expiration (TTL), so there is
     * nothing to do here.
     *
     * @param int $maxlifetime The maximum session lifetime (unused).
     *
     * @return int|false
     *
     */
    public function gc(int $maxlifetime): int|false
    {
        return 0;
    }

    /**
     *
     * Validates a session id, i.e. reports whether the session exists. Lets PHP
     * avoid regenerating ids for sessions that are already stored.
     *
     * @param string $session_id The session id.
     *
     * @return bool
     *
     */
    public function validateId(string $session_id): bool
    {
        return $this->redis->exists($this->key($session_id));
    }

    /**
     *
     * Updates the session's timestamp when the data has not changed; only the
     * TTL is refreshed, avoiding a full rewrite of every field.
     *
     * @param string $session_id The session id.
     *
     * @param string $session_data The encoded session data (unused).
     *
     * @return bool
     *
     */
    public function updateTimestamp(string $session_id, string $session_data): bool
    {
        $this->redis->expire($this->key($session_id), $this->ttl());
        return true;
    }

    /**
     *
     * Returns the prefixed Redis key for a session id.
     *
     * @param string $session_id The session id.
     *
     * @return string
     *
     */
    protected function key(string $session_id): string
    {
        return $this->prefix . $session_id;
    }

    /**
     *
     * Returns the TTL to apply to session keys, in seconds.
     *
     * @return int
     *
     */
    protected function ttl(): int
    {
        if ($this->ttl !== null) {
            return $this->ttl;
        }

        return max(1, (int) ini_get('session.gc_maxlifetime'));
    }

    /**
     *
     * Asserts that the session serialize handler is `php_serialize`, which this
     * handler depends on to split and rebuild session data as hash fields.
     *
     * @return void
     *
     * @throws RuntimeException when the serialize handler is not `php_serialize`.
     *
     */
    protected function assertSerializeHandler(): void
    {
        if (ini_get('session.serialize_handler') !== 'php_serialize') {
            throw new RuntimeException(
                'Aura\Session\RedisSessionHandler requires '
                . "ini_set('session.serialize_handler', 'php_serialize') "
                . 'so session data can be stored as Redis hash fields.'
            );
        }
    }
}

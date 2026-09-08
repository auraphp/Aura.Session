<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 *
 */
namespace Aura\Session;

/**
 *
 * A session segment; lazy-loads from the session.
 *
 * @package Aura.Session
 *
 */
class Segment implements SegmentInterface
{
    /**
     *
     * The session manager.
     *
     * @var Session
     *
     */
    protected $session;

    /**
     *
     * The segment name.
     *
     * @var string
     *
     */
    protected $name;

    /**
     *
     * Constructor.
     *
     * @param Session $session The session manager.
     *
     * @param string $name The segment name.
     *
     */
    public function __construct(Session $session, $name)
    {
        $this->session = $session;
        $this->name = $name;
    }

    /**
     *
     * Returns the value of a key in the segment.
     *
     * @param string $key The key in the segment.
     *
     * @param mixed $alt An alternative value to return if the key is not set.
     *
     * @return mixed
     *
     */
    public function get(string $key, mixed $alt = null): mixed
    {
        $this->resumeSession();
        return isset($_SESSION[$this->name][$key])
             ? $_SESSION[$this->name][$key]
             : $alt;
    }

    /**
     *
     * Returns the entire segment.
     *
     * @return mixed
     *
     */
    public function getSegment(): mixed
    {
        $this->resumeSession();
        return isset($_SESSION[$this->name])
            ? $_SESSION[$this->name]
            : null;
    }

    /**
     *
     * Sets the value of a key in the segment.
     *
     * @param string $key The key to set.
     *
     * @param mixed $val The value to set it to.
     *
     */
    public function set(string $key, mixed $val): void
    {
        $this->resumeOrStartSession();
        $_SESSION[$this->name][$key] = $val;
    }

    /**
     *
     * Clear all data from the segment.
     *
     * @return void
     *
     */
    public function clear(): void
    {
        if ($this->resumeSession()) {
            $_SESSION[$this->name] = array();
        }
    }


    /**
     * Remove a key from the segment, or remove the entire segment (including key) from the session
     *
     * @param string|null $key The key to remove, or null to clear the entire segment.
     */
    public function remove(?string $key = null): void
    {
        if (! $this->resumeSession()) {
            return;
        }
        if ($key === null) {
            unset($_SESSION[$this->name]);
            unset($_SESSION[Session::FLASH_NOW][$this->name]);
            unset($_SESSION[Session::FLASH_NEXT][$this->name]);
            return;
        }
        if (array_key_exists($key, $_SESSION[$this->name])) {
            unset($_SESSION[$this->name][$key]);
        }
    }

    /**
     *
     * Sets a flash value for the *next* request.
     *
     * @param string $key The key for the flash value.
     *
     * @param mixed $val The flash value itself.
     *
     */
    public function setFlash(string $key, mixed $val): void
    {
        $this->resumeOrStartSession();
        $_SESSION[Session::FLASH_NEXT][$this->name][$key] = $val;
    }

    /**
     *
     * Gets the flash value for a key in the *current* request.
     *
     * @param string $key The key for the flash value.
     *
     * @param mixed $alt An alternative value to return if the key is not set.
     *
     * @return mixed The flash value itself.
     *
     */
    public function getFlash(string $key, mixed $alt = null): mixed
    {
        $this->resumeSession();
        return isset($_SESSION[Session::FLASH_NOW][$this->name][$key])
             ? $_SESSION[Session::FLASH_NOW][$this->name][$key]
             : $alt;
    }

    /**
     *
     * Gets all the flash values for the *current* request.
     *
     * @return array All the flash values for the current request; empty when
     * there are none.
     *
     */
    public function getFlashAll(): array
    {
        $this->resumeSession();
        return $_SESSION[Session::FLASH_NOW][$this->name] ?? array();
    }

    /**
     *
     * Clears flash values for *only* the next request.
     *
     * @return void
     *
     */
    public function clearFlash(): void
    {
        if ($this->resumeSession()) {
            $_SESSION[Session::FLASH_NEXT][$this->name] = array();
        }
    }

    /**
     *
     * Gets the flash value for a key in the *next* request.
     *
     * @param string $key The key for the flash value.
     *
     * @param mixed $alt An alternative value to return if the key is not set.
     *
     * @return mixed The flash value itself.
     *
     */
    public function getFlashNext(string $key, mixed $alt = null): mixed
    {
        $this->resumeSession();
        return isset($_SESSION[Session::FLASH_NEXT][$this->name][$key])
             ? $_SESSION[Session::FLASH_NEXT][$this->name][$key]
             : $alt;
    }

    /**
     *
     * Gets all the flash values for the *next* request.
     *
     * @return array All the flash values for the next request; empty when
     * there are none.
     *
     */
    public function getFlashNextAll(): array
    {
        $this->resumeSession();
        return $_SESSION[Session::FLASH_NEXT][$this->name] ?? array();
    }

    /**
     *
     * Sets a flash value for the *next* request *and* the current one.
     *
     * @param string $key The key for the flash value.
     *
     * @param mixed $val The flash value itself.
     *
     */
    public function setFlashNow(string $key, mixed $val): void
    {
        $this->resumeOrStartSession();
        $_SESSION[Session::FLASH_NOW][$this->name][$key] = $val;
        $_SESSION[Session::FLASH_NEXT][$this->name][$key] = $val;
    }

    /**
     *
     * Clears flash values for *both* the next request *and* the current one.
     *
     * @return void
     *
     */
    public function clearFlashNow(): void
    {
        if ($this->resumeSession()) {
            $_SESSION[Session::FLASH_NOW][$this->name] = array();
            $_SESSION[Session::FLASH_NEXT][$this->name] = array();
        }
    }

    /**
     *
     * Retains all the current flash values for the next request; values that
     * already exist for the next request take precedence.
     *
     * @return void
     *
     */
    public function keepFlash(): void
    {
        if ($this->resumeSession()) {
            $_SESSION[Session::FLASH_NEXT][$this->name] = array_merge(
                $_SESSION[Session::FLASH_NEXT][$this->name],
                $_SESSION[Session::FLASH_NOW][$this->name]
            );
        }
    }

    /**
     *
     * Loads the segment only if the session has already been started, or if
     * a session is available (in which case it resumes the session first).
     *
     * @return bool
     *
     */
    protected function resumeSession()
    {
        if ($this->session->isStarted() || $this->session->resume()) {
            $this->load();
            return true;
        }

        return false;
    }

    /**
     *
     * Sets the segment properties to $_SESSION references.
     *
     * @return void
     *
     */
    protected function load()
    {
        if (! isset($_SESSION[$this->name])) {
            $_SESSION[$this->name] = array();
        }

        if (! isset($_SESSION[Session::FLASH_NOW][$this->name])) {
            $_SESSION[Session::FLASH_NOW][$this->name] = array();
        }

        if (! isset($_SESSION[Session::FLASH_NEXT][$this->name])) {
            $_SESSION[Session::FLASH_NEXT][$this->name] = array();
        }
    }

    /**
     *
     * Resumes a previous session, or starts a new one, and loads the segment.
     *
     * @return void
     *
     */
    protected function resumeOrStartSession()
    {
        if (! $this->resumeSession()) {
            $this->session->start();
            $this->load();
        }
    }
}

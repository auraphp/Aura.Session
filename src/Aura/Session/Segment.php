<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Session
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
     * @var Manager
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
     * The data in the segment is a reference to a $_SESSION key.
     * 
     * @var array
     * 
     */
    protected $data;
    
    /**
     * 
     * Constructor.
     * 
     * @param Manager $session The session manager.
     * 
     * @param string $name The segment name.
     * 
     */
    public function __construct(Manager $session, $name)
    {
        $this->session = $session;
        $this->name = $name;
    }

    /**
     * 
     * Checks to see if the segment data has been loaded; if not, checks to
     * see if a session has already been started or is available, and then
     * loads the segment data from the session.
     * 
     * @return bool
     * 
     */
    protected function isLoaded()
    {
        if ($this->data !== null) {
            return true;
        }
        
        if ($this->session->isStarted() || $this->session->isAvailable()) {
            $this->load();
            return true;
        }
        
        return false;
    }
    
    /**
     * 
     * Forces a session start (or reactivation) and loads the segment data
     * from the session.
     * 
     * @return void
     * 
     */
    protected function load()
    {
        // is data already loaded?
        if ($this->data !== null) {
            // no need to re-load
            return;
        }
        
        // if the session is not started, start it
        if (! $this->session->isStarted()) {
            $this->session->start();
        }
        
        // if we don't have a $_SESSION key for the segment, create one
        if (! isset($_SESSION[$this->name])) {
            $_SESSION[$this->name] = [];
        }
        
        // set $data as a reference to the $_SESSION key
        $this->data = &$_SESSION[$this->name];
    }
    
    /**
     * 
     * Returns the value of a key in the segment.
     * 
     * @param string $key The key in the segment.
     * 
     * @return mixed
     * 
     */
    public function __get($key)
    {
        if ($this->isLoaded()) {
            return isset($this->data[$key]) ? $this->data[$key] : null;
        }
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
    public function __set($key, $val)
    {
        $this->load();
        $this->data[$key] = $val;
    }

    /**
     * 
     * Check whether a key is set in the segment.
     * 
     * @param string $key The key to check.
     * 
     * @return bool
     * 
     */
    public function __isset($key)
    {
        if ($this->isLoaded()) {
            return isset($this->data[$key]);
        }
        return false;
    }

    /**
     * 
     * Unsets a key in the segment.
     * 
     * @param string $key The key to unset.
     * 
     * @return void
     * 
     */
    public function __unset($key)
    {
        if ($this->isLoaded()) {
            unset($this->data[$key]);
        }
    }

    /**
     * 
     * Clear all data from the segment.
     * 
     * @return void
     * 
     */
    public function clear()
    {
        if ($this->isLoaded()) {
            $this->data = [];
        }
    }

    /**
     * 
     * Gets the segment name.
     * 
     * @return string
     * 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 
     * Sets a read-once flash value on the segment.
     * 
     * @param string $key The key for the flash value.
     * 
     * @param mixed $val The flash value itself.
     * 
     */
    public function setFlash($key, $val)
    {
        $this->load();
        $this->data['__flash'][$key] = $val;
    }

    /**
     * 
     * Reads the flash value for a key, thereby removing it from the session.
     * 
     * @param string $key The key for the flash value.
     * 
     * @return mixed The flash value itself.
     * 
     */
    public function getFlash($key)
    {
        if ($this->isLoaded() && isset($this->data['__flash'][$key])) {
            $val = $this->data['__flash'][$key];
            unset($this->data['__flash'][$key]);
            return $val;
        }
    }

    /**
     * 
     * Checks whether a flash key is set, without reading it.
     * 
     * @param string $key The flash key to check.
     * 
     * @return bool True if it is set, false if not.
     * 
     */
    public function hasFlash($key)
    {
        if ($this->isLoaded()) {
            return isset($this->data['__flash'][$key]);
        }
        return false;
    }

    /**
     * 
     * Clears all flash values.
     * 
     * @return void
     * 
     */
    public function clearFlash()
    {
        if ($this->isLoaded()) {
            unset($this->data['__flash']);
        }
    }
}

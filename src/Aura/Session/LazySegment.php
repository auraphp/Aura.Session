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
 * A "lazy" segment object: if a session is already active, uses it;
 * otherwise, starts a new session only on write.
 * 
 * @package Aura.Session
 * 
 */
class LazySegment implements SegmentInterface
{
    /**
     * 
     * The actual segment object; this gets created only if a session is
     * already active, or on write.
     * 
     * @var Segment
     * 
     */
    protected $segment;
    
    /**
     * 
     * The session manager; used to get the segment, thereby starting a session.
     * 
     * @var Manager
     * 
     */
    protected $manager;
    
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
     * @param Manager $manager The session manager.
     * 
     * @param string $name The segment name.
     * 
     */
    public function __construct(Manager $manager, $name)
    {
        $this->manager = $manager;
        $this->name = $name;
    }
    
    /**
     * 
     * Loads the segment from the manager, thereby starting the session.
     * 
     */
    protected function loadSegment()
    {
        if (! $this->segment) {
            $this->segment = $this->manager->getSegment($this->name);
        }
    }
    
    /**
     * 
     * Checks to see if the segment is loaded; if a session is already 
     * active, loads it.
     * 
     * @return bool
     * 
     */
    protected function isLoaded()
    {
        // if we already have a segment, it's loaded
        if ($this->segment) {
            return true;
        }
        
        // we have no segment. is a session available?
        if ($this->manager->isActive() || $this->manager->isStarted()) {
            // yes, load it.
            $this->loadSegment();
            return true;
        }
        
        // we have no segment, and no active session. not loaded.
        return false;
    }
    
    /**
     * 
     * Returns a reference to the value of a key in the segment; does not
     * start a session.
     * 
     * @param string $key The key in the segment.
     * 
     * @return mixed
     * 
     */
    public function &__get($key)
    {
        $val = null;
        if ($this->isLoaded()) {
            $val =& $this->segment->$key;
        }
        return $val;
    }

    /**
     * 
     * Sets the value of a key in the segment; starts a session.
     * 
     * @param string $key The key to set.
     * 
     * @param mixed $val The value to set it to.
     * 
     */
    public function __set($key, $val)
    {
        $this->loadSegment();
        $this->segment->$key = $val;
    }

    /**
     * 
     * Checks whether a key is set in the segment; does not start a session.
     * 
     * @param string $key The key to check.
     * 
     * @return bool|null True/false if the segment is active, null if not.
     * 
     */
    public function __isset($key)
    {
        if ($this->isLoaded()) {
            return isset($this->segment->$key);
        }
        return false;
    }

    /**
     * 
     * Unsets a key in the segment; does not start a session.
     * 
     * @param string $key The key to unset.
     * 
     * @return void
     * 
     */
    public function __unset($key)
    {
        if ($this->isLoaded()) {
            unset($this->segment->$key);
        }
    }

    /**
     * 
     * Clear all data from the segment; does not start a session.
     * 
     * @return void
     * 
     */
    public function clear()
    {
        if ($this->isLoaded()) {
            $this->segment->clear();
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
        $this->loadSegment();
        return $this->segment->setFlash($key, $val);
    }

    /**
     * 
     * Reads the flash value for a key, thereby removing it from the session;
     * does not start a session.
     * 
     * @param string $key The key for the flash value.
     * 
     * @return mixed The flash value itself.
     * 
     */
    public function getFlash($key)
    {
        if ($this->isLoaded()) {
            return $this->segment->getFlash($key);
        }
    }

    /**
     * 
     * Checks whether a flash key is set, without reading it; does not start
     * a session.
     * 
     * @param string $key The flash key to check.
     * 
     * @return bool True if it is set, false if not.
     * 
     */
    public function hasFlash($key)
    {
        if ($this->isLoaded()) {
            return $this->segment->hasFlash($key);
        }
        return false;
    }

    /**
     * 
     * Clears all flash values; does not start a session.
     * 
     * @return void
     * 
     */
    public function clearFlash()
    {
        if ($this->isLoaded()) {
            $this->segment->clearFlash();
        }
    }
}

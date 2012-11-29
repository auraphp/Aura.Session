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
 * A session segment.
 * 
 * @package Aura.Session
 * 
 */
class Segment
{
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
     * The data in the segment.
     * 
     * @var array
     * 
     */
    protected $data;

    /**
     * 
     * Constructor
     * 
     * @param string $name The name of the segment.
     * 
     * @param array &$data The reference to segment data, typically from
     * $_SESSION.
     * 
     */
    public function __construct($name, &$data)
    {
        $this->name = $name;
        $this->data = &$data;
    }

    /**
     * 
     * Returns a reference to the value of a key in the segment.
     * 
     * @param string $key The key in the segment.
     * 
     * @return mixed
     * 
     */
    public function &__get($key)
    {
        return $this->data[$key];
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
        return isset($this->data[$key]);
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
        unset($this->data[$key]);
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
        $this->data = [];
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
        if (isset($this->data['__flash'][$key])) {
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
        return isset($this->data['__flash'][$key]);
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
        unset($this->data['__flash']);
    }
}

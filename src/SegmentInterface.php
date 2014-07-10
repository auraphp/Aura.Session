<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @package Aura.Session
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Session;

/**
 *
 * An interface for session segment objects.
 *
 * @package Aura.Session
 *
 */
interface SegmentInterface
{
    /**
     *
     * Returns the value of a key in the segment.
     *
     * @param string $key The key in the segment.
     *
     * @return mixed
     *
     */
    public function get($key, $alt = null);

    /**
     *
     * Sets the value of a key in the segment.
     *
     * @param string $key The key to set.
     *
     * @param mixed $val The value to set it to.
     *
     */
    public function set($key, $val);

    /**
     *
     * Clear all data from the segment.
     *
     * @return null
     *
     */
    public function clear();

    /**
     *
     * Sets a read-once flash value on the segment.
     *
     * @param string $key The key for the flash value.
     *
     * @param mixed $val The flash value itself.
     *
     */
    public function setFlash($key, $val);

    /**
     *
     * Reads the flash value for a key, thereby removing it from the session.
     *
     * @param string $key The key for the flash value.
     *
     * @return mixed The flash value itself.
     *
     */
    public function getFlash($key, $alt = null);

    /**
     *
     * Clears all flash values.
     *
     * @return null
     *
     */
    public function clearFlash();
}

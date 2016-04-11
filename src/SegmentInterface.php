<?php
/**
 *
 * This file is part of Aura for PHP.
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
     * @param mixed $alt An alternative value to return if the key is not set.
     *
     * @return mixed
     *
     */
    public function get($key, $alt = null);

    /**
     *
     * Returns the entire segment.
     *
     * @return mixed
     *
     */
    public function getSegment();

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
     * Append a value to a numeric key in the segment.
     *
     * @param mixed $val The value to append.
     *
     */
    public function add($val);

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
     * Sets a flash value for the *next* request.
     *
     * @param string $key The key for the flash value.
     *
     * @param mixed $val The flash value itself.
     *
     */
    public function setFlash($key, $val);

    /**
     *
     * Append a flash value with a numeric key for the *next* request.
     *
     * @param mixed $val The flash value itself.
     *
     */
    public function addFlash($val);

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
    public function getFlash($key, $alt = null);

    /**
     *
     * Gets all the flash values in the *current* request.
     *
     * @param mixed $alt An alternative value to return if no flash values are set.
     *
     * @return mixed The flash values themselves.
     *
     */
    public function getAllCurrentFlash($alt = array());

    /**
     *
     * Clears flash values for *only* the next request.
     *
     * @return null
     *
     */
    public function clearFlash();

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
    public function getFlashNext($key, $alt = null);

    /**
     *
     * Gets all flash values for the *next* request.
     *
     * @param mixed $alt An alternative value to return if no flash values set.
     *
     * @return mixed The flash values themselves.
     *
     */
    public function getAllFlashNext($alt = array());

    /**
     *
     * Sets a flash value for the *next* request *and* the current one.
     *
     * @param string $key The key for the flash value.
     *
     * @param mixed $val The flash value itself.
     *
     */
    public function setFlashNow($key, $val);

    /**
     *
     * Append a flash value with a numeric key for the *next* request *and* the current one.
     *
     * @param mixed $val The flash value itself.
     *
     */
    public function addFlashNow($val);

    /**
     *
     * Clears flash values for *both* the next request *and* the current one.
     *
     * @return null
     *
     */
    public function clearFlashNow();

    /**
     *
     * Retains all the current flash values for the next request; values that
     * already exist for the next request take precedence.
     *
     * @return null
     *
     */
    public function keepFlash();

    /**
     * Remove a key from the segment, or remove the entire segment (including key) from the session
     *
     * @param null $key
     */
    public function remove($key = null);
}

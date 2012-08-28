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
 * Segment
 * 
 * @package Aura.Session
 * 
 */
class Segment
{
    /**
     *
     * segment name
     * 
     * @var string 
     * 
     */
    protected $name;

    /**
     * 
     * @var string|array
     * 
     */
    protected $data;

    /**
     * 
     * constructor
     * 
     * @param string $name
     * 
     * @param string|array $data
     * 
     */
    public function __construct($name, &$data)
    {
        $this->name = $name;
        $this->data = &$data;
    }

    /**
     * 
     * returns the data
     * 
     * @param string $key
     * 
     * @return string|array
     * 
     */
    public function &__get($key)
    {
        return $this->data[$key];
    }

    /**
     * 
     * set the data for the key
     * 
     * @param string $key
     * 
     * @param string|array $val
     * 
     */
    public function __set($key, $val)
    {
        $this->data[$key] = $val;
    }

    /**
     * 
     * check whether the data is set for the key
     * 
     * @param string $key
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
     * unset the data for the key
     * 
     * @param string $key
     * 
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * 
     * clear all data
     * 
     */
    public function clear()
    {
        $this->data = [];
    }

    /**
     * 
     * return segment name
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
     * set flash message
     * 
     * @param string $key
     * 
     * @param mixed $val
     * 
     */
    public function setFlash($key, $val)
    {
        $this->data['__flash'][$key] = $val;
    }

    /**
     * 
     * get the flash message for the key
     * 
     * @param string $key
     * 
     * @return mixed
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
     * check whether the flash message is there for the key
     * 
     * @param string $key
     * 
     * @return bool
     */
    public function hasFlash($key)
    {
        return isset($this->data['__flash'][$key]);
    }

    /**
     * 
     * clear all flash message
     * 
     */
    public function clearFlash()
    {
        unset($this->data['__flash']);
    }
}

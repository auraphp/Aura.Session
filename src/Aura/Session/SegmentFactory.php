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
 * A factory to create session segment objects.
 * 
 * @package Aura.Session
 * 
 */
class SegmentFactory
{
    /**
     * 
     * Creates a session segment object.
     * 
     * @param Manager $manager
     * @param string  $name
     *
     * @return Segment
     */
    public function newInstance(Manager $manager, $name)
    {
        return new Segment($manager, $name);
    }
}

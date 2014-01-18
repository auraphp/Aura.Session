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
     * @param Session $manager
     * @param string  $name
     *
     * @return Segment
     */
    public function newInstance(Session $manager, $name)
    {
        return new Segment($manager, $name);
    }
}

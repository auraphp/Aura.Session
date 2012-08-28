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
 * A factory to create csrf token
 * 
 * @package Aura.Session
 * 
 */
class CsrfTokenFactory
{
    /**
     * 
     * create a CsrfToken object
     * 
     * @param \Aura\Session\Manager $manager
     * 
     * @return \Aura\Session\CsrfToken
     * 
     */
    public function newInstance(Manager $manager)
    {
        $segment = $manager->getSegment('Aura\Session\CsrfToken');
        return new CsrfToken($segment);
    }
}

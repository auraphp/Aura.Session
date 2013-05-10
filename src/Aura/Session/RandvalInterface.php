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
 * Interface for generating cryptographically-secure random values.
 * 
 * @package Aura.Session
 * 
 */
interface RandvalInterface
{
    /**
     * 
     * Returns a cryptographically secure random value.
     * 
     * @return string
     * 
     */
    public function generate();
}

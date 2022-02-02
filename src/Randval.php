<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 *
 */
namespace Aura\Session;

use Aura\Session\Exception;

/**
 *
 * Generates cryptographically-secure random values.
 *
 * @package Aura.Session
 *
 */
class Randval implements RandvalInterface
{
    /**
     *
     * Returns a cryptographically secure random value.
     *
     * @return string
     *
     */
    public function generate()
    {
        return random_bytes(32);
    }
}

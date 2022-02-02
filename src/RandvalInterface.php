<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
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

<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
     * An object to intercept PHP function calls; this makes testing easier.
     *
     * @var Phpfunc
     *
     */
    protected $phpfunc;

    /**
     *
     * Constructor.
     *
     * @param Phpfunc $phpfunc An object to intercept PHP function calls;
     * this makes testing easier.
     *
     */
    public function __construct(Phpfunc $phpfunc)
    {
        $this->phpfunc = $phpfunc;
    }

    /**
     *
     * Returns a cryptographically secure random value.
     *
     * @return string
     */
    public function generate()
    {
        return $this->phpfunc->random_bytes(32);
    }
}

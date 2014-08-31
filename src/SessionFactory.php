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
 * A factory to create a Session manager.
 *
 * @package Aura.Session
 *
 */
class SessionFactory
{
    /**
     *
     * Creates a new Session manager.
     *
     * @param array $cookies An array of cookie values, typically $_COOKIE.
     *
     * @param callable $delete_cookie A alternative callable to invoke when
     * deleting the session cookie.
     *
     */
    public function newInstance(array $cookies, $delete_cookie = null)
    {
        $phpfunc = new Phpfunc;
        return new Session(
            new SegmentFactory,
            new CsrfTokenFactory(new Randval($phpfunc)),
            $phpfunc,
            $cookies,
            $delete_cookie
        );
    }
}

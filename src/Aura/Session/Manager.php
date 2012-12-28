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
 * A central control point for new session segments, PHP session management
 * values, and CSRF token checking.
 * 
 * @package Aura.Session
 * 
 */
class Manager
{
    /**
     *
     * A session segment factory.
     * 
     * @var SegmentFactory 
     * 
     */
    protected $segment_factory;

    /**
     *
     * The CSRF token for this session.
     * 
     * @var CsrfToken
     * 
     */
    protected $csrf_token;

    /**
     * 
     * A CSRF token factory, for lazy-creating the CSRF token.
     * 
     * @var CsrfTokenFactory
     * 
     */
    protected $csrf_token_factory;

    /**
     * 
     * Incoming cookies from the client, typically a copy of the $_COOKIE
     * superglobal.
     * 
     * @var array
     * 
     */
    protected $cookies;
    
    /**
     * 
     * Session cookie parameters.
     * 
     * @var array
     * 
     */
    protected $cookie_params = [];

    /**
     * 
     * Constructor
     * 
     * @param SegmentFactory $segment_factory A session segment factory.
     * 
     * @param CsrfTokenFactory A CSRF token factory.
     * 
     * @param array $cookies An arry of cookies from the client, typically a
     * copy of $_COOKIE.
     * 
     */
    public function __construct(
        SegmentFactory   $segment_factory,
        CsrfTokenFactory $csrf_token_factory,
        array $cookies = []
    ) {
        $this->segment_factory    = $segment_factory;
        $this->csrf_token_factory = $csrf_token_factory;
        $this->cookies            = $cookies;
        $this->cookie_params      = session_get_cookie_params();
    }

    /**
     * 
     * Gets a new session segment instance by name. Segments with the same
     * name will be different objects but will reference the same $_SESSION
     * values, so it is possible to have two or more objects that share state.
     * For good or bad, this a function of how $_SESSION works.
     * 
     * @param string $name The name of the session segment, typically a 
     * fully-qualified class name.
     * 
     * @return Segment
     * 
     */
    public function newSegment($name)
    {
        return $this->segment_factory->newInstance($this, $name);
    }

    /**
     * 
     * Tells us if a session is available to be reactivated, but not if it has
     * started yet.
     * 
     * @return bool
     * 
     */
    public function isAvailable()
    {
        $name = $this->getName();
        return isset($this->cookies[$name]);
    }

    /**
     * 
     * Tells us if a session has started.
     * 
     * @return bool
     * 
     */
    public function isStarted()
    {
        return $this->getStatus() == PHP_SESSION_ACTIVE;
    }

    /**
     * 
     * Starts a new session, or resumes an existing one.
     * 
     * @return bool
     * 
     */
    public function start()
    {
        return session_start();
    }

    /**
     * 
     * Clears all session variables across all segments.
     * 
     * @return null
     * 
     */
    public function clear()
    {
        return session_unset();
    }

    /**
     * 
     * Writes session data from all segments and ends the session.
     * 
     * @return null
     * 
     */
    public function commit()
    {
        return session_write_close();
    }

    /**
     * 
     * Destroys the session entirely.
     * 
     * @return bool
     * 
     */
    public function destroy()
    {
        $this->clear();
        return session_destroy();
    }

    /**
     * 
     * Returns the CSRF token, creating it if needed (and thereby starting a
     * session).
     * 
     * @return CsrfToken
     * 
     */
    public function getCsrfToken()
    {
        if (! $this->csrf_token) {
            $this->csrf_token = $this->csrf_token_factory->newInstance($this);
        }

        return $this->csrf_token;
    }

    // =======================================================================
    //
    // support and admin methods
    //

    /**
     * 
     * Sets the session cache expire time.
     * 
     * @param int $expire The expiration time in seconds.
     * 
     * @return int
     * 
     * @see session_cache_expire()
     * 
     */
    public function setCacheExpire($expire)
    {
        return session_cache_expire($expire);
    }

    /**
     * 
     * Gets the session cache expire time.
     * 
     * @return int The cache expiration time in seconds.
     * 
     * @see session_cache_expire()
     * 
     */
    public function getCacheExpire()
    {
        return session_cache_expire();
    }

    /**
     * 
     * Sets the session cache limiter value.
     * 
     * @param string $limiter The limiter value.
     * 
     * @return string
     * 
     * @see session_cache_limiter()
     * 
     */
    public function setCacheLimiter($limiter)
    {
        return session_cache_limiter($limiter);
    }

    /**
     * 
     * Gets the session cache limiter value.
     * 
     * @return string The limiter value.
     * 
     * @see session_cache_limiter()
     * 
     */
    public function getCacheLimiter()
    {
        return session_cache_limiter();
    }

    /**
     * 
     * Sets the session cookie params.  Param array keys are:
     * 
     * - `lifetime` : Lifetime of the session cookie, defined in seconds.
     * 
     * - `path` : Path on the domain where the cookie will work. 
     *   Use a single slash ('/') for all paths on the domain.
     * 
     * - `domain` : Cookie domain, for example 'www.php.net'. 
     *   To make cookies visible on all subdomains then the domain must be 
     *   prefixed with a dot like '.php.net'.
     * 
     * - `secure` : If TRUE cookie will only be sent over secure connections.
     * 
     * - `httponly` : If set to TRUE then PHP will attempt to send the httponly 
     *   flag when setting the session cookie.
     * 
     * @param array $params The array of session cookie param keys and values.
     * 
     * @return void
     * 
     * @see session_set_cookie_params()
     * 
     */
    public function setCookieParams(array $params)
    {
        $this->cookie_params = array_merge($this->cookie_params, $params);
        session_set_cookie_params(
            $this->cookie_params['lifetime'],
            $this->cookie_params['path'],
            $this->cookie_params['domain'],
            $this->cookie_params['secure'],
            $this->cookie_params['httponly']
        );
    }

    /**
     * 
     * Gets the session cookie params.
     * 
     * @return array
     * 
     */
    public function getCookieParams()
    {
        return $this->cookie_params;
    }

    /**
     * 
     * Gets the current session id.
     * 
     * @return string
     * 
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * 
     * Regenerates and replaces the current session id; also regenerates the
     * CSRF token value if one exists.
     * 
     * @return bool True is regeneration worked, false if not.
     * 
     */
    public function regenerateId()
    {
        $result = session_regenerate_id(true);
        if ($result && $this->csrf_token) {
            $this->csrf_token->regenerateValue();
        }
        return $result;
    }

    /**
     * 
     * Sets the current session name.
     * 
     * @param string $name The session name to use.
     * 
     * @return string
     * 
     * @see session_name()
     * 
     */
    public function setName($name)
    {
        return session_name($name);
    }

    /**
     * 
     * Returns the current session name.
     * 
     * @return string
     * 
     */
    public function getName()
    {
        return session_name();
    }

    /**
     * 
     * Sets the session save path.
     * 
     * @param string $path The new save path.
     * 
     * @return string
     * 
     * @see session_save_path()
     * 
     */
    public function setSavePath($path)
    {
        return session_save_path($path);
    }

    /**
     * 
     * Gets the session save path.
     * 
     * @return string
     * 
     * @see session_save_path()
     * 
     */
    public function getSavePath()
    {
        return session_save_path();
    }

    /**
     * 
     * Returns the current session status:
     * 
     * - `PHP_SESSION_DISABLED` if sessions are disabled.
     * - `PHP_SESSION_NONE` if sessions are enabled, but none exists.
     * - `PHP_SESSION_ACTIVE` if sessions are enabled, and one exists.
     * 
     * @return int
     * 
     * @see session_status()
     * 
     */
    public function getStatus()
    {
        return session_status();
    }
}

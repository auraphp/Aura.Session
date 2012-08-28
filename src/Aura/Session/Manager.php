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
 * we can't inject $_SESSION. the problem is, it is not available
 * until after session_start().  this means we can't lazy-start the
 * sessions *and* inject session data at the same time. and, session_start()
 * kills off any previous $_SESSION values.
 * 
 * we don't do lazy session-starting, exactly. in theory it shouldn't try to
 * start until you read or write, but if you call getSegment(), we assume
 * you're going to be reading and writing, so we start a session when you get
 * a segment.
 * 
 *  * @package Aura.Session
 * 
 */
class Manager
{
    /**
     *
     * Aura\Session\Segment instances
     * 
     * @var array
     */
    protected $segment = [];

    /**
     *
     * Aura\Session\SegmentFactory object
     * 
     * @var Aura\Session\SegmentFactory 
     * 
     */
    protected $segment_factory;

    /**
     *
     * csrf token
     * 
     * @var CsrfToken
     * 
     */
    protected $csrf_token;

    /**
     *
     * @var Aura\Session\CsrfTokenFactory
     */
    protected $csrf_token_factory;

    /**
     * 
     * cookie params
     * 
     * @var array cookie params
     * 
     */
    protected $cookie_params = [];

    /**
     * constructor
     * 
     * @param \Aura\Session\SegmentFactory $segment_factory
     * 
     * @param \Aura\Session\CsrfTokenFactory $csrf_token_factory
     * 
     */
    public function __construct(
        SegmentFactory   $segment_factory,
        CsrfTokenFactory $csrf_token_factory
    ) {
        $this->segment_factory    = $segment_factory;
        $this->csrf_token_factory = $csrf_token_factory;
        $this->cookie_params      = session_get_cookie_params();
    }

    // 
    /**
     * 
     * gets a session segment. starts the session if needed.
     * 
     * @param string $name
     * 
     * @return \Aura\Session\Segment object of Aura\Session\Segment
     * 
     */
    public function getSegment($name)
    {
        // start session if needed
        if (! $this->isStarted()) {
            $this->start();
        }

        // create a segment object if needed
        if (! isset($this->segment[$name])) {
            // create and retain the segment object
            $segment = $this->segment_factory->newInstance($name);
            $this->segment[$name] = $segment;
        }

        // return the segment object
        return $this->segment[$name];
    }

    /**
     * 
     * tells us if a session *exists*, not if it has started yet
     * 
     * @return bool
     */
    public function isActive()
    {
        return $this->getStatus() == PHP_SESSION_ACTIVE;
    }

    /**
     * tells us if a session has started
     * 
     * @return bool
     * 
     */
    public function isStarted()
    {
        return $this->getId() !== '';
    }

    /**
     * 
     * Start new or resume existing session
     * 
     * @return bool
     */
    public function start()
    {
        return session_start();
    }

    /**
     * 
     * Free all session variables
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
     * Write session data and end session
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
     * Destroys all data registered to a session
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
     * get a CsrfToken object
     * 
     * @return \Aura\Session\CsrfToken
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
     * the current cache expire is replaced with $expire
     * 
     * @param string $expire
     * 
     * @return int
     * 
     */
    public function setCacheExpire($expire)
    {
        return session_cache_expire($expire);
    }

    /**
     * 
     * Return current cache expire
     * 
     * @return int
     * 
     */
    public function getCacheExpire()
    {
        return session_cache_expire();
    }

    /**
     * 
     * set the current cache limiter
     * 
     * @param type $limiter
     * 
     * @return string
     * 
     */
    public function setCacheLimiter($limiter)
    {
        return session_cache_limiter($limiter);
    }

    /**
     * 
     * Get the current cache limiter
     * 
     * @return string
     * 
     */
    public function getCacheLimiter()
    {
        return session_cache_limiter();
    }

    /**
     * 
     * Set the session cookie params
     * 
     * Where params as 
     * 
     * lifetime : Lifetime of the session cookie, defined in seconds.
     * 
     * path : Path on the domain where the cookie will work. 
     * Use a single slash ('/') for all paths on the domain.
     * 
     * domain : Cookie domain, for example 'www.php.net'. 
     * To make cookies visible on all subdomains then the domain must be 
     * prefixed with a dot like '.php.net'.
     * 
     * secure : If TRUE cookie will only be sent over secure connections.
     * 
     * httponly : If set to TRUE then PHP will attempt to send the httponly 
     * flag when setting the session cookie.
     * 
     * @param array $params
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
     * Return cookie params
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
     * Get the current session id
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
     * Update the current session id with a newly generated one
     * 
     * @return bool
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
     * set the current session name
     * 
     * @param string $name
     * 
     * @return string
     * 
     */
    public function setName($name)
    {
        return session_name($name);
    }

    /**
     * 
     * Get the current session name
     * 
     * @return string
     */
    public function getName()
    {
        return session_name();
    }

    /**
     * 
     * set the current session save path
     * 
     * @param string $path
     * 
     * @return string
     * 
     */
    public function setSavePath($path)
    {
        return session_save_path($path);
    }

    /**
     * 
     * Get the current session save path
     * 
     * @return string
     * 
     */
    public function getSavePath()
    {
        return session_save_path();
    }

    /**
     * 
     * Returns the current session status
     * 
     * PHP_SESSION_DISABLED if sessions are disabled.
     * PHP_SESSION_NONE if sessions are enabled, but none exists.
     * PHP_SESSION_ACTIVE if sessions are enabled, and one exists.
     * 
     * @return type
     * 
     */
    public function getStatus()
    {
        return session_status();
    }
}

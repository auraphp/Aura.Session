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
 * Define constants for PHP versions earlier than 5.4.
 */
if (! defined('PHP_SESSION_DISABLED')) {
    define('PHP_SESSION_DISABLED', 0);
}

if (! defined('PHP_SESSION_NONE')) {
    define('PHP_SESSION_NONE', 1);
}

if (! defined('PHP_SESSION_ACTIVE')) {
    define('PHP_SESSION_ACTIVE', 2);
}

/**
 *
 * A central control point for new session segments, PHP session management
 * values, and CSRF token checking.
 *
 * @package Aura.Session
 *
 */
class Session
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
    protected $cookie_params = array();

    protected $phpfunc;

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
        SegmentFactory $segment_factory,
        CsrfTokenFactory $csrf_token_factory,
        PhpFunc $phpfunc,
        array $cookies = array(),
        $destroy_cookie = null
    ) {
        $this->segment_factory    = $segment_factory;
        $this->csrf_token_factory = $csrf_token_factory;
        $this->phpfunc            = $phpfunc;
        $this->cookies            = $cookies;

        $this->setDestroyCookie($destroy_cookie);

        $this->cookie_params = $this->phpfunc->session_get_cookie_params();
    }

    protected function setDestroyCookie($destroy_cookie)
    {
        $this->destroy_cookie = $destroy_cookie;
        if (! $this->destroy_cookie) {
            $phpfunc = $this->phpfunc;
            $this->destroy_cookie = function (
                $name,
                $path,
                $domain
            ) use ($phpfunc) {
                $phpfunc->setcookie(
                    $name,
                    '',
                    time() - 42000,
                    $path,
                    $domain
                );
            };
        }
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
     * Is a session available to be resumed?
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
     * Is the session already started?
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
     * Starts a new or existing session.
     *
     * @return bool
     *
     */
    public function start()
    {
        return $this->phpfunc->session_start();
    }

    /**
     *
     * Resumes an available session, but does not start a new one if there is no
     * existing one.
     *
     * @return bool
     *
     */
    public function resume()
    {
        if ($this->isStarted()) {
            return true;
        }

        if ($this->isAvailable()) {
            return $this->start();
        }

        return false;
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
        return $this->phpfunc->session_unset();
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
        return $this->phpfunc->session_write_close();
    }

    /**
     *
     * Destroys the session entirely.
     *
     * @return bool
     *
     * @see http://php.net/manual/en/function.session-destroy.php
     *
     */
    public function destroy()
    {
        if (! $this->isStarted()) {
            $this->start();
        }

        $name = $this->getName();
        $params = $this->getCookieParams();
        $this->clear();

        $destroyed = $this->phpfunc->session_destroy();
        if ($destroyed) {
            call_user_func(
                $this->destroy_cookie,
                $name,
                $params['path'],
                $params['domain']
            );
        }

        return $destroyed;
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
        return $this->phpfunc->session_cache_expire($expire);
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
        return $this->phpfunc->session_cache_expire();
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
        return $this->phpfunc->session_cache_limiter($limiter);
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
        return $this->phpfunc->session_cache_limiter();
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
        $this->phpfunc->session_set_cookie_params(
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
        return $this->phpfunc->session_id();
    }

    /**
     *
     * Regenerates and replaces the current session id; also regenerates the
     * CSRF token value if one exists.
     *
     * @return bool True if regeneration worked, false if not.
     *
     */
    public function regenerateId()
    {
        $result = $this->phpfunc->session_regenerate_id(true);
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
        return $this->phpfunc->session_name($name);
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
        return $this->phpfunc->session_name();
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
        return $this->phpfunc->session_save_path($path);
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
        return $this->phpfunc->session_save_path();
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
     * @see http://stackoverflow.com/questions/3788369/how-to-tell-if-a-session-is-active/7656468#7656468
     *
     */
    public function getStatus()
    {
        if ($this->phpfunc->function_exists('session_status')) {
            return $this->phpfunc->session_status();
        }

        // PHP 5.3 implementation of session_status.
        // Relies on the fact that ini setting 'session.use_trans_sid' cannot be
        // changed when a session is active.
        $setting = 'session.use_trans_sid';
        $current = $this->phpfunc->ini_get($setting);
        if ($current === false) {
            return PHP_SESSION_DISABLED;
        }

        // ini_set raises a warning when we attempt to change this setting
        // and session is active
        $level = $this->phpfunc->error_reporting(0);
        $result = $this->phpfunc->ini_set($setting, $current);
        $this->phpfunc->error_reporting($level);

        return $result !== $current
             ? PHP_SESSION_ACTIVE
             : PHP_SESSION_NONE;
    }
}

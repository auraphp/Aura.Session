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
 * Cross-site request forgery token tools.
 *
 * @package Aura.Session
 *
 */
class CsrfToken
{
    /**
     *
     * A cryptographically-secure random value generator.
     *
     * @var RandvalInterface
     *
     */
    protected $randval;

    /**
     *
     * Session segment for values in this class.
     *
     * @var SegmentInterface
     *
     */
    protected $segment;

    /**
     *
     * Constructor.
     *
     * @param SegmentInterface $segment A segment for values in this class.
     *
     * @param RandvalInterface $randval A cryptographically-secure random
     * value generator.
     *
     */
    public function __construct(SegmentInterface $segment, RandvalInterface $randval)
    {
        $this->segment = $segment;
        $this->randval = $randval;
    }

    /**
     *
     * Checks whether an incoming CSRF token value is valid.
     *
     * @param string $value The incoming token value.
     *
     * @param string $key  A string key name which session value is saved.
     *
     * @return bool True if valid, false if not.
     *
     */
    public function isValid($value, $key = 'value')
    {
        return hash_equals($value, $this->getValue($key));
    }

    /**
     *
     * Gets the value of the outgoing CSRF token.
     *
     * @param string $key  A string key name which session value is saved.
     *
     * @return string
     *
     */
    public function getValue($key = 'value')
    {
        if ($this->segment->get($key) == null ) {
            $this->regenerateValue($key);
        }

        return $this->segment->get($key);
    }

    /**
     *
     * Regenerates the value of the outgoing CSRF token.
     *
     * @param string $key  A string key name which session value is saved.
     *
     * @return string
     *
     */
    public function regenerateValue($key = 'value')
    {
        $hash = hash('sha512', $this->randval->generate());
        $this->segment->set($key, $hash);

        return $this->segment->get($key);
    }


    /**
     * Regenerates all csrf tokens
     *
     * @return void
     */
    public function regenerateAllKeyValues()
    {
        $segment = $this->segment->getSegment();

        if ($segment) {
            foreach ($segment as $key => $value) {
                $this->regenerateValue($key);
            }
        }
    }

}

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

use Aura\Session\SegmentFactory;

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
     * Session segment for values in this class.
     * 
     * @var Segment 
     * 
     */
    protected $segment;

    /**
     * 
     * Constructor.
     * 
     * @param Segment $segment A segment for values in this class.
     * 
     */
    public function __construct(Segment $segment)
    {
        $this->segment = $segment;
        if (! isset($this->segment->value)) {
            $this->regenerateValue();
        }
    }

    /**
     * 
     * Checks whether an incoming CSRF token value is valid.
     * 
     * @param string $value The incoming token value.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function isValid($value)
    {
        return $value === $this->getValue();
    }

    /**
     * 
     * Gets the value of the outgoing CSRF token.
     * 
     * @return string
     * 
     */
    public function getValue()
    {
        return $this->segment->value;
    }

    /**
     * 
     * Regenerates the value of the outgoing CSRF token.
     * 
     */
    public function regenerateValue()
    {
        $this->segment->value = uniqid(mt_rand(), true);
    }
}

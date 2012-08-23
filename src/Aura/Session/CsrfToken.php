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
 * CsrfToken
 * 
 * @package Aura.Session
 * 
 */
class CsrfToken
{
    /**
     *
     * \Aura\Session\Segment object
     * 
     * @var \Aura\Session\Segment 
     */
    protected $segment;

    /**
     * 
     * constructor
     * 
     * @param \Aura\Session\Segment $segment
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
     * check whether segment value is same
     * 
     * @param string $value
     * 
     * @return bool
     * 
     */
    public function isValid($value)
    {
        return $value === $this->segment->value;
    }

    /**
     * 
     * get segment value
     * 
     * @return type
     * 
     */
    public function getValue()
    {
        return $this->segment->value;
    }

    /**
     * 
     * regenerate segment value
     * 
     */
    public function regenerateValue()
    {
        $this->segment->value = uniqid(mt_rand(), true);
    }
}


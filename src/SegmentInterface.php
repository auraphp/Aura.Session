<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 *
 */
namespace Aura\Session;

use Aura\Session_Interface\SegmentInterface as BaseSegmentInterface;
use Aura\Session_Interface\ManageableSegmentInterface;
use Aura\Session_Interface\FlashSegmentInterface;

/**
 *
 * An interface for session segment objects.
 *
 * It composes the shared, light-weight contracts from Aura.Session_Interface:
 * {@see BaseSegmentInterface} (plain read/write), {@see ManageableSegmentInterface}
 * (whole-segment management), and {@see FlashSegmentInterface} (flash values).
 *
 * @package Aura.Session
 *
 */
interface SegmentInterface extends
    BaseSegmentInterface,
    ManageableSegmentInterface,
    FlashSegmentInterface
{
}

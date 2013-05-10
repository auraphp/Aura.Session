<?php
namespace Aura\Session;

use Aura\Framework\Test\WiringAssertionsTrait;

class WiringTest extends \PHPUnit_Framework_TestCase
{
    use WiringAssertionsTrait;

    protected function setUp()
    {
        $this->loadDi();
    }

    public function testServices()
    {
        $this->assertGet('session_manager', 'Aura\Session\Manager');
    }

    public function testInstances()
    {
        $this->assertNewInstance('Aura\Session\CsrfTokenFactory');
        $this->assertNewInstance('Aura\Session\Manager');
        $this->assertNewInstance('Aura\Session\Segment');
    }
}

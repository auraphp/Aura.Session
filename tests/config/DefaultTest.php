<?php
namespace Aura\Session;

use Aura\Framework\Test\WiringAssertionsTrait;

class DefaultTest extends \PHPUnit_Framework_TestCase
{
    use WiringAssertionsTrait;

    protected function setUp()
    {
        $this->loadDi();
    }

    public function testServices()
    {
        $this->assertGet('session_manager', 'Aura\Session\Session');
    }

    public function testInstances()
    {
        $this->assertNewInstance('Aura\Session\CsrfTokenFactory');
        $this->assertNewInstance('Aura\Session\Session');
        $this->assertNewInstance('Aura\Session\Segment');
    }
}

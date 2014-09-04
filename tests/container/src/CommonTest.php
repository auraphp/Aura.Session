<?php
namespace Aura\Session\_Config;

use Aura\Di\ContainerAssertionsTrait;

class CommonTest extends \PHPUnit_Framework_TestCase
{
    use ContainerAssertionsTrait;

    public function setUp()
    {
        $this->setUpContainer(array(
            'Aura\Session\_Config\Common',
        ));
    }

    public function test()
    {
        $this->assertGet('aura/session:session', 'Aura\Session\Session');
        $this->assertNewInstance('Aura\Session\CsrfTokenFactory');
        $this->assertNewInstance('Aura\Session\Session');
        $this->assertNewInstance('Aura\Session\Randval');
        $this->assertNewInstance('Aura\Session\Segment');
    }
}

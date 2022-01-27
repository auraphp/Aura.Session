<?php
namespace Aura\Session;

use PHPUnit\Framework\TestCase;

class SessionFactoryTest extends TestCase
{
    public function testNewInstance()
    {
        $session_factory = new SessionFactory;
        $session = $session_factory->newInstance($_COOKIE);
        $this->assertInstanceOf('Aura\Session\Session', $session);
    }
}

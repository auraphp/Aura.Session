<?php
namespace Aura\Session;

use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class CsrfTokenTest extends TestCase
{
    protected $session;

    protected $csrf_token;

    protected $name = __CLASS__;

    protected $phpfunc;

    protected function setUp(): void
    {
        $this->phpfunc = new FakePhpfunc;

        $this->session = new Session(
            new SegmentFactory,
            new CsrfTokenFactory(new Randval($this->phpfunc)),
            $this->phpfunc,
            $_COOKIE
        );
    }

    public function teardown(): void
    {
        session_unset();
        if (session_id() !== '') {
            session_destroy();
        }
    }

    public function testLaziness()
    {
        $this->assertFalse($this->session->isStarted());
        $token = $this->session->getCsrfToken();
        $this->assertTrue($this->session->isStarted());
    }

    public function testGetAndRegenerateValue()
    {
        $token = $this->session->getCsrfToken();

        $old = $token->getValue();
        $this->assertTrue($old != '');

        // with openssl
        $this->phpfunc->extensions = array('openssl');
        $token->regenerateValue();
        $openssl = $token->getValue();
        $this->assertTrue($old != $openssl);

        // with mcrypt
        $this->phpfunc->extensions = array('mcrypt');
        $token->regenerateValue();
        $mcrypt = $token->getValue();
        $this->assertTrue($old != $openssl && $old != $mcrypt);

        if (!$this->phpfunc->function_exists('random_bytes')) {
            // with nothing
            $this->phpfunc->extensions = array();
            $this->expectException('Aura\Session\Exception');
            $token->regenerateValue();
        }

    }

    public function testIsValid()
    {
        $token = $this->session->getCsrfToken();
        $value = $token->getValue();

        $this->assertTrue($token->isValid($value));
        $token->regenerateValue();
        $this->assertFalse($token->isValid($value));
    }
}

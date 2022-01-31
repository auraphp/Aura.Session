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
            new CsrfTokenFactory(new Randval()),
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
        $this->assertFalse($this->session->isStarted());
        $token->getValue('__csrf');
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

    public function testDifferentTokens()
    {
        $this->assertFalse($this->session->isStarted());
        $token = $this->session->getCsrfToken();

        $value1 = $token->getValue('__csrf1');
        $value2 = $token->getValue('__csrf2');
        $value3 = $token->getValue('__csrf3');

        $this->assertTrue($token->isValid($value1, '__csrf1'));
        $this->assertTrue($token->isValid($value2, '__csrf2'));
        $this->assertTrue($token->isValid($value3, '__csrf3'));

        // After isValid call, the value stored in session will not be reset
        $this->assertEquals($value3, $token->getValue('__csrf3'));

        $this->assertNotEquals($value3, $token->regenerateValue('__csrf3'));
    }
}

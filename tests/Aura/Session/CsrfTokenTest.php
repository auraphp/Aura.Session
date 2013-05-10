<?php
namespace Aura\Session;

class CsrfTokenTest extends \PHPUnit_Framework_TestCase
{
    protected $session;
    
    protected $csrf_token;
    
    protected $name = __CLASS__;
    
    protected $phpfunc;
    
    protected function setUp()
    {
        $this->phpfunc = new MockPhpfunc;
        
        $this->session = new Manager(
            new SegmentFactory,
            new CsrfTokenFactory(new Randval($this->phpfunc)),
            $_COOKIE
        );
    }
    
    public function teardown()
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
        $this->phpfunc->setExtensions(['openssl']);
        $token->regenerateValue();
        $openssl = $token->getValue();
        $this->assertTrue($old != $openssl);
        
        // with mcrypt
        $this->phpfunc->setExtensions(['mcrypt']);
        $token->regenerateValue();
        $mcrypt = $token->getValue();
        $this->assertTrue($old != $openssl && $old != $mcrypt);
        
        // with nothing
        $this->phpfunc->setExtensions([]);
        $this->setExpectedException('Aura\Session\Exception');
        $token->regenerateValue();
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

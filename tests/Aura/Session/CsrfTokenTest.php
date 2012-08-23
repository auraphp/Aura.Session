<?php
namespace Aura\Session;

class CsrfTokenTest extends \PHPUnit_Framework_TestCase
{
    protected $session;
    
    protected $csrf_token;
    
    protected $name = __CLASS__;
    
    protected function setUp()
    {
        $this->session = new Manager(
            new SegmentFactory,
            new CsrfTokenFactory
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
        
        $token->regenerateValue();
        $new = $token->getValue();
        $this->assertTrue($old != $new);
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

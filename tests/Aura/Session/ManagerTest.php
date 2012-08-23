<?php
namespace Aura\Session;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    // the session object
    protected $session;
    
    protected function setUp()
    {
        session_set_save_handler(new MockSessionHandler);
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
    
    public function testStart()
    {
        $this->session->start();
        $this->assertTrue($this->session->isStarted());
    }
    
    public function testClear()
    {
        // get a test segment and set some data
        $segment = $this->session->getSegment('test');
        $segment->foo = 'bar';
        $segment->baz = 'dib';
        
        $expect = ['test' => ['foo' => 'bar', 'baz' => 'dib']];
        $this->assertSame($expect, $_SESSION);
        
        // now clear it
        $this->session->clear();
        $this->assertSame([], $_SESSION);
    }
    
    public function testDestroy()
    {
        // get a test segment and set some data
        $segment = $this->session->getSegment('test');
        $segment->foo = 'bar';
        $segment->baz = 'dib';
        
        $expect = ['test' => ['foo' => 'bar', 'baz' => 'dib']];
        $this->assertSame($expect, $_SESSION);
        
        // now destroy it
        $this->session->destroy();
        $this->assertFalse($this->session->isStarted());
    }
    
    public function testCommit()
    {
        $this->session->commit();
        $this->assertFalse($this->session->isStarted());
    }
    
    public function testGetSegment()
    {
        // get a test segment
        $segment = $this->session->getSegment('test');
        
        // should have started the session
        $this->assertTrue($this->session->isStarted());
        
        // should have created the fake $_SESSION
        $this->assertSame($_SESSION, ['test' => []]);
        
        // should be a segment instance
        $this->assertInstanceof('Aura\Session\Segment', $segment);
        
        // get it again, should be the same
        $actual = $this->session->getSegment('test');
        $this->assertSame($segment, $actual);
    }
    
    public function testGetCsrfToken()
    {
        $actual = $this->session->getCsrfToken();
        $expect = 'Aura\Session\CsrfToken';
        $this->assertInstanceOf($expect, $actual);
    }
    
    public function testIsActive()
    {
        $this->assertFalse($this->session->isActive());
        $this->session->start();
        $this->assertTrue($this->session->isActive());
    }
    
    public function testGetAndRegenerateId()
    {
        $this->session->start();
        $old_id = $this->session->getId();
        $this->session->regenerateId();
        $new_id = $this->session->getId();
        $this->assertTrue($old_id != $new_id);
        
        // check the csrf token as well
        $old_value = $this->session->getCsrfToken()->getValue();
        $this->session->regenerateId();
        $new_value = $this->session->getCsrfToken()->getValue();
        $this->assertTrue($old_value != $new_value);
    }
    
    public function testSetAndGetName()
    {
        $expect = 'new_name';
        $this->session->setName($expect);
        $actual = $this->session->getName();
        $this->assertSame($expect, $actual);
    }
    
    public function testSetAndGetSavePath()
    {
        $expect = '/new/save/path';
        $this->session->setSavePath($expect);
        $actual = $this->session->getSavePath();
        $this->assertSame($expect, $actual);
    }
    
    public function testSetAndGetCookieParams()
    {
        $expect = $this->session->getCookieParams();
        $expect['lifetime'] = '999';
        $this->session->setCookieParams($expect);
        $actual = $this->session->getCookieParams();
        $this->assertSame($expect, $actual);
    }
    
    public function testSetAndGetCacheExpire()
    {
        $expect = 123;
        $this->session->setCacheExpire($expect);
        $actual = $this->session->getCacheExpire();
        $this->assertSame($expect, $actual);
    }
    
    public function testSetAndGetCacheLimiter()
    {
        $expect = 'private_no_cache';
        $this->session->setCacheLimiter($expect);
        $actual = $this->session->getCacheLimiter();
        $this->assertSame($expect, $actual);
    }
}

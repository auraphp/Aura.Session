<?php
namespace Aura\Session;

class SegmentTest extends \PHPUnit_Framework_TestCase
{
    // the segment object
    protected $segment;
    
    // a hypothetical subarray in $_SESSION
    protected $data = [];
    
    protected $name = __CLASS__;
    
    protected function setUp()
    {
        $this->segment = new Segment($this->name, $this->data);
    }
    
    public function testGetName()
    {
        $this->assertSame($this->name, $this->segment->getName());
    }
    
    public function testMagicMethods()
    {
        // var not set
        $this->assertFalse(isset($this->segment->foo));
        
        // set the var and check isset
        $this->segment->foo = 'bar';
        $this->assertTrue(isset($this->segment->foo));
        
        // is the var value correct?
        $this->assertSame('bar', $this->segment->foo);
        
        // is the var value referenced in the data location?
        $this->assertSame('bar', $this->data['foo']);
        
        // unset and check
        unset($this->segment->foo);
        $this->assertFalse(isset($this->segment->foo));
        
        // set var from outside and check
        $this->data['foo'] = 'zim';
        $this->assertSame('zim', $this->segment->foo);
    }
    
    public function testClear()
    {
        $this->segment->foo = 'bar';
        $this->segment->baz = 'dib';
        $this->assertSame('bar', $this->data['foo']);
        $this->assertSame('dib', $this->data['baz']);
        
        // now clear the data
        $this->segment->clear();
        $this->assertSame([], $this->data);
        $this->assertNull($this->segment->foo);
        $this->assertNull($this->segment->baz);
    }
    
    public function testFlash()
    {
        // no message yet
        $this->assertFalse($this->segment->hasFlash('message'));
        
        // add a message
        $this->segment->setFlash('message', 'doom');
        $this->assertTrue($this->segment->hasFlash('message'));
        
        // read the message
        $expect = 'doom';
        $actual = $this->segment->getFlash('message');
        $this->assertSame($expect, $actual);
        
        // read-once means the message should be gone now
        $this->assertFalse($this->segment->hasFlash('message'));
        $this->assertNull($this->segment->getFlash('message'));
        
        // add message then clear it
        $this->segment->setFlash('message', 'doom');
        $this->segment->clearFlash();
        $this->assertFalse($this->segment->hasFlash('message'));
        $this->assertNull($this->segment->getFlash('message'));
    }
}

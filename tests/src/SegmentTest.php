<?php
namespace Aura\Session;

class SegmentTest extends \PHPUnit_Framework_TestCase
{
    protected $session;

    protected $segment;

    protected $name = __CLASS__;

    protected function setUp()
    {
        $this->session = $this->newSession();
        $this->segment = $this->session->newSegment($this->name);
    }

    protected function newSession(array $cookies = array())
    {
        return new Session(
            new SegmentFactory,
            new CsrfTokenFactory(new Randval(new Phpfunc)),
            new Phpfunc,
            $cookies
        );
    }

    protected function getValue($key = null)
    {
        if ($key) {
            return $_SESSION[$this->name][$key];
        } else {
            return $_SESSION[$this->name];
        }
    }

    protected function setValue($key, $val)
    {
        $_SESSION[$this->name][$key] = $val;
    }

    public function testMagicMethods()
    {
        $this->assertNull($this->segment->get('foo'));

        $this->segment->set('foo', 'bar');
        $this->assertSame('bar', $this->segment->get('foo'));
        $this->assertSame('bar', $this->getValue('foo'));

        $this->setValue('foo', 'zim');
        $this->assertSame('zim', $this->segment->get('foo'));
    }

    public function testClear()
    {
        $this->segment->set('foo', 'bar');
        $this->segment->set('baz', 'dib');
        $this->assertSame('bar', $this->getValue('foo'));
        $this->assertSame('dib', $this->getValue('baz'));

        // now clear the data
        $this->segment->clear();
        $this->assertSame(array(), $this->getValue());
        $this->assertNull($this->segment->get('foo'));
        $this->assertNull($this->segment->get('baz'));
    }

    public function testFlash()
    {
        // no message yet
        $this->assertNull($this->segment->getFlash('message'));

        // add a message
        $this->segment->setFlash('message', 'doom');
        $expect = 'doom';
        $actual = $this->segment->getFlash('message');
        $this->assertSame($expect, $actual);

        // add message then clear it
        $this->segment->setFlash('message', 'doom');
        $this->segment->clearFlash();
        $this->assertNull($this->segment->getFlash('message'));
    }

    public function test__getDoesNotStartSession()
    {
        $this->assertFalse($this->session->isStarted());
        $foo = $this->segment->get('foo');
        $this->assertNull($foo);
        $this->assertFalse($this->session->isStarted());
    }

    public function test__getReactivatesSession()
    {
        // fake a cookie
        $cookies = array(
            $this->session->getName() => 'fake-cookie-value',
        );
        $this->session = $this->newSession($cookies);

        // should be active now, even though not started
        $this->assertTrue($this->session->isAvailable());

        // reset the segment to use the new session manager
        $this->segment = $this->session->newSegment($this->name);

        // this should restart the session
        $foo = $this->segment->get('foo');
        $this->assertTrue($this->session->isStarted());
    }

    public function test__setStartsSessionAndCanReadAfter()
    {
        // no session yet
        $this->assertFalse($this->session->isStarted());

        // set it
        $this->segment->set('foo', 'bar');

        // session should have started
        $this->assertTrue($this->session->isStarted());

        // get it from the session
        $foo = $this->segment->get('foo');
        $this->assertSame('bar', $foo);

        // make sure it's actually in $_SESSION
        $this->assertSame($foo, $_SESSION[$this->name]['foo']);
    }

    public function testClearDoesNotStartSession()
    {
        $this->assertFalse($this->session->isStarted());
        $this->segment->clear();
        $this->assertFalse($this->session->isStarted());
    }

    public function testSetFlashStartsSessionAndCanReadAfter()
    {
        // no session yet
        $this->assertFalse($this->session->isStarted());

        // set it
        $this->segment->setFlash('foo', 'bar');

        // session should have started
        $this->assertTrue($this->session->isStarted());

        // should see it in the session
        $this->assertSame('bar', $_SESSION[$this->name]['__flash']['foo']);

        // now read it
        $foo = $this->segment->getFlash('foo');
        $this->assertSame('bar', $foo);

        // should not be there any more
        $this->assertFalse(isset($_SESSION[$this->name]['__flash']['foo']));
    }

    public function testGetFlashDoesNotStartSession()
    {
        $this->assertFalse($this->session->isStarted());
        $this->assertNull($this->segment->getFlash('foo'));
        $this->assertFalse($this->session->isStarted());
    }
}

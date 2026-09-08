<?php
namespace Aura\Session;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
class SegmentTest extends TestCase
{
    protected $session;

    protected $segment;

    protected $name = __CLASS__;

    protected function setUp(): void
    {
        $this->session = $this->newSession();
        $this->segment = $this->session->getSegment($this->name);
    }

    protected function newSession(array $cookies = array())
    {
        return new Session(
            new SegmentFactory,
            new CsrfTokenFactory(new Randval()),
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

    public function testGetSegment()
    {
        $this->segment->set('foo', 'bar');
        $this->segment->set('baz', 'dib');
        $this->assertSame('bar', $this->getValue('foo'));
        $this->assertSame('dib', $this->getValue('baz'));

        // now get the data
        $this->assertSame(array('foo' => 'bar', 'baz' => 'dib'), $this->segment->getSegment());
    }

    public function testFlash()
    {
        // set a value
        $this->segment->setFlash('foo', 'bar');
        $expect = 'bar';
        $this->assertSame($expect, $this->segment->getFlashNext('foo'));
        $this->assertNull($this->segment->getFlash('foo'));

        // set a value and make it available now
        $this->segment->setFlashNow('baz', 'dib');
        $expect = 'dib';
        $this->assertSame($expect, $this->segment->getFlash('baz'));
        $this->assertSame($expect, $this->segment->getFlashNext('baz'));

        // clear the next values
        $this->segment->clearFlash();
        $this->assertNull($this->segment->getFlashNext('foo'));
        $this->assertNull($this->segment->getFlashNext('baz'));
        $expect = 'dib';
        $this->assertSame($expect, $this->segment->getFlash('baz'));

        // set some current values and make sure they get kept
        $now =& $_SESSION[Session::FLASH_NOW][$this->name];
        $now['foo'] = 'bar';
        $now['baz'] = 'dib';
        $this->segment->keepFlash();
        $this->assertSame('bar', $this->segment->getFlashNext('foo'));
        $this->assertSame('dib', $this->segment->getFlashNext('baz'));

        // clear the current and future values
        $this->segment->clearFlashNow();
        $this->assertNull($this->segment->getFlash('foo'));
        $this->assertNull($this->segment->getFlashNext('foo'));
        $this->assertNull($this->segment->getFlash('baz'));
        $this->assertNull($this->segment->getFlashNext('baz'));
    }

    public function testGetDoesNotStartSession()
    {
        $this->assertFalse($this->session->isStarted());
        $foo = $this->segment->get('foo');
        $this->assertNull($foo);
        $this->assertFalse($this->session->isStarted());
    }

    public function testGetResumesSession()
    {
        // fake a cookie
        $cookies = array(
            $this->session->getName() => 'fake-cookie-value',
        );
        $this->session = $this->newSession($cookies);

        // should be active now, even though not started
        $this->assertTrue($this->session->isResumable());

        // reset the segment to use the new session manager
        $this->segment = $this->session->getSegment($this->name);

        // this should restart the session
        $foo = $this->segment->get('foo');
        $this->assertTrue($this->session->isStarted());
    }

    public function testSetStartsSessionAndCanReadAfter()
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
        $actual = $_SESSION[Session::FLASH_NEXT][$this->name]['foo'];
        $this->assertSame('bar', $actual);

    }

    public function testGetFlashDoesNotStartSession()
    {
        $this->assertFalse($this->session->isStarted());
        $this->assertNull($this->segment->getFlash('foo'));
        $this->assertFalse($this->session->isStarted());
    }

    public function testRemoveDoesNothingWhenSessionNotStarted(): void
    {
        $this->segment->remove('foo');
        $this->assertFalse($this->session->isStarted());
    }

    public function testRemoveKey(){
        $this->segment->set('foo', 'bar');
        $this->segment->set('baz', 'dib');
        $this->assertSame('bar', $this->getValue('foo'));
        $this->assertSame('dib', $this->getValue('baz'));

        // now remove the key
        $this->segment->remove('foo');
        $this->assertNull($this->segment->get('foo'));
        $this->assertArrayNotHasKey('foo', $_SESSION[$this->name]);
        $this->assertSame('dib', $this->getValue('baz'));
    }

    public function testRemoveSegment(){
        $this->segment->set('foo', 'bar');
        $this->segment->set('baz', 'dib');
        $this->assertSame('bar', $this->getValue('foo'));
        $this->assertSame('dib', $this->getValue('baz'));

        // now remove the key
        $this->segment->remove();
        $this->assertArrayNotHasKey($this->name, $_SESSION);
    }

    /**
     * A flash value set for the *next* request must be readable via getFlash()
     * in the following request. resumeSession() has to run *before* the value
     * is read so that Session::moveFlash() promotes FLASH_NEXT to FLASH_NOW.
     *
     * Regression guard for GitHub PR #68, which reordered getFlash() to read
     * the value before resuming the session; that always returns the alternate
     * (null) here because FLASH_NOW is still empty at read time.
     */
    public function testGetFlashResumesSessionBeforeReading()
    {
        // ---- request 1: set a flash for the next request ----
        $this->segment->setFlash('foo', 'bar');
        $this->assertSame('bar', $_SESSION[Session::FLASH_NEXT][$this->name]['foo']);
        // not visible via getFlash() in the same request (reads FLASH_NOW)
        $this->assertNull($this->segment->getFlash('foo'));

        $name = $this->session->getName();
        $id = $this->session->getId();
        $this->session->commit();

        // ---- request 2: brand new Session object so flash_moved is false ----
        $cookies = array($name => $id);
        $session = $this->newSession($cookies);
        $segment = $session->getSegment($this->name);

        // getFlash() resumes, promotes FLASH_NEXT -> FLASH_NOW, and returns it
        $this->assertSame('bar', $segment->getFlash('foo'));
    }

    public function testRestartSessionFlashNotMove()
    {
        $this->assertFalse($this->session->isStarted());

        // set it
        $this->segment->setFlash('foo', 'bar');

        // session should have started
        $this->assertTrue($this->session->isStarted());

        // should see it in the session
        $actual = $_SESSION[Session::FLASH_NEXT][$this->name]['foo'];
        $this->assertSame('bar', $actual);

        $this->session->commit();

        $this->assertFalse($this->session->isStarted());

        $this->session->start();

        // should see it in the session
        $actual = $_SESSION[Session::FLASH_NEXT][$this->name]['foo'];
        $this->assertSame('bar', $actual);
    }

    public function testGetFlashAll()
    {
        // nothing set yet
        $this->assertSame(array(), $this->segment->getFlashAll());
        $this->assertSame(array(), $this->segment->getFlashNextAll());

        // values for the next request only
        $this->segment->setFlash('foo', 'bar');
        $this->segment->setFlash('baz', 'dib');
        $this->assertSame(
            array('foo' => 'bar', 'baz' => 'dib'),
            $this->segment->getFlashNextAll()
        );
        $this->assertSame(array(), $this->segment->getFlashAll());

        // a value for the current request as well
        $this->segment->setFlashNow('zim', 'gir');
        $this->assertSame(array('zim' => 'gir'), $this->segment->getFlashAll());
        $this->assertSame(
            array('foo' => 'bar', 'baz' => 'dib', 'zim' => 'gir'),
            $this->segment->getFlashNextAll()
        );

        // clearing the next request leaves the current one alone
        $this->segment->clearFlash();
        $this->assertSame(array(), $this->segment->getFlashNextAll());
        $this->assertSame(array('zim' => 'gir'), $this->segment->getFlashAll());

        // clearing both empties them
        $this->segment->clearFlashNow();
        $this->assertSame(array(), $this->segment->getFlashAll());
        $this->assertSame(array(), $this->segment->getFlashNextAll());
    }

    public function testGetFlashAllDoesNotStartSession()
    {
        $this->assertFalse($this->session->isStarted());
        $this->assertSame(array(), $this->segment->getFlashAll());
        $this->assertSame(array(), $this->segment->getFlashNextAll());
        $this->assertFalse($this->session->isStarted());
    }

    public function testGetFlashAllResumesSessionBeforeReading()
    {
        // ---- request 1: set a flash for the next request ----
        $this->segment->setFlash('foo', 'bar');
        $this->segment->setFlash('baz', 'dib');

        $name = $this->session->getName();
        $id = $this->session->getId();
        $this->session->commit();

        // ---- request 2: brand new Session object so flash_moved is false ----
        $cookies = array($name => $id);
        $session = $this->newSession($cookies);
        $segment = $session->getSegment($this->name);

        // getFlashAll() resumes, promotes FLASH_NEXT -> FLASH_NOW, and returns it
        $this->assertSame(
            array('foo' => 'bar', 'baz' => 'dib'),
            $segment->getFlashAll()
        );
    }
}

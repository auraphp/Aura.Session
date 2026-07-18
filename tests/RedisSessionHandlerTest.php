<?php
namespace Aura\Session;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class RedisSessionHandlerTest extends TestCase
{
    private FakeRedisClient $redis;

    private RedisSessionHandler $handler;

    private ?string $restore_serialize_handler = null;

    protected function setUp(): void
    {
        $this->restore_serialize_handler = ini_get('session.serialize_handler');
        ini_set('session.serialize_handler', 'php_serialize');

        $this->redis = new FakeRedisClient();
        $this->handler = new RedisSessionHandler($this->redis, 3600, 'test-session:');
    }

    protected function tearDown(): void
    {
        if ($this->restore_serialize_handler !== null) {
            ini_set('session.serialize_handler', $this->restore_serialize_handler);
        }
    }

    private function encode(array $session): string
    {
        // matches how PHP encodes $_SESSION under php_serialize
        return serialize($session);
    }

    public function testReadMissingSessionReturnsEmptyString()
    {
        $this->assertSame('', $this->handler->read('no-such-id'));
    }

    public function testWriteStoresEachKeyAsItsOwnHashField()
    {
        $session = array(
            'identity' => array('userId' => 'asd1234'),
            'cart' => array('items' => 3),
        );

        $this->assertTrue($this->handler->write('abc', $this->encode($session)));

        // one Redis hash, one field per top-level session key
        $stored = $this->redis->hashes['test-session:abc'];
        $this->assertSame(array('identity', 'cart'), array_keys($stored));
        $this->assertSame($session['identity'], unserialize($stored['identity']));
        $this->assertSame($session['cart'], unserialize($stored['cart']));

        // TTL applied
        $this->assertSame(3600, $this->redis->ttls['test-session:abc']);
    }

    public function testWriteThenReadRoundTrip()
    {
        $session = array('identity' => array('userId' => 'asd1234'));
        $this->handler->write('abc', $this->encode($session));

        $encoded = $this->handler->read('abc');
        $this->assertSame($session, unserialize($encoded));
    }

    public function testWriteRemovesStaleFields()
    {
        $this->handler->write('abc', $this->encode(array('a' => 1, 'b' => 2)));
        $this->handler->write('abc', $this->encode(array('a' => 1)));

        $stored = $this->redis->hashes['test-session:abc'];
        $this->assertSame(array('a'), array_keys($stored));
    }

    public function testWriteEmptySessionStoresNothing()
    {
        $this->handler->write('abc', '');
        $this->assertArrayNotHasKey('test-session:abc', $this->redis->hashes);
    }

    public function testDestroy()
    {
        $this->handler->write('abc', $this->encode(array('a' => 1)));
        $this->assertTrue($this->handler->destroy('abc'));
        $this->assertArrayNotHasKey('test-session:abc', $this->redis->hashes);
    }

    public function testValidateId()
    {
        $this->assertFalse($this->handler->validateId('abc'));
        $this->handler->write('abc', $this->encode(array('a' => 1)));
        $this->assertTrue($this->handler->validateId('abc'));
    }

    public function testUpdateTimestampRefreshesTtlOnly()
    {
        $this->handler->write('abc', $this->encode(array('a' => 1)));
        $this->redis->ttls['test-session:abc'] = 10;

        $this->assertTrue($this->handler->updateTimestamp('abc', $this->encode(array('a' => 1))));
        $this->assertSame(3600, $this->redis->ttls['test-session:abc']);
    }

    public function testGcReturnsZero()
    {
        $this->assertSame(0, $this->handler->gc(1440));
    }

    public function testTtlFallsBackToGcMaxlifetime()
    {
        $prev = ini_get('session.gc_maxlifetime');
        ini_set('session.gc_maxlifetime', '1234');

        $handler = new RedisSessionHandler($this->redis, null, 'test-session:');
        $handler->write('abc', $this->encode(array('a' => 1)));
        $this->assertSame(1234, $this->redis->ttls['test-session:abc']);

        ini_set('session.gc_maxlifetime', (string) $prev);
    }

    public function testRequiresPhpSerializeHandler()
    {
        ini_set('session.serialize_handler', 'php');

        $this->expectException(RuntimeException::class);
        $this->handler->read('abc');
    }
}

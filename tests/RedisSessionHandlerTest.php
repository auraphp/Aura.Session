<?php
namespace Aura\Session;

use PHPUnit\Framework\TestCase;

class RedisSessionHandlerTest extends TestCase
{
    private FakeRedisClient $redis;

    private RedisSessionHandler $handler;

    protected function setUp(): void
    {
        $this->redis = new FakeRedisClient();
        $this->handler = new RedisSessionHandler($this->redis, 3600, 'test-session:');
    }

    public function testReadMissingSessionReturnsEmptyString()
    {
        $this->assertSame('', $this->handler->read('no-such-id'));
    }

    public function testWriteStoresValueWithTtl()
    {
        $data = 'identity|a:1:{s:6:"userId";s:7:"asd1234";}';

        $this->assertTrue($this->handler->write('abc', $data));
        $this->assertSame($data, $this->redis->values['test-session:abc']);
        $this->assertSame(3600, $this->redis->ttls['test-session:abc']);
    }

    public function testWriteThenReadRoundTrip()
    {
        $data = 'identity|a:1:{s:6:"userId";s:7:"asd1234";}';
        $this->handler->write('abc', $data);

        $this->assertSame($data, $this->handler->read('abc'));
    }

    public function testWriteEmptySessionDestroysInstead()
    {
        $this->handler->write('abc', 'something');
        $this->assertTrue($this->handler->write('abc', ''));

        $this->assertArrayNotHasKey('test-session:abc', $this->redis->values);
    }

    public function testDestroy()
    {
        $this->handler->write('abc', 'something');
        $this->assertTrue($this->handler->destroy('abc'));
        $this->assertArrayNotHasKey('test-session:abc', $this->redis->values);
    }

    public function testValidateId()
    {
        $this->assertFalse($this->handler->validateId('abc'));
        $this->handler->write('abc', 'something');
        $this->assertTrue($this->handler->validateId('abc'));
    }

    public function testUpdateTimestampRefreshesTtlOnly()
    {
        $this->handler->write('abc', 'something');
        $this->redis->ttls['test-session:abc'] = 10;

        $this->assertTrue($this->handler->updateTimestamp('abc', 'something'));
        $this->assertSame(3600, $this->redis->ttls['test-session:abc']);
        // payload untouched
        $this->assertSame('something', $this->redis->values['test-session:abc']);
    }

    public function testGcReturnsZero()
    {
        $this->assertSame(0, $this->handler->gc(1440));
    }

    public function testTtlFallsBackToGcMaxlifetime()
    {
        $prev = ini_get('session.gc_maxlifetime');
        ini_set('session.gc_maxlifetime', '1234');

        try {
            $handler = new RedisSessionHandler($this->redis, null, 'test-session:');
            $handler->write('abc', 'something');
            $this->assertSame(1234, $this->redis->ttls['test-session:abc']);
        } finally {
            ini_set('session.gc_maxlifetime', (string) $prev);
        }
    }
}

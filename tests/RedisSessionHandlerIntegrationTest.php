<?php
namespace Aura\Session;

use Aura\Session\Redis\PhpredisClient;
use Aura\Session\Redis\PredisClient;
use Aura\Session\Redis\RedisClientInterface;
use PHPUnit\Framework\TestCase;
use Predis\Client as PredisNativeClient;
use Redis;

// Exercises RedisSessionHandler against a live Redis server, through every
// available client adapter. Skipped entirely unless REDIS_HOST is set, so local
// runs without a server are unaffected; CI sets it and provides a service.
class RedisSessionHandlerIntegrationTest extends TestCase
{
    private ?string $restore_serialize_handler = null;

    protected function setUp(): void
    {
        if (getenv('REDIS_HOST') === false) {
            $this->markTestSkipped('REDIS_HOST not set; skipping live Redis tests.');
        }

        $this->restore_serialize_handler = ini_get('session.serialize_handler');
        ini_set('session.serialize_handler', 'php_serialize');
    }

    protected function tearDown(): void
    {
        if ($this->restore_serialize_handler !== null) {
            ini_set('session.serialize_handler', $this->restore_serialize_handler);
        }
    }

    /**
     * @return array<string, RedisClientInterface>
     */
    private function clients(): array
    {
        $host = getenv('REDIS_HOST') ?: '127.0.0.1';
        $port = (int) (getenv('REDIS_PORT') ?: 6379);

        $clients = array();

        if (extension_loaded('redis')) {
            $phpredis = new Redis();
            $phpredis->connect($host, $port);
            $clients['phpredis'] = new PhpredisClient($phpredis);
        }

        if (class_exists(PredisNativeClient::class)) {
            $clients['predis'] = new PredisClient(
                new PredisNativeClient(array('host' => $host, 'port' => $port))
            );
        }

        if ($clients === array()) {
            $this->markTestSkipped('No Redis client (phpredis or predis) available.');
        }

        return $clients;
    }

    public function testRoundTripAcrossAdapters()
    {
        foreach ($this->clients() as $name => $client) {
            $handler = new RedisSessionHandler($client, 100, 'aura-it:');
            $id = 'it-' . bin2hex(random_bytes(8));

            $session = array(
                'identity' => array('userId' => 'asd1234'),
                'cart' => array('items' => 3),
            );

            $this->assertTrue($handler->write($id, serialize($session)), $name);
            $this->assertSame($session, unserialize($handler->read($id)), $name);
            $this->assertTrue($handler->validateId($id), $name);

            // stale-field removal survives a real round trip
            $handler->write($id, serialize(array('identity' => array('userId' => 'x'))));
            $this->assertSame(
                array('identity' => array('userId' => 'x')),
                unserialize($handler->read($id)),
                $name
            );

            $handler->destroy($id);
            $this->assertSame('', $handler->read($id), $name);
            $this->assertFalse($handler->validateId($id), $name);
        }
    }
}

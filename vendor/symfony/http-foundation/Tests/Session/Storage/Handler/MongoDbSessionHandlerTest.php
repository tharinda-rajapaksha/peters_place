<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Tests\Session\Storage\Handler;

use InvalidArgumentException;
use MongoDB\BSON\Binary;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use MongoDB\Collection;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 * @group time-sensitive
 * @requires extension mongodb
 */
class MongoDbSessionHandlerTest extends TestCase
{
    public $options;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $mongo;
    private $storage;

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorShouldThrowExceptionForMissingOptions()
    {
        new MongoDbSessionHandler($this->mongo, []);
    }

    public function testOpenMethodAlwaysReturnTrue()
    {
        $this->assertTrue($this->storage->open('test', 'test'), 'The "open" method should always return true');
    }

    public function testCloseMethodAlwaysReturnTrue()
    {
        $this->assertTrue($this->storage->close(), 'The "close" method should always return true');
    }

    public function testRead()
    {
        $collection = $this->createMongoCollectionMock();

        $this->mongo->expects($this->once())
            ->method('selectCollection')
            ->with($this->options['database'], $this->options['collection'])
            ->willReturn($collection);

        // defining the timeout before the actual method call
        // allows to test for "greater than" values in the $criteria
        $testTimeout = time() + 1;

        $collection->expects($this->once())
            ->method('findOne')
            ->willReturnCallback(function ($criteria) use ($testTimeout) {
                $this->assertArrayHasKey($this->options['id_field'], $criteria);
                $this->assertEquals($criteria[$this->options['id_field']], 'foo');

                $this->assertArrayHasKey($this->options['expiry_field'], $criteria);
                $this->assertArrayHasKey('$gte', $criteria[$this->options['expiry_field']]);

                $this->assertInstanceOf(UTCDateTime::class, $criteria[$this->options['expiry_field']]['$gte']);
                $this->assertGreaterThanOrEqual(round((string)$criteria[$this->options['expiry_field']]['$gte'] / 1000), $testTimeout);

                return [
                    $this->options['id_field'] => 'foo',
                    $this->options['expiry_field'] => new UTCDateTime(),
                    $this->options['data_field'] => new Binary('bar', Binary::TYPE_OLD_BINARY),
                ];
            });

        $this->assertEquals('bar', $this->storage->read('foo'));
    }

    public function testWrite()
    {
        $collection = $this->createMongoCollectionMock();

        $this->mongo->expects($this->once())
            ->method('selectCollection')
            ->with($this->options['database'], $this->options['collection'])
            ->willReturn($collection);

        $collection->expects($this->once())
            ->method('updateOne')
            ->willReturnCallback(function ($criteria, $updateData, $options) {
                $this->assertEquals([$this->options['id_field'] => 'foo'], $criteria);
                $this->assertEquals(['upsert' => true], $options);

                $data = $updateData['$set'];
                $expectedExpiry = time() + (int)ini_get('session.gc_maxlifetime');
                $this->assertInstanceOf(Binary::class, $data[$this->options['data_field']]);
                $this->assertEquals('bar', $data[$this->options['data_field']]->getData());
                $this->assertInstanceOf(UTCDateTime::class, $data[$this->options['time_field']]);
                $this->assertInstanceOf(UTCDateTime::class, $data[$this->options['expiry_field']]);
                $this->assertGreaterThanOrEqual($expectedExpiry, round((string)$data[$this->options['expiry_field']] / 1000));
            });

        $this->assertTrue($this->storage->write('foo', 'bar'));
    }

    public function testReplaceSessionData()
    {
        $collection = $this->createMongoCollectionMock();

        $this->mongo->expects($this->once())
            ->method('selectCollection')
            ->with($this->options['database'], $this->options['collection'])
            ->willReturn($collection);

        $data = [];

        $collection->expects($this->exactly(2))
            ->method('updateOne')
            ->willReturnCallback(function ($criteria, $updateData, $options) use (&$data) {
                $data = $updateData;
            });

        $this->storage->write('foo', 'bar');
        $this->storage->write('foo', 'foobar');

        $this->assertEquals('foobar', $data['$set'][$this->options['data_field']]->getData());
    }

    public function testDestroy()
    {
        $collection = $this->createMongoCollectionMock();

        $this->mongo->expects($this->once())
            ->method('selectCollection')
            ->with($this->options['database'], $this->options['collection'])
            ->willReturn($collection);

        $collection->expects($this->once())
            ->method('deleteOne')
            ->with([$this->options['id_field'] => 'foo']);

        $this->assertTrue($this->storage->destroy('foo'));
    }

    public function testGc()
    {
        $collection = $this->createMongoCollectionMock();

        $this->mongo->expects($this->once())
            ->method('selectCollection')
            ->with($this->options['database'], $this->options['collection'])
            ->willReturn($collection);

        $collection->expects($this->once())
            ->method('deleteMany')
            ->willReturnCallback(function ($criteria) {
                $this->assertInstanceOf(UTCDateTime::class, $criteria[$this->options['expiry_field']]['$lt']);
                $this->assertGreaterThanOrEqual(time() - 1, round((string)$criteria[$this->options['expiry_field']]['$lt'] / 1000));
            });

        $this->assertTrue($this->storage->gc(1));
    }

    public function testGetConnection()
    {
        $method = new ReflectionMethod($this->storage, 'getMongo');
        $method->setAccessible(true);

        $this->assertInstanceOf(Client::class, $method->invoke($this->storage));
    }

    protected function setUp()
    {
        parent::setUp();

        if (!class_exists(Client::class)) {
            $this->markTestSkipped('The mongodb/mongodb package is required.');
        }

        $this->mongo = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->options = [
            'id_field' => '_id',
            'data_field' => 'data',
            'time_field' => 'time',
            'expiry_field' => 'expires_at',
            'database' => 'sf-test',
            'collection' => 'session-test',
        ];

        $this->storage = new MongoDbSessionHandler($this->mongo, $this->options);
    }

    private function createMongoCollectionMock()
    {
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $collection;
    }
}

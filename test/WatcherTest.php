<?php
namespace Test\unit;

use Evenement\EventEmitter;
use Fileon\Resource;
use Fileon\Watcher;

/**
 * Class WatcherTest
 *
 * @coversDefaultClass \Reacton\Watcher
 *
 * @package Test\unit
 */
class WatcherTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @var Resource
     */
    protected $mockResource;

    /**
     * @var Watcher
     */
    protected $mockWatcher;

    /**
     * @var Watcher
     */
    protected $watcher;

    protected function setUp()
    {
        $this->mockResource = $this
            ->getMockBuilder(Resource::class)
            ->setMethods(
                [
                    'getResources',
                    'getStashedResources',
                    'haveToBeIgnored',
                    'outOfNeeding',
                    'updateStashedResource'
                ]
            )
            ->setConstructorArgs(
                [
                    __DIR__
                ]
            )
            ->getMock();
        $this->mockWatcher = $this
            ->getMockBuilder(Watcher::class)
            ->setMethods(
                [
                    'registerEvents'
                ]
            )
            ->setConstructorArgs(
                [
                    $this->mockResource,
                    10
                ]
            )
            ->getMock();
        $this->watcher = new Watcher($this->mockResource, 10);
    }

    /**
     * @covers ::__construct
     */
    public function testBadConstructResourceCall()
    {
        $this->expectException(\TypeError::class);

        new Watcher('bad param');
    }

    /**
     * @covers ::__construct
     */
    public function testWatcherInstance()
    {
        $this->assertAttributeInstanceOf(\Evenement\EventEmitter::class, 'ee', $this->watcher);
    }

    /**
     * @covers ::stop
     * @covers ::isStopped
     */
    public function testStopWatching()
    {
        $this->assertFalse($this->watcher->isStopped());
        $this->watcher->stop();
        $this->assertTrue($this->watcher->isStopped());
    }

    /**
     * @covers ::getActions
     */
    public function testGetEventActionsRegistered()
    {
        $this->assertEmpty($this->watcher->getActions());

        $this->watcher->onNew(function(){
            return 1;
        });
        $this->watcher->onModified(function(){
            return 2;
        });

        $this->assertArrayHasKey(Watcher::EVENT_NEW, $this->watcher->getActions());
        $this->assertArrayHasKey(Watcher::EVENT_MODIFIED, $this->watcher->getActions());
    }

    /**
     * @covers ::getEventEmitter
     */
    public function testGetEventEmitterInstance()
    {
        $this->assertInstanceOf(EventEmitter::class, $this->watcher->getEventEmitter());
    }

    /**
     * @covers ::onNew
     */
    public function testRegisterOnNewEvent()
    {
        $this->watcher->onNew(function(){
            return 1;
        });

        $this->assertEquals(1, $this->watcher->getActions()[Watcher::EVENT_NEW]());
    }

    /**
     * @covers ::onModified
     */
    public function testRegisterOnModifiedEvent()
    {
        $this->watcher->onModified(function(){
            return 2;
        });

        $this->assertEquals(2, $this->watcher->getActions()[Watcher::EVENT_MODIFIED]());
    }

    /**
     * @covers ::watch
     */
    public function testWatch()
    {
        $this->mockWatcher
            ->expects($this->once())
            ->method('registerEvents');

        $this->mockResource
            ->expects($this->any())
            ->method('getResources')
            ->will($this->returnValue(new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(__DIR__, \FilesystemIterator::SKIP_DOTS)
            )));

        $this->mockResource
            ->expects($this->any())
            ->method('haveToBeIgnored')
            ->will($this->returnValue(false));

        $this->mockResource
            ->expects($this->any())
            ->method('outOfNeeding')
            ->will($this->returnValue(false));

        $this->mockResource
            ->expects($this->any())
            ->method('getStashedResources')
            ->will($this->returnValue([]));

        $this->mockResource
            ->expects($this->any())
            ->method('updateStashedResource');

        $this->mockWatcher->watch(function(){
            $this->mockWatcher->stop();
        });
    }
}

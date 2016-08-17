<?php
namespace Test\unit;

use Fileon\Resource;

/**
 * Class ResourceTest
 *
 * @coversDefaultClass \Reacton\Resource
 *
 * @package Test\unit
 */
class ResourceTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @var \Fileon\Resource
     */
    protected $resource;

    protected function setUp()
    {
        $this->resource = new Resource(__DIR__);
    }

    /**
     * @covers ::__construct
     */
    public function testBadConstructResourceCall()
    {
        $this->expectException(\BadMethodCallException::class);

        new Resource('invalid/path');
    }

    /**
     * @covers ::getResources
     */
    public function testGetResources()
    {
        $r = $this->resource->getResources();
        $rAsArray = iterator_to_array($r);

        $this->assertNotEmpty($r);
        $this->assertInstanceOf('RecursiveIteratorIterator', $r);
        $this->assertInstanceOf('SplFileInfo', array_values($rAsArray)[0]);
    }

    /**
     * @covers ::getStashedResources
     */
    public function testGetStashedResources()
    {
        $sr = $this->resource->getStashedResources();

        $this->assertNotEmpty($sr);
        $this->assertInternalType('array', $sr);
        $this->assertInternalType('integer', array_values($sr)[0]);
    }

    /**
     * @covers ::getIgnoringPatterns
     */
    public function testGetIgnoringPatterns()
    {
        $this->assertEmpty($this->resource->getIgnoringPatterns());
        $this->assertEquals([], $this->resource->getIgnoringPatterns());
    }

    /**
     * @covers ::getNeedingPatterns
     */
    public function testGetNeedingPatterns()
    {
        $this->assertEmpty($this->resource->getNeedingPatterns());
        $this->assertEquals([], $this->resource->getNeedingPatterns());
    }

    /**
     * @covers ::addIgnoringPatterns
     */
    public function testAddIgnoringPatterns()
    {
        $this->resource->addIgnoringPatterns(
            [
                'test',
                'test2',
            ]
        );

        $this->assertArraySubset($this->resource->getIgnoringPatterns(), ['test', 'test2']);
    }

    /**
     * @covers ::haveToBeIgnored
     */
    public function testHaveToBeIgnored()
    {
        $this->resource->addIgnoringPatterns(
            [
                'test',
                'test2',
            ]
        );

        $this->assertTrue($this->resource->haveToBeIgnored('path/contains/ignoring/path/test.php'));
        $this->assertFalse($this->resource->haveToBeIgnored('path/not/contains/ignoring/path/teest.js'));
        $this->assertTrue($this->resource->haveToBeIgnored('path/contains/ignoring/path/test2.php'));
        $this->assertFalse($this->resource->haveToBeIgnored('path/not/contains/ignoring/path/teest2.js'));
    }

    /**
     * @covers ::addNeedingPatterns
     */
    public function testAddNeedingPatterns()
    {
        $this->resource->addNeedingPatterns(
            [
                'test',
                'test2',
            ]
        );

        $this->assertArraySubset($this->resource->getNeedingPatterns(), ['test', 'test2']);
    }

    /**
     * @covers ::outOfNeeding
     */
    public function testOutOfNeeding()
    {
        $this->assertFalse($this->resource->outOfNeeding('emptyneedingpatterns'));

        $this->resource->addNeedingPatterns(
            [
                'test',
                'test2',
            ]
        );

        $this->assertFalse($this->resource->outOfNeeding('path/contains/needing/path/test.php'));
        $this->assertTrue($this->resource->outOfNeeding('path/not/contains/needing/path/teest.js'));
        $this->assertFalse($this->resource->outOfNeeding('path/contains/needing/path/test2.php'));
        $this->assertTrue($this->resource->outOfNeeding('path/not/contains/needing/path/teest2.js'));
    }

    /**
     * @covers ::updateStashedResource
     */
    public function testUpdateStashedResource()
    {
        $sr = $this->resource->getStashedResources();
        $firstStashedKey = array_keys($sr)[0];
        $firstStashedValue = array_values($sr)[0];

        $this->resource->updateStashedResource($firstStashedKey, 0);
        $sr = $this->resource->getStashedResources();
        $firstStashedUpdatedValue = array_values($sr)[0];

        $this->assertEquals(0, $firstStashedUpdatedValue);
        $this->assertNotEquals($firstStashedValue, $firstStashedUpdatedValue);
    }
}

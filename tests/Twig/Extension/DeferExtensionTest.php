<?php
namespace Tests\Boekkooi\Bundle\TwigJackBundle\Twig\Extension;

use Boekkooi\Bundle\TwigJackBundle\Twig\Extension\DeferExtension;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class DeferExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeferExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->extension = new DeferExtension();
    }
    protected function tearDown()
    {
        $this->extension = null;
    }

    public function testCacheAndRetrieve()
    {
        $this->extension->cache('a', 'c', 'a');
        $this->extension->cache('a', 'c');
        $this->extension->cache('a', 'c', 'c');

        $this->extension->cache('b', 'c', 'a');
        $this->extension->cache('b', 'c', 'b');

        $res = $this->extension->retrieve('a');
        $this->assertInternalType('array', $res);
        $this->assertCount(3, $res);
        $this->assertEquals(array('a' => 'c', 'c', 'c' => 'c'), $res);

        $res = $this->extension->retrieve('b');
        $this->assertInternalType('array', $res);
        $this->assertCount(2, $res);
        $this->assertEquals(array('a' => 'c', 'b' => 'c'), $res);
    }

    public function testRetrieveClear()
    {
        $this->extension->cache('a', 'a', 'c');

        $res = $this->extension->retrieve('a');
        $this->assertInternalType('array', $res);
        $this->assertCount(1, $res);

        $res = $this->extension->retrieve('a');
        $this->assertInternalType('array', $res);
        $this->assertCount(0, $res);
    }

    public function testRetrieveKeep()
    {
        $this->extension->cache('a', 'a', 'c');

        $res = $this->extension->retrieve('a', false);
        $this->assertInternalType('array', $res);
        $this->assertCount(1, $res);

        $resSame = $this->extension->retrieve('a');
        $this->assertEquals($res, $resSame);
    }
}

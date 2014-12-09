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

    public function testGetters()
    {
        $this->assertCount(1, $this->extension->getTokenParsers());
        $this->assertCount(1, $this->extension->getFunctions());
        $this->assertEquals('defer', $this->extension->getName());
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
        $this->assertEquals(array('c', 'c', 'c'), $res);

        $res = $this->extension->retrieve('b');
        $this->assertInternalType('array', $res);
        $this->assertCount(2, $res);
        $this->assertEquals(array('c', 'c'), $res);
    }

    public function testCacheAndRetrieveOrder()
    {
        $this->extension->cache('a', '0', 'a');
        $this->extension->cache('a', '1', null, 0);
        $this->extension->cache('a', '2', 'c', 1);

        $res = $this->extension->retrieve('a');
        $this->assertInternalType('array', $res);
        $this->assertCount(3, $res);
        $this->assertEquals(array('1', '2', '0'), $res);
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

    public function testContains()
    {
        $this->assertFalse($this->extension->contains('type', 'name'));
        $this->assertFalse($this->extension->contains('type', 'fake_name'));
        $this->assertFalse($this->extension->contains('fake_type', 'name'));
        $this->assertFalse($this->extension->contains('fake_type', 'fake_name'));

        $this->extension->cache('type', 'content', 'name');

        $this->assertTrue($this->extension->contains('type', 'name'));
        $this->assertFalse($this->extension->contains('type', 'fake_name'));
        $this->assertFalse($this->extension->contains('fake_type', 'name'));
        $this->assertFalse($this->extension->contains('fake_type', 'fake_name'));
    }
}

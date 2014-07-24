<?php
namespace Tests\Boekkooi\Bundle\TwigJackBundle;

use Boekkooi\Bundle\TwigJackBundle\BoekkooiTwigJackBundle;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class BoekkooiTwigJackBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $bundle = new BoekkooiTwigJackBundle();
        $this->assertEquals('BoekkooiTwigJackBundle', $bundle->getName());
    }
}

<?php

namespace SitePoint\Container\Test\Reference;

use SitePoint\Container\Reference\ServiceReference;

class ServiceReferenceTest extends \PHPUnit_Framework_TestCase
{
    public function testServiceReference()
    {
        $reference = new ServiceReference('foo.bar');
        $this->assertEquals('foo.bar', $reference->getName());
    }
}

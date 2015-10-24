<?php

namespace SitePoint\Container\Test\Reference;

use SitePoint\Container\Reference\ParameterReference;

class ParameterReferenceTest extends \PHPUnit_Framework_TestCase
{
    public function testParameterReference()
    {
        $reference = new ParameterReference('foo.bar');
        $this->assertEquals('foo.bar', $reference->getName());
    }
}

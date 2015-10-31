<?php

namespace SitePoint\Container\Test;

use SitePoint\Container\Container;
use SitePoint\Container\Reference\ParameterReference;
use SitePoint\Container\Reference\ServiceReference;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testParameters()
    {
        $parameters = [
            'hello' => 'world',
            'first' => [
                'second' => 'foo',
                'third' => [
                    'fourth' => 'bar',
                ],
            ],
        ];

        $container = new Container([], $parameters);

        // Basic test
        $this->assertEquals('world', $container->getParameter('hello'));

        // Layered test
        $this->assertEquals('foo', $container->getParameter('first.second'));
        $this->assertEquals('bar', $container->getParameter('first.third.fourth'));
    }

    public function testContainer()
    {
        // Service definitions
        $services = [
            'service' => [
                'class' => MockService::class,
                'arguments' => [
                    new ServiceReference('dependency'),
                    'foo',
                ],
                'calls' => [
                    [
                        'method' => 'setProperty',
                        'arguments' => [
                            new ParameterReference('group.param')
                        ]
                    ]
                ]
            ],
            'dependency' => [
                'class' => MockDependency::class,
                'arguments' => [
                    new ParameterReference('group.param'),
                ],
            ],
        ];

        // Parameter definitions
        $parameters = [
            'group' => [
                'param' => 'bar',
            ],
        ];

        // Create container
        $container = new Container($services, $parameters);

        // Check retrieval of service
        $service = $container->get('service');
        $this->assertInstanceOf(MockService::class, $service);

        // Check retrieval of dependency
        $dependency = $container->get('dependency');
        $this->assertInstanceOf(MockDependency::class, $dependency);

        // Check that the dependency has been reused
        $this->assertSame($dependency, $service->getDependency());

        // Check the retrieval of container parameters
        $this->assertTrue($container->hasParameter('group.param'));
        $this->assertFalse($container->hasParameter('foo.bar'));

        // Check the parameters have been loaded correctly
        $this->assertEquals('foo', $service->getParameter());
        $this->assertEquals('bar', $dependency->getParameter());

        // Check the service calls have initialized
        $this->assertEquals('bar', $service->getProperty());
    }

    // ERROR TESTING

    /**
     * @expectedException SitePoint\Container\Exception\ServiceNotFoundException
     */
    public function testServiceNotFound()
    {
        $container = new Container();
        $container->get('foo');
    }

    /**
     * @expectedException SitePoint\Container\Exception\ParameterNotFoundException
     */
    public function testParameterNotFound()
    {
        $container = new Container();
        $container->getParameter('foo');
    }

    /**
     * @expectedException        SitePoint\Container\Exception\ContainerException
     * @expectedExceptionMessage must be an array containing a 'class' key
     */
    public function testBadServiceEntry()
    {
        $container = new Container(['foo' => 'bar']);
        $container->get('foo');
    }

    /**
     * @expectedException        SitePoint\Container\Exception\ContainerException
     * @expectedExceptionMessage class does not exist
     */
    public function testInvalidClassPath()
    {
        $container = new Container(['foo' => ['class' => 'LALALALALALA']]);
        $container->get('foo');
    }

    /**
     * @expectedException        SitePoint\Container\Exception\ContainerException
     * @expectedExceptionMessage circular reference
     */
    public function testCircularReference()
    {
        $container = new Container([
            'foo' => [
                'class' => MockService::class,
                'arguments' => [
                    new ServiceReference('foo'),
                ],
            ],
        ]);

        $container->get('foo');
    }

    /**
     * @expectedException        SitePoint\Container\Exception\ContainerException
     * @expectedExceptionMessage service calls must be arrays containing a 'method' key
     */
    public function testNoMethod()
    {
        $container = new Container([
            'foo' => [
                'class' => MockDependency::class,
                'arguments' => [
                    'foo',
                ],
                'calls' => [
                    [ 'foo' ],
                ],
            ],
        ]);

        $container->get('foo');
    }

    /**
     * @expectedException        SitePoint\Container\Exception\ContainerException
     * @expectedExceptionMessage call to uncallable method
     */
    public function testUncallableMethod()
    {
        $container = new Container([
            'foo' => [
                'class' => MockDependency::class,
                'arguments' => [
                    'foo',
                ],
                'calls' => [
                    [ 'method' => 'LALALALALA' ],
                ],
            ],
        ]);

        $container->get('foo');
    }
}

// Mock classes for testing

class MockService
{
    private $dependency;
    private $parameter;
    private $property;

    public function __construct(MockDependency $dependency, $parameter)
    {
        $this->dependency = $dependency;
        $this->parameter  = $parameter;
    }

    public function getDependency()
    {
        return $this->dependency;
    }

    public function getParameter()
    {
        return $this->parameter;
    }

    public function setProperty($value)
    {
        $this->property = $value;
    }

    public function getProperty()
    {
        return $this->property;
    }
}

class MockDependency
{
    private $parameter;

    public function __construct($parameter)
    {
        $this->parameter = $parameter;
    }

    public function getParameter()
    {
        return $this->parameter;
    }
}

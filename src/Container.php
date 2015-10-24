<?php

namespace SitePoint\Container;

use SitePoint\Container\Exception\ContainerException;
use SitePoint\Container\Exception\ParameterNotFoundException;
use SitePoint\Container\Exception\ServiceNotFoundException;
use SitePoint\Container\Reference\ParameterReference;
use SitePoint\Container\Reference\ServiceReference;

/**
 * A very simple dependency injection container.
 */
class Container
{
    /**
     * @var array
     */
    private $services;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var array
     */
    private $serviceStore;

    /**
     * Constructor for the container.
     *
     * Entrys into the $services array must be an associative array with a
     * 'class' key and an optional 'arguments' key. Where present the arguments
     * will be passed to the class constructor. If an argument is an instance of
     * ContainerService the argument will be replaced with the corresponding
     * service from the container before the class is instantiated. If an
     * argument is an instance of ContainerParameter the argument will be
     * replaced with the corresponding parameter from the container before the
     * class is instantiated.
     *
     * @param array $services   The service definitions.
     * @param array $parameters The parameter definitions.
     */
    public function __construct(array $services = [], array $parameters = [])
    {
        $this->services     = $services;
        $this->parameters   = $parameters;
        $this->serviceStore = [];
    }

    /**
     * Retrieve a service from the container.
     *
     * @param string $name The service name.
     *
     * @return mixed The service.
     */
    public function get($name)
    {
        if (!isset($this->services[$name])) {
            throw new ServiceNotFoundException('Service not found: '.$name);
        }

        // If we haven't created it, create it and save to store
        if (!isset($this->servicesStore[$name])) {
            $this->servicesStore[$name] = $this->createService($name);
        }

        // Return service from store
        return $this->servicesStore[$name];
    }

    /**
     * Retrieve a parameter from the container.
     *
     * @param string $name The parameter name.
     *
     * @return mixed The parameter.
     */
    public function getParameter($name)
    {
        $tokens  = explode('.', $name);
        $context = $this->parameters;

        while (null !== ($token = array_shift($tokens))) {
            if (!isset($context[$token])) {
                throw new ParameterNotFoundException('Parameter not found: '.$name);
            }

            $context = $context[$token];
        }

        return $context;
    }

    /**
     * Attempt to create a service.
     *
     * @param string $name The service name.
     *
     * @return mixed The created service.
     *
     * @throws \Exception On failure.
     */
    private function createService($name)
    {
        $entry = $this->services[$name];

        if (!is_array($entry) || !isset($entry['class'])) {
            throw new ContainerException($name.' service entry must be an array containing a \'class\' key');
        } elseif (!class_exists($entry['class'])) {
            throw new ContainerException($name.' service class does not exist: '.$entry['class']);
        }

        $arguments = isset($entry['arguments']) ? $this->resolveServiceArguments($name, $entry['arguments']) : [];

        $reflector = new \ReflectionClass($entry['class']);
        return $reflector->newInstanceArgs($arguments);
    }

    /**
     * Resolve the arguments of a service.
     *
     * @param string $name                The service name.
     * @param array  $argumentDefinitions The service arguments definition.
     *
     * @return array The service constructor arguments.
     */
    private function resolveServiceArguments($name, $argumentDefinitions)
    {
        $arguments = [];

        foreach ($argumentDefinitions as $argumentDefinition) {
            if ($argumentDefinition instanceof ServiceReference) {
                $argumentServiceName = $argumentDefinition->getName();

                if ($argumentServiceName === $name) {
                    throw new ContainerException($name.' service contains a circular reference');
                }

                $arguments[] = $this->get($argumentServiceName);
            } elseif ($argumentDefinition instanceof ParameterReference) {
                $argumentParameterName = $argumentDefinition->getName();

                $arguments[] = $this->getParameter($argumentParameterName);
            } else {
                $arguments[] = $argumentDefinition;
            }
        }

        return $arguments;
    }
}

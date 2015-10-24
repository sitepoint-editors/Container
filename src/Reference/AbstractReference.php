<?php

namespace SitePoint\Container\Reference;

/**
 * An abstract value object for a name that references some other value.
 */
abstract class AbstractReference
{
    /**
     * @var string
     */
    private $name;

    /**
     * Constructor for the container argument.
     *
     * @param string $serviceName The service name.
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Retrieve the service name.
     *
     * @return string The service name.
     */
    public function getName()
    {
        return $this->name;
    }
}

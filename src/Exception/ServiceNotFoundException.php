<?php

namespace SitePoint\Container\Exception;

use Interop\Container\Exception\NotFoundException as InteropNotFoundException;

/**
 * The ServiceNotFoundException is thrown when the container is asked to provide
 * a service that has not been defined.
 */
class ServiceNotFoundException extends \Exception implements InteropNotFoundException {}

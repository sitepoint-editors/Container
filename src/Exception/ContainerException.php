<?php

namespace SitePoint\Container\Exception;

use Interop\Container\Exception\ContainerException as InteropContainerException;

/**
 * Container exceptions are thrown by the container when it cannot behave as it
 * has been requested to.
 */
class ContainerException extends \Exception implements InteropContainerException {}

# Container

[![Latest Stable Version](https://poser.pugx.org/sitepoint/container/v/stable)](https://packagist.org/packages/sitepoint/container)
[![Build Status](https://travis-ci.org/sitepoint/container.svg?branch=master)](https://travis-ci.org/sitepoint/container)
[![Coverage Status](https://coveralls.io/repos/sitepoint/Container/badge.svg?branch=master&service=github)](https://coveralls.io/github/sitepoint/Container?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sitepoint/Container/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sitepoint/Container/?branch=master)
[![Total Downloads](https://poser.pugx.org/sitepoint/container/downloads)](https://packagist.org/packages/sitepoint/container)[![License](https://poser.pugx.org/sitepoint/container/license)](https://packagist.org/packages/sitepoint/container)

A simple, easy to follow PHP dependency injection container. Designed to be forked, modified, extended and hacked.

## How to Use

Although it isn't required to do so, a good practice is to split up the configuration for our container. In this example we'll use three files to create our container for the Monolog component.

In the service definitions file, we define three services. All of the services require constructor injection arguments. Some of these arguments are imported from the container parameters and some are defined directly. The logger service also requires two calls to the `pushHandler` method, each with a different handler service imported. 
```PHP
<?php // config/services.php

// Value objects are used to reference parameters and services in the container
use SitePoint\Container\Reference\ParameterReference;
use SitePoint\Container\Reference\ServiceReference;

use Monolog\Logger;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\StreamHandler;

return [
    'stream_handler' => [
        'class' => StreamHandler::class,
        'arguments' => [
            new ParameterReference('logger.file'),
            Logger::DEBUG,
        ],
    ],
    'mail_handler' => [
        'class' => NativeMailerHandler::class,
        'arguments' => [
            new ParameterReference('logger.mail.to_address'),
            new ParameterReference('logger.mail.subject'),
            new ParameterReference('logger.mail.from_address'),
            Logger::ERROR,
        ],
    ],
    'logger' => [
        'class' => Logger::class,
        'arguments' => [ 'channel-name' ],
        'calls' => [
            [
                'method' => 'pushHandler',
                'arguments' => [
                    new ServiceReference('stream_handler'),
                ]
            ],
            [
                'method' => 'pushHandler',
                'arguments' => [
                    new ServiceReference('mail_handler'),
                ]
            ]
        ]
    ]
];
```

The parameters definitions file just returns an array of values. These are defined as an N-dimensional array, but they are accessed through references using the notation: `'logger.file'` or `'logger.mail.to_address'`.

```PHP
<?php // config/parameters.php

return [
    'logger' => [
        'file' => __DIR__.'/../app.log',
        'mail' => [
            'to_address' => 'webmaster@domain.com',
            'from_address' => 'alerts@domain.com',
            'subject' => 'App Logs',
        ],
    ],
];
```

The container file just extracts the service and parameter definitions and passes them to the `Container` class constructor.

```PHP
<?php // config/container.php
use SitePoint\Container\Container;

$services   = include __DIR__.'/services.php';
$parameters = include __DIR__.'/parameters.php';

return new Container($services, $parameters);
```

Now we can obtain the container in our app and use the logger service.

```PHP
<?php // app/file.php

require_once __DIR__.'/../vendor/autoload.php';

$container = include __DIR__.'/../config/container.php';

$logger = $container->get('logger');
$logger->debug('This will be logged to the file');
$logger->error('This will be logged to the file and the email');
```

## Change Log

This project maintains a [change log file](CHANGELOG.md)

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

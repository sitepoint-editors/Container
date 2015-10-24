# Container

A simple, easy to follow PHP dependency injection container. Designed to be forked, modified, extended and hacked.

# How To Use

Although it isn't required to do so, a good practice is to split up the configuration for our container. In this example we'll use three files to create our container for the Monolog component.

In the service definitions file, we define three services. The 'stream_handler' and 'mail_handler' services are created via constructor injection arguments. Some of these arguments are imported from the container parameters and some are defined directly. The 'logger' service is instantiated via two calls to the 'pushHandler' method, each with a different handler service imported. 
```PHP
// config/services.php

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
    ]
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
// config/parameters.php

return [
    'logger' => [
        'file' => __DIR__.'/app.log',
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
// config/container.php
use SitePoint\Container\Container;

$services   = include __DIR__.'/services.php';
$parameters = include __DIR__.'/parameters.php';

return new Container($services, $parameters);
```

Now we can obtain the container in our app and use the logger service.

```PHP
// app/file.php

$container = include __DIR__.'/../config/container.php';

$logger = $container->get('logger');
$logger->debug('This will be logged to the file');
$logger->error('This will be logged to the file and the email');
```

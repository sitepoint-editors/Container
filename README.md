# Container

A simple, easy to follow PHP dependency injection container. Designed to be forked, modified, extended and hacked.

# How To Use

```PHP
// config/services.php

use SitePoint\Container\Reference\ParameterReference;
use SitePoint\Container\Reference\ServiceReference;

use Monolog\Logger;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\StreamHandler;

return [
    'steam_handler' => [
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

```PHP
// config/container.php
use SitePoint\Container\Container;

$services   = include __DIR__.'/services.php';
$parameters = include __DIR__.'/parameters.php';

return new Container($services, $parameters);
```

```PHP
// app/file.php

$container = include __DIR__.'/../config/container.php';

$logger = $container->get('logger');
$logger->debug('This will be logged to the file');
$logger->error('This will be logged to the file and the email');
```

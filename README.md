# Container

A simple, easy to follow PHP dependency injection container. Designed to be forked, modified, extended and hacked.

# How To Use

```PHP
// Container Imports
use SitePoint\Container\Container;
use SitePoint\Container\Reference\ParameterReference;
use SitePoint\Container\Reference\ServiceReference;

// Service Imports
use Monolog\Logger;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\StreamHandler;

$parameters = [
    'logger' => [
        'file' => __DIR__.'/app.log',
        'mail' => [
            'to_address'   => 'webmaster@domain.com',
            'from_address' => 'alerts@domain.com',
            'subject'      => 'App Logs',
        ],
    ],
];

$services = [
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
                'method'    => 'pushHandler',
                'arguments' => [
                    new ServiceReference('stream_handler'),
                ]
            ],
            [
                'method'    => 'pushHandler',
                'arguments' => [
                    new ServiceReference('mail_handler'),
                ]
            ]
        ]
    ]
];

$container = new Container($services, $parameters);

$logger = $container->get('logger');
$logger->debug('This will be logged to the file');
$logger->error('This will be logged to the file and the email');
```

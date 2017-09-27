# Slim Event Dispatcher

[![Build Status](https://travis-ci.org/aneek/slim-event-dispatcher.svg?branch=develop)](https://travis-ci.org/aneek/slim-event-dispatcher)

This library is an implementation of ```League\Event``` for ```Slim Framework```. This works with the latest version of Slim (V3).

## Installation
It's recommended that you use [Composer](https://getcomposer.org/) to install Slim Event Dispatcher.

```bash
$ composer require aneek/slim-event-dispatcher "^1.0"
```
This will install Slim Event Dispatcher and all required dependencies. This package requires PHP 5.6 or newer. Though version 1.x is not recommended for PHP 7.0 and above. The next major version (v2.x) will support PHP 7.x.

## Usage
This is not a Slim Middleware but a package which integrates with your Slim application by extending the ```SlimEventManager``` class and adding it to Slim's dependency container. Currently there are two ways of integration.

### Class Constructor
```Slim\Event\SlimEventManager``` class accepts an array argument in it's constructor and initializes all Listeners (more information on [listeners](http://event.thephpleague.com/2.0/listeners/callables/)).

The below file content serves as a application loader file for Slim Framework:

```php
<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Container;
use Slim\App;
use Slim\Event\SlimEventManager;

require 'vendor/autoload.php';

$settings = [
    'settings' => [
        'determineRouteBeforeAppMiddleware' => true,
        'displayErrorDetails' => true,
    ],
];

// Array of event Listeners
$events = [
    'event.one' => [
        // First element is the FQCN class. This element is mandatory.
        // Second element is the listener priority but this is not mandatory.
        [\FooListener::class, 100],
        [\BarListener::class]    
    ],
    'event.two' => [
        [\BazListener::class]
    ]
];


$app = new Slim\App();

$app->get('/hello/{name}', function ($request, $response, $args) {
    return $response->write("Hello, " . $args['name']);
});

$app->run();
```

<?php
// Enable Composer autoloader
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';


// Register test classes
$autoloader->addPsr4('Slim\\Event\\Tests\\', __DIR__);
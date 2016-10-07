<?php

// Instantiate the app
require __DIR__ . '/vendor/autoload.php';
$settings = require __DIR__ . '/src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/src/dependencies.php';

// Register routes
require __DIR__ . '/src/routes.php';

require __DIR__ . '/src/dbguy.php';

require __DIR__ . '/src/emailguy.php';

/*
spl_autoload_register(function ($classname) {
   require(__DIR__ . "/../models/" . $classname . ".php");
});
*/


$app->run();




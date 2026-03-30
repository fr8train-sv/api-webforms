<?php

/*
 * BOOTSTRAPPING
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use Slim\Factory\AppFactory;
use middleware\Middlewares;
use exceptions\Exceptions;
use config\Routes;
use Dotenv\Dotenv;

/*
 * CONFIG SETTINGS
 */

$dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../');
$dotenv->load();

$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addRoutingMiddleware();
Middlewares::register($app);

Exceptions::register($app);
Routes::register($app);

$app->run();

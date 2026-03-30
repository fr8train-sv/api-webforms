<?php


namespace middleware;

use middleware\before\JSONParsedBodyMiddleware;
use Slim\App;


class Middlewares
{
    public static function register(App $app) {
        $app->add(new JSONParsedBodyMiddleware());
        $app->add(new CorsMiddleware());
    }
}
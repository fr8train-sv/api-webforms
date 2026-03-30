<?php

namespace config;

use controllers\BaseController;
use controllers\HealthFormController;
use controllers\HelloWorldController;
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Routes
{
    public static function register(App $app) {
        $app->options('/{routes:.+}', function (Request $request, Response $response, $args) {
            return $response;
        });

        $app->get('/', function (Request $request, Response $response) {
            $controller = new BaseController();
            return $controller->json($response, [ 'message' => 'You have reached us. Please leave us a message.' ]);
        });

        $app->post('/healthform', HealthFormController::class.':post');
    }
}
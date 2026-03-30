<?php


namespace controllers;

use models\ServiceResponse;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;

class BaseController
{
    public function json(Response $response, mixed $payload, int $httpCode = 200) : Response
    {
        if (!is_array($payload)) $payload = (array) $payload;

        $status = [
            'status' => $httpCode === 200 ? 'OK' : 'error'
        ];

        $payload = json_encode(array_merge($status, $payload));

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Length', strlen($payload))
            ->withStatus($httpCode);
    }

    public function error(Response $response, Logger $log, ServiceResponse $serviceResponse) : Response {
        $payload = [
            'status' => 'error',
        ];

        if (!empty($serviceResponse->message)) $payload['message'] = $serviceResponse->message;

        if (!empty($serviceResponse->exception)) {
            $exception = [
                'message' => $serviceResponse->exception->getMessage(),
                'trace' => $serviceResponse->exception->getTrace()
            ];

            $log->error(json_encode($exception));
            $payload['exception'] = $exception;
        }

        return $this->json($response, $payload, $serviceResponse->http_code);
    }
}
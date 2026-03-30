<?php


namespace middleware\before;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class JSONParsedBodyMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        if (empty($request->getParsedBody()) &&
            in_array('application/json', $request->getHeader('Content-Type'))) {
            $input = json_decode((string)$request->getBody(), true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $request = $request->withParsedBody($input);
            }
        }

        return $handler->handle($request);
    }
}
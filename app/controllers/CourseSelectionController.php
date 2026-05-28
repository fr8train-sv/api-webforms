<?php

namespace controllers;

use factories\LoggerFactory;
use factories\ObjectFactory;
use models\BasicCourseSelectionRequest;
use models\CourseSelectionSubmission;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use singletons\CourseSelectionServiceSingleton as csss;

class CourseSelectionController extends BaseController
{
    protected Logger $log;

    public function __construct()
    {
        $this->log = LoggerFactory::createLogger(static::class);
    }

    public function queryPriorSubmission(Request $request, Response $response): Response
    {
        $serviceResponse = csss::instantiate()->queryPriorSubmission(ObjectFactory::loadClass(BasicCourseSelectionRequest::class, $request->getParsedBody()));
        return match ($serviceResponse->http_code) {
            200 => $this->json($response, [
                'message' => $serviceResponse->message,
                'payload' => $serviceResponse->payload,
            ]),
            default => $this->error($response, $this->log, $serviceResponse)
        };
    }

    public function storeSubmission(Request $request, Response $response): Response
    {
        $serviceResponse = csss::instantiate()->storeSubmission(ObjectFactory::loadClass(CourseSelectionSubmission::class, $request->getParsedBody()));
        return match ($serviceResponse->http_code) {
            200 => $this->json($response, [
                'message' => $serviceResponse->message,
                'payload' => $serviceResponse->payload,
            ]),
        };
    }

    public function fetchStudent(Request $request, Response $response): Response
    {
        $serviceResponse = csss::instantiate()->fetchStudent(ObjectFactory::loadClass(BasicCourseSelectionRequest::class, $request->getParsedBody()));
        return match ($serviceResponse->http_code) {
            200 => $this->json($response, [
                'message' => $serviceResponse->message,
                'payload' => $serviceResponse->payload,
            ]),
            default => $this->error($response, $this->log, $serviceResponse)
        };
    }
}
<?php

namespace controllers;

use Carbon\Carbon;
use factories\LoggerFactory;
use models\HealthFormInput;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use singletons\HealthFormServiceSingleton;

class HealthFormController extends BaseController
{
    protected Logger $log;

    public function __construct()
    {
        $this->log = LoggerFactory::createLogger(static::class);
    }

    public function post(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        $input = new HealthFormInput();

        $input->date = Carbon::parse($body['date']);
        $input->student->firstname = $body['student']['firstName'];
        $input->student->lastname = $body['student']['lastName'];
        $input->student->email = $body['student']['email'];
        $input->student->weight = $body['student']['weight'];
        $input->student->height->feet = $body['student']['height']['feet'];
        $input->student->height->inches = $body['student']['height']['inches'];
        $input->assessment->one_mile_run->minutes = $body['assessment']['oneMileRun']['minutes'];
        $input->assessment->one_mile_run->seconds = $body['assessment']['oneMileRun']['seconds'];
        $input->assessment->curl_ups = $body['assessment']['curlUp']['reps'];
        $input->assessment->trunk_lift = $body['assessment']['trunkLift']['inches'];
        $input->assessment->push_ups = $body['assessment']['pushUps']['reps'];
        $input->assessment->shoulder_stretch->left = $body['assessment']['shoulderStretch']['left'];
        $input->assessment->shoulder_stretch->right = $body['assessment']['shoulderStretch']['right'];
        $serviceResponse = HealthFormServiceSingleton::getInstance()->saveHealthForm($input);

        return match ($serviceResponse->http_code) {
            200 => $this->json($response, [
                'message' => $serviceResponse->message,
                'payload' => $serviceResponse->payload,
            ]),
            default => $this->error($response, $this->log, $serviceResponse)
        };
    }
}
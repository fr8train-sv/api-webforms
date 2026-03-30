<?php

namespace models;

use Carbon\Carbon;
use stdClass;

class HealthFormInput
{
    public Carbon $date;
    public stdClass $student;
    public stdClass $assessment;

    public function __construct()
    {
        $this->date = Carbon::now();
        $this->student = (object) [
            'firstname' => null,
            'lastname' => null,
            'email' => null,
            'weight' => null,
            'height' => (object) [
                'feet' => null,
                'inches' => null,
            ],
        ];
        $this->assessment = (object) [
            'one_mile_run' => (object) [
                'minutes' => null,
                'seconds' => null,
            ],
            'curl_ups' => null,
            'trunk_lift' => null,
            'push_ups' => null,
            'shoulder_stretch' => (object) [
                'left' => false,
                'right' => false,
            ]
        ];
    }
}
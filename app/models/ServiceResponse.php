<?php

namespace models;

use Throwable;
use traits\CastableTrait;
use Exception;

class ServiceResponse
{
    use CastableTrait;

    public int $http_code;
    public string $message;
    public mixed $payload;
    public Exception|Throwable|null $exception;

    public function __construct(int       $http_code = 200,
                                string    $message = '',
                                mixed     $payload = null,
                                Exception|Throwable $exception = null)
    {
        $this->http_code = $http_code;
        $this->message = $message;
        $this->payload = $payload;
        $this->exception = $exception;
    }
}
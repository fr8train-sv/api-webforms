<?php

namespace traits;

use models\ServiceResponse;
use ReflectionClass;
use attributes\Required;

trait ValidatableTrait
{
    public function validate(): array
    {
        $errors = [];
        $reflection = new ReflectionClass($this);

        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(Required::class);

            if (!empty($attributes)) {
                $name = $property->getName();
                $value = $this->$name;

                if (empty($value)) {
                    $errors[] = "Property '$name' is required.";
                }
            }
        }

        return $errors;
    }

    public function formatServiceResponse(array $errors): ServiceResponse
    {
        return ServiceResponse::cast([
            'http_code' => 422,
            'message' => 'Required parameters are missing: ['.implode(', ', $errors).']'
        ]);
    }
}
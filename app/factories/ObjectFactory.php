<?php

namespace factories;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionObject;

class ObjectFactory
{
    protected static array $subModels = [];

    protected static function transferCarbonProperty(&$object, &$data, string $propertyName, string $_propertyName): void
    {
        if (!empty($data->{$_propertyName})) {
            $object->{$propertyName} = Carbon::parse($data->{$_propertyName});
        } else $object->{$propertyName} = null;
    }

    protected static function transferUuidProperty(&$object, &$data, string $propertyName, string $_propertyName): void
    {
        $object->{$propertyName} = match (gettype($data->{$_propertyName})) {
            'string' => Uuid::fromString($data->{$_propertyName}),
            default => $data->{$_propertyName},
        };
    }

    protected static function gatherSubModelProperties($data, $subModelProperty): array
    {
        $properties = [];
        $reflection = new ReflectionObject($data);

        foreach ($reflection->getProperties() as $property) {
            $propertyName = $property->getName();
            if (str_starts_with($propertyName, $subModelProperty)) {
                $newSubModelProperty = substr($propertyName, strlen($subModelProperty));
                $properties[$newSubModelProperty] = $data->{$propertyName};
            }
        }

        return $properties;
    }

    /**
     * @throws ReflectionException
     */
    protected static function transferSubModelProperty(&$object, &$data, string $propertyName, string $_propertyName, string $propertyTypeName): void
    {
        // GET CONTEXT
        $subModel = self::gatherSubModelProperties($data, $_propertyName);
        $object->{$propertyName} = self::loadClass($propertyTypeName, $subModel);
    }

    protected static function propertyNameCheck(&$data, string $propertyName): ?string
    {
        if (property_exists($data, $propertyName)) {
            return $propertyName;
        } else {
            // CAMEL CASE TO SNAKE CASE CONVERSION
            $_propertyName = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $propertyName));

            // CHECK TO SEE IF PROPERTY EXISTS IN DATA OBJECT AS-IS
            if (property_exists($data, $_propertyName)) return $_propertyName;

            // CHECK TO SEE IF PROPERTY EXISTS AS NESTED PROPERTY
            $nestedPropertyName = "$_propertyName.";
            foreach ($data as $key => $value) {
                if (str_starts_with($key, $nestedPropertyName)) return $nestedPropertyName;
            }
            return null;
        }
    }

    /**
     * @throws ReflectionException
     */
    public static function loadClass(string $className, object|array $data): object
    {
        // CONSISTENCY CHECK - ALWAYS CONVERT ARRAYS TO OBJECTS
        if (is_array($data)) $data = (object)$data;

        // STEP 1: REFLECT OBJECT CLASS
        $reflection = new ReflectionClass($className);

        // STEP 1A: CHECK IF CLASS IS INSTANTIABLE BECAUSE IT NEEDS TO BE
        if (!$reflection->isInstantiable()) throw new ReflectionException("Class $className is not instantiable.");

        // STEP 1B: INSTANTIATE OBJECT (ALL OF OUR MODELS ARE CREATED WITHOUT CONSTRUCTOR PARAMETERS)
        $object = $reflection->newInstance();

        // STEP 2: GRAB ALL PUBLIC PROPERTIES FROM NEWLY REFLECTED OBJECT
        // REMEMBER THIS IS LOADING OUR MODELS AND OUR MODELS ARE PRIMARILY PUBLIC-BASED PROPERTIES
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $propertyType = $property->getType();
            $propertyTypeName = $propertyType->getName();

            // STEP 2A: CHECK IF PROPERTY EXISTS IN DATA OBJECT AND CORRECT FOR CAMEL CASE
            // $_propertyName IS THE PROPERTY NAME IN THE DATA OBJECT
            $_propertyName = self::propertyNameCheck($data, $propertyName);
            if (is_null($_propertyName)) continue;

            if (!$propertyType instanceof ReflectionNamedType) throw new ReflectionException("Property $propertyName has invalid type.");

            if (!$propertyType->isBuiltin()) {
                switch ($propertyTypeName) {
                    case Carbon::class:
                        self::transferCarbonProperty($object, $data, $propertyName, $_propertyName);
                        break;
                    case UuidInterface::class:
                        self::transferUuidProperty($object, $data, $propertyName, $_propertyName);
                        break;
                    default:
                        if (in_array($propertyTypeName, self::$subModels)) {
                            self::transferSubModelProperty($object, $data, $propertyName, $_propertyName, $propertyTypeName);
                        } else {
                            $object->{$propertyName} = $data->{$_propertyName};
                        }
                        break;
                }
            } else {
                $object->{$propertyName} = $data->{$_propertyName};
            }
        }

        return $object;
    }
}
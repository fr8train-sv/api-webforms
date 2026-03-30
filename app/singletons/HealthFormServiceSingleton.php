<?php

namespace singletons;

use interfaces\SingletonInterface;
use services\HealthFormService;

class HealthFormServiceSingleton implements SingletonInterface
{
    private static ?HealthFormService $instance = null;

    private function __construct() {}

    public static function instantiate(): HealthFormService
    {
        return new HealthFormService(
            AppDatabaseSingleton::getInstance(),
            DataWarehouseSingleton::getInstance()
        );
    }

    public static function getInstance(): HealthFormService
    {
        return self::$instance ??= self::instantiate();
    }
}
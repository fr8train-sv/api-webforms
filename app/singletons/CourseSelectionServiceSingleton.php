<?php

namespace singletons;

use interfaces\SingletonInterface;
use services\CourseSelectionService;

class CourseSelectionServiceSingleton implements SingletonInterface
{
    private static ?CourseSelectionService $instance = null;

    private function __construct() {}

    public static function instantiate(): CourseSelectionService
    {
        return new CourseSelectionService(
            AppDatabaseSingleton::getInstance(),
            DataWarehouseSingleton::getInstance()
        );
    }

    public static function getInstance(): CourseSelectionService
    {
        return self::$instance ??= self::instantiate();
    }
}
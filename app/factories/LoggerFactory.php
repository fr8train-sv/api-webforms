<?php


namespace factories;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use ReflectionClass;
use ReflectionException;

class LoggerFactory
{
    public static function createLogger(string $name) : Logger
    {
        try {
            // Extract class name (removes namespace)
            $className = (new ReflectionClass($name))->getShortName();
        } catch (ReflectionException $e) {
            $className = "Default";
        }

        $logPath = __DIR__."/../../logs/$className.log";
        $log = new Logger($className.'Logger');
        $log->pushHandler(new StreamHandler($logPath));

        return $log;
    }
}
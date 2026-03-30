<?php

namespace singletons;

use interfaces\SingletonInterface;
use PDO;

class AppDatabaseSingleton implements SingletonInterface
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function instantiate(): object
    {
        $host = getenv('DB_HOST');
        $port = intval(getenv('DB_PORT'));
        $db = getenv('DB_DATABASE');
        $user = getenv('DB_USERNAME');
        $pass = getenv('DB_PASSWORD');

        $dsn = sprintf(
            'sqlsrv:Server=%s,%d;Database=%s;Encrypt=yes;TrustServerCertificate=no;LoginTimeout=10',
            $host,
            $port,
            $db
        );

        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        ]);
    }

    public static function getInstance(): object
    {
        return self::$instance ??= self::instantiate();
    }
}
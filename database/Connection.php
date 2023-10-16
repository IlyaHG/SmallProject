<?php

require_once '../vendor/autoload.php';
use PDO;
class Connection
{
    private $pdo;
    private static $instance = null;

    private function make()
    {
        $host = DB_HOST;
        $dbname = DB_NAME;
        $username = DB_USERNAME;
        $userpass = DB_PASSWORD;
        $charset = DB_CHARSET;
        return new \PDO(
            "{$host};
             dbname={$dbname};
             charset={$charset}",
            $username,
            $userpass
        );
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            {
                self::$instance = self::make();
            }
        }
    }
}
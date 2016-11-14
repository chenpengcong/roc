<?php

namespace Roc\Models;

use Roc\Config\Database;

class Model
{
    protected function connect($database)
    {
        $dsn = Database::$configs[$database]['dsn'];
        $user = Database::$configs[$database]['user'];
        $password = Database::$configs[$database]['password'];
        $pdo = new \PDO($dsn, $user, $password);
        return $pdo;
    }
}
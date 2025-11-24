<?php

namespace Model;

use PDO;
use PDOException;

require_once __DIR__ . "/../Config/configuration.php";

class Connection {
    private static $stmt;

    public static function getInstance(): PDO {
        if(empty(self::$stmt)) {
            try {
                self::$stmt = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . '', DB_USER, DB_PASSWORD);
            }
            catch (PDOException $error) {
                die("Erro de conexão: " . $error->getMessage());
            }
        }
        return self::$stmt;
    }
}

?>
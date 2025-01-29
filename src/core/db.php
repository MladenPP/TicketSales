<?php

namespace ProdajaKarata\core;

use ProdajaKarata\core\Config;
use PDO ;

class Db{
    private static $instance;

    private static function connect () :PDO {

        $dsn = "mysql:host=localhost;dbname=prodaja_karata";
        $dbconfig = Config::getInstance()->get('db');
        
        try {
            $pdo = new PDO($dsn, $dbconfig['user'],$dbconfig['password']);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
        return $pdo;
    
    }

    public static function getInstance(){
        if(self::$instance==null){
            self::$instance = self::connect();
        }
        return self::$instance;
    }
}



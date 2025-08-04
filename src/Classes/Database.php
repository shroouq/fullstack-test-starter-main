<?php
namespace App\Classes;
use PDO;


header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");







class Database
{
    private $host = "localhost";
    private $user = "root";
    private $password = "";
    private $dbName = "scandiweb";

    public function connect()
    {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbName;
        $pdo = new PDO($dsn, $this->user, $this->password);
        return $pdo;
    }
}

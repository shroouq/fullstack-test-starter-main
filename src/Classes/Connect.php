<?php
namespace App\Classes;

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");
class Connect extends Database
{
    
    public function setOrder($data)
    {
        try {
            $columnString = implode(',', array_keys($data));
            $valueString = implode(',', array_fill(0, count($data), '?'));
            $sql = "INSERT INTO orders ({$columnString}) VALUES ({$valueString})";

            $stmt = $this->connect()->prepare($sql);
            $success = $stmt->execute(array_values($data));

            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                file_put_contents('graphql.log', "DB Error: " . print_r($errorInfo, true), FILE_APPEND);
                throw new \Exception("DB Insert failed: " . $errorInfo[2]);
            }
        } catch (\Throwable $e) {
            file_put_contents("graphql.log", "setUser ERROR: " . $e->getMessage(), FILE_APPEND);
            throw $e;
        }
    }


}
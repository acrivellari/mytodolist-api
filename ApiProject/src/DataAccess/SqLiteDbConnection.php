<?php

class SqLiteDbConnection
{
    private static string $dbPath = __DIR__ . '/../../config/mydb.sqlite';

    public static function createConnection() : PDO {
        try {
            $pdo = new PDO("sqlite:" . SqLiteDbConnection::$dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
}
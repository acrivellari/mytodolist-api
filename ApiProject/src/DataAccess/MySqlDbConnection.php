<?php

class MySqlDbConnection
{
    private static string $configPath = __DIR__ . '/../../config/dbConfig.json';

    /**
     * Creates and returns a new PDO connection: object to connect PHP to a db
     *
     * @return PDO
     * @throws Exception
     */
    public static function createConnection(): PDO
    {
        // Check if the config file exists
        if (!file_exists(MySqlDbConnection::$configPath)) {
            throw new Exception("Configuration file not found");
        }

        // Read and decode the JSON content
        $jsonContent = file_get_contents(MySqlDbConnection::$configPath);
        $config = json_decode($jsonContent, true);

        // Validate the decoded configuration
        if ($config === null || !isset($config['host'], $config['dbname'], $config['username'], $config['password'])) {
            throw new Exception("Invalid or incomplete JSON configuration.");
        }

        // Extract credentials
        $host = $config['host'];
        $dbname = $config['dbname'];
        $username = $config['username'];
        $password = $config['password'];
        $charset = $config['charset'] ?? 'utf8mb4';

        // Create a DSN string
        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

        try {
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
}
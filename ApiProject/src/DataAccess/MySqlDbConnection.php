<?php

class MySqlDbConnection
{
    private static string $configPath = __DIR__ . '/../../config/dbConfig.json';

    /**
     * Creates and returns a new mysqli connection: object to connect PHP to a mysql db
     *
     * @return mysqli
     * @throws mysqli_sql_exception
     */
    public static function createConnection(): mysqli
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

        $mysqli = new mysqli($host, $username, $password, $dbname);
        if ($mysqli->connect_error) {
            throw new mysqli_sql_exception("{$mysqli->connect_error} ({$mysqli->connect_errno})");
        }

        return $mysqli;
    }
}
<?php

include_once __DIR__ . '/../ApiProject/src/DataAccess/MySqlDbConnection.php';
include_once __DIR__ . '/../ApiProject/src/DataAccess/SqLiteDbConnection.php';


$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$urlParts = explode('/', trim ($requestUri, '/'));

if ($urlParts[0] != 'api') {
    http_response_code(404);
} else {
    if ($method == 'GET' && $urlParts[1] == 'users') {
        if ($urlParts[2] == 'evilcrive') {
            echo "calling api...";

            $pdo = MySqlDbConnection::createConnection();

            try {
                // 1. Prepare the SQL statement.
                // Use named placeholders (e.g., :id) to make the query safe.
                $stmt = $pdo->prepare("SELECT id, username, name, surname FROM users WHERE username = :username");

                // 2. Bind the parameter.
                // This securely links the value of a PHP variable to the placeholder in the SQL query.
                $userId = 1;
                $stmt->bindParam(':username', $urlParts[2], PDO::PARAM_STR);

                // 3. Execute the statement.
                $stmt->execute();

                // 4. Fetch the results.
                // PDO::FETCH_ASSOC returns the result as an associative array.
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // 5. Check if a user was found and display the data.
                if ($user) {
                    echo "User found!";
                    print_r($user);
                } else {
                    echo "No user found with that ID.";
                }

            } catch (PDOException $e) {
                // Handle any exceptions that occurred during the process.
                echo "Query failed: " . $e->getMessage();
            }
        }
        else if ($urlParts[2] == 'crivez') {
            try {
            $pdo = SqLiteDbConnection::createConnection();
            // 1. Prepare the SQL statement.
                // Use named placeholders (e.g., :id) to make the query safe.
                $stmt = $pdo->prepare("SELECT * FROM users");
                
                // 3. Execute the statement.
                $stmt->execute();

                // 4. Fetch the results.
                // PDO::FETCH_ASSOC returns the result as an associative array.
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // 5. Check if a user was found and display the data.
                if ($user) {
                    echo "User found!";
                    print_r($user);
                } else {
                    echo "No user found with that ID.";
                }
            } catch (Exception $e) {
                echo "Error";
                print_r ($e);
            }
        }
    }
}
<?php

include_once __DIR__ . '/../Models/User.php';

/**
 * Repository class for users table (mysql db)
 */
class UsersRepository {
    private static string $tableName = "users";

    /**
     * Given username and hashed password, returns the user id. Returns null if the credentials aren't correct
     * @param string $user
     * @param string $pwd
     */
    public static function getId(string $user, string $pwd): ?int {
        try {
            $sqlCommand = "SELECT id FROM ".UsersRepository::$tableName." WHERE username = ? and password = ?";
            
            $pdo = MySqlDbConnection::createConnection();
            $stmt = $pdo->prepare($sqlCommand);
            $stmt->bind_param("ss", $user, $pwd);
            $stmt->execute();

            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($row) { 
                    return $row['id'] ?? null; 
                }
            }

            return null;
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Given the user id, returns the User entity
     * @param int $id
     * @return User|null
     */
    public static function getUserInfoById(int $id): User|null {
        try {
            $tableName = UsersRepository::$tableName;
            $sqlCommand = "SELECT * FROM {$tableName} WHERE id=?";
            
            $mysqli = MySqlDbConnection::createConnection();
            $stmt = $mysqli->prepare($sqlCommand);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            
            $row = $stmt->get_result()->fetch_assoc();

            $entity = null;
            if ($row !== null && $row !== false) {
                $entity = User::convert($row);
            }
            return $entity;
            
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Insert a new entity on the database
     * @return string|null user id, null if not-inserted
     */
    public static function insertUser (
        string $username, 
        string $name, 
        string $surname, 
        string $passwordHashed,
        string|null $email = null
    ) : int|string|null {
        try {
            $tableName = UsersRepository::$tableName;

            $mysqli = MySqlDbConnection::createConnection();
            $sqlCommand = $mysqli->prepare(
                <<<QUERY
                    INSERT INTO {$tableName} 
                    (username, name, surname, email, password) 
                    VALUES (?, ?, ?, ?, ?)
                QUERY
            );

            $sqlCommand->bind_param("sssss", $username, $name, $surname, $email, $passwordHashed);
            $sqlCommand->execute();

            $id = $mysqli->insert_id;
            $sqlCommand->close();
            $mysqli->close();
            
            return $id;
        }
        catch (mysqli_sql_exception $ex) {
            if ($ex->getCode() == 1062) {
                return null;
            }
            throw $ex;
        }
        catch (\Throwable $ex) {
            throw $ex;
        }
    }

}
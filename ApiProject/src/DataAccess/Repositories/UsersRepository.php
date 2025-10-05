<?php

include_once __DIR__ . '/../Models/User.php';

/**
 * Repository class for users table (mysql db)
 */
class UsersRepository {
    private static string $tableName = "users";
    private mysqli $connection;

    /**
     * Inject connection with DI
     * @param mysqli $connection
     */
    public function __construct(mysqli $connection) {
        $this->connection = $connection;
    }

    /**
     * Given username and hashed password, returns the user id. Returns null if the credentials aren't correct
     * @param string $user
     * @param string $pwd
     */
    public function getId(string $user, string $pwd): ?int {
        try {
            $sqlCommand = "SELECT id FROM ".UsersRepository::$tableName." WHERE username = ? and password = ?";
        
            $stmt = $this->connection->prepare($sqlCommand);
            $stmt->bind_param("ss", $user, $pwd);
            $stmt->execute();

            $row = $stmt->get_result()->fetch_assoc();
            return ($row !== null && $row !== false) 
                ? (int)$row['id']
                : null;
        } catch (Exception) {
            return null;
        }
        finally {
            $stmt->close();
        }
    }

    /**
     * Given the user id, returns the User entity
     * @param int $id
     * @return User|null
     */
    public function getUserInfoById(int $id): User|null {
        try {
            $tableName = UsersRepository::$tableName;
            $sqlCommand = "SELECT * FROM {$tableName} WHERE id=?";
            
            $stmt = $this->connection->prepare($sqlCommand);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            
            $row = $stmt->get_result()->fetch_assoc();
            return ($row !== null && $row !== false) 
                ? User::convert($row)
                : null;
            
        } catch (Exception) {
            return null;
        }
        finally {
            $stmt->close();
        }
    }

    /**
     * Insert a new entity on the database
     * @return string|null user id, null if not-inserted
     */
    public function insertUser (
        string $username, 
        string $name, 
        string $surname, 
        string $passwordHashed,
        string|null $email = null
    ) : int|string|null {
        try {
            $tableName = UsersRepository::$tableName;
            $sqlCommand = "INSERT INTO {$tableName} (username, name, surname, email, password) VALUES (?,?,?,?,?)";
            
            $stmt = $this->connection->prepare($sqlCommand);
            $stmt->bind_param("sssss", $username, $name, $surname, $email, $passwordHashed);
            $stmt->execute();

            return $this->connection->insert_id;
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
        finally {
            $stmt->close();
        }
    }

}
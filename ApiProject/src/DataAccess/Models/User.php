<?php

class User {
    public int $id;
    public string $name;
    public string $surname;
    public string $username;
    public ?string $email;

    /**
     * Constructor of User
     * @param int $id
     * @param string $name
     * @param string $surname
     * @param string $username
     * @param ?string $email
     */
    public function __construct(int $id, string $name, string $surname, string $username, ?string $email = null) {
        $this->id = $id;
        $this->name = $name;
        $this->surname = $surname;
        $this->username = $username;
        $this->email = $email;
    }

    /**
     * Static factory method: convert db entity to User entity
     * @param array $row
     * @return User|null Return null if conversion fails
     */
    public static function convert(array $row): ?User {
        try {
            return new User(
                $row['id'] ?? null, 
                $row['name'] ?? null, 
                $row['surname'] ?? null, 
                $row['username'] ?? null, 
                $row['email'] ?? null);
        }
        catch (Exception) {
            return null;
        }
    }
}
<?php 

class SignupRequest {
    public string $username;
    public string $name;
    public string $surname;
    public string|null $email;
    public string $password;

    /**
     * Private constructor
     * @param string $username
     * @param string $name
     * @param string $surname
     * @param string $passwordHashed
     * @param string|null $email
     */
    private function __construct(string $username, string $name, string $surname, string $passwordHashed, string|null $email) {
        $this->username = $username;
        $this->name = $name;
        $this->surname = $surname;
        $this->password = $passwordHashed;
        $this->email = $email ?? null;
    }

    /**
     * Static factory method: from json string returns SignupRequest instance.
     * @param string $requestJsonBody request body
     * @throws \RuntimeException if json is malformed or with invalid structure
     * @throws \MyValidationException json fields missing or invalid
     * @return SignupRequest new dto instance
     */
    public static function fromRawJson(string $requestJsonBody): SignupRequest {
        $data = json_decode($requestJsonBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Malformed JSON.");
        }
        if (!is_array($data)) {
            throw new RuntimeException("Invalid JSON structure.");
        }

        $email = null;
        if (ArrayUtils::checkIfValueIsString($data, 'username' == false)) {
            throw new MyValidationException("username");
        }
        if (ArrayUtils::checkIfValueIsString($data, 'name') == false) {
            throw new MyValidationException("name");
        }
        if (ArrayUtils::checkIfValueIsString($data, 'surname') == false) {
            throw new MyValidationException("surname");
        }
        if (ArrayUtils::checkIfValueIsString($data, 'email')) {
            $email = (string)$data['email'];
        }
        if (ArrayUtils::checkIfValueIsString($data, 'password') == false) {
            throw new MyValidationException("password");
        }

        return new self (
            (string)$data['username'], 
            (string)$data['name'], 
            (string)$data['surname'], 
            (string)$data['password'], 
            $email
        );
    }
}
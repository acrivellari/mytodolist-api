<?php

/**
 * Dto class for the request body of the api endpoint POST /users/login
 */
class DoLoginRequest {
    public string $username;
    public string $password;

    /**
     * Private constructor
     * @param string $username
     * @param string $password
     */
    private function __construct(string $username, string $password) {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Static factory method: from json string returns DoLoginRequest instance.
     * @param string $requestJsonBody request body
     * @throws \RuntimeException if json is malformed or with invalid structure
     * @throws \MyValidationException json fields missing
     * @return DoLoginRequest new dto instance
     */
    public static function fromRawJson(string $requestJsonBody): DoLoginRequest {
        $data = json_decode($requestJsonBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Malformed JSON.");
        }
        if (!is_array($data)) {
            throw new RuntimeException("Invalid JSON structure.");
        }
        
        if (ArrayUtils::checkIfValueIsString($data, 'username') == false) {
            throw new MyValidationException("username");
        }
        if (ArrayUtils::checkIfValueIsString($data, 'password') == false) {
            throw new MyValidationException("password");
        }

        return new self((string)$data['username'], (string)$data['password']);
    }


}
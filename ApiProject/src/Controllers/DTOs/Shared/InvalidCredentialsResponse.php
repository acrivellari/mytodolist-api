<?php

class InvalidCredentialsResponse extends IResponse {
    public function getHttpCode(): int { 
        return 401; 
    }
    
    public function jsonSerialize(): mixed {
        return [
            'message' => 'Unauthorized: Invalid credentials.', 
            'error_code' => 'CREDS_INVALID'
        ];
    }
}
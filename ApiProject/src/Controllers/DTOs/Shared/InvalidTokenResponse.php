<?php

/**
 * Unauthorized response (401) for token invalid
 */
class InvalidTokenResponse extends IResponse {
    public function getHttpCode(): int { 
        return 401; 
    }

    public function jsonSerialize(): mixed {
        return [
            'message' => 'Unauthorized: Token is expired, invalid or absent.', 
            'error_code' => 'TOKEN_INVALID'
        ];
    }
}
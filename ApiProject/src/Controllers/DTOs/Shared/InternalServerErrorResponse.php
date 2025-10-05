<?php

class InternalServerErrorResponse extends IResponse {

    public function getHttpCode(): int {
        return 500;
    }

    public function jsonSerialize(): mixed {
        return [
            'message' => "Internal Server Error: an unexpected error occurred.", 
            'error_code' => 'INTERNAL_SERVER_ERROR'
        ];
    }
}
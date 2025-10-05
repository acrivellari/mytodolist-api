<?php

class InternalServerErrorResponse extends IResponse {
    private string $message;
    public function __construct(string|null $message = null) {
        $this->message = ($message !== null) ? "({$message})." : ".";
    }

    public function getHttpCode(): int {
        return 500;
    }

    public function jsonSerialize(): mixed {
        return [
            'message' => "Internal Server Error: an unexpected error occurred{$this->message}", 
            'error_code' => 'INTERNAL_SERVER_ERROR'
        ];
    }
}
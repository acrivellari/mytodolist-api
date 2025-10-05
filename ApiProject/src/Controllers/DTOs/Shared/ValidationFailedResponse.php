<?php

class ValidationFailedResponse extends IResponse {
    private ?string $field = null;

    public function __construct(?string $field = null) { 
        $this->field = $field; 
    }

    public function getHttpCode(): int {
        return 400;
    }

    public function jsonSerialize(): mixed {
        return [
            'message' => "Bad request: ". $this->field . " field is missing or invalid.", 
            'error_code' => "VALIDATION_FAILED"
        ];
    }
}
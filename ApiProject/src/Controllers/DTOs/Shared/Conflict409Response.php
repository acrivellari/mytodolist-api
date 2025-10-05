<?php

class Conflict409Response extends IResponse {
    public function getHttpCode(): int {
        return 409;
    }
    public function jsonSerialize(): mixed {
        return [
            'message' => "Conflict: the resource conflicts with an existing resource.", 
            'error_code' => 'CONFLICT_RESOURCE'
        ];
    }
}
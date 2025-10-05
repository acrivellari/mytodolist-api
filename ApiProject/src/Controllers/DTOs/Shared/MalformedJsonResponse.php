<?php

class MalformedJsonResponse extends IResponse {
    public function getHttpCode(): int {
        return 400;
    }

    public function jsonSerialize(): mixed {
        return [
            'message' => "Bad request: the request body couldn't be parsed as a valid JSON.", 
            'error_code' => 'JSON_SYNTAX_ERROR'
        ];
    }
}
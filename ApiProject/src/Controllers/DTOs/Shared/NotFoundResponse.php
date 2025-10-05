<?php

class NotFoundResponse extends IResponse {
    public function getHttpCode(): int {
        return 404;
    }
    public function jsonSerialize(): mixed {
        return [
            'message' => "Not found: the requested URL doesn't match any resource.", 
            'error_code' => 'RESOURCE_NOT_FOUND'
        ];
    }

}
<?php

class Created201Response extends IResponse {
    private int $id;

    public function __construct(int|string $id) {
        $this->id = $id;
    }

    public function getHttpCode(): int {
        return 201;
    }

    public function jsonSerialize(): mixed {
        return [
            'status' => 'success',
            'message' => "Created: Resource successfully created.", 
            'id' => $this->id,
            'created_at' => (new DateTime('@' . time()))->setTimezone(new DateTimeZone('UTC'))->format(DateTime::ATOM) 
        ];
    }
}
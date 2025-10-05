<?php

class AuthenticateResponse extends IResponse {
    public string $token;
    public string $tokenType = 'Bearer';
    public int $expiresIn;

    public function __construct(string $token) {        
        $this->token = $token;

        $obj = JWT::decode($token);
        if ($obj !== null) {
            $this->expiresIn = $obj->exp;
        }
    }

    public function getHttpCode(): int {
        return 200;
    }

    /**
     * Required by the JsonSerializable interface -> define what gets encoded in the json
     * @return array{expires_in: int, token: string, token_type: string}
     */
    public function jsonSerialize(): mixed
    {
        return [
            'access_token' => $this->token,
            'token_type' => $this->tokenType,
            'expires_in' => (new DateTime('@' . $this->expiresIn))->setTimezone(new DateTimeZone('UTC'))->format(DateTime::ATOM) 
        ];
    }
}
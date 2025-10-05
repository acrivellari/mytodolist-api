<?php

class GetUserResponse extends IResponse {

    private string $userId;
    private string $username;
    private string $name;
    private string $surname;
    private ?string $email = null;
    private string $exp;

    /**
     * Initializer for reponse DTO for api endpoint /users
     * @param JwtPayload $tokenPayload
     * @param mixed $entity
     */
    public function __construct(JwtPayload $tokenPayload, ?User $entity = null) {
        $this->userId = $tokenPayload->userId;
        $this->username = $tokenPayload->username;
        $this->exp = $tokenPayload->exp;
        if ($entity !== null) {
            $this->name = $entity->name;
            $this->surname = $entity->surname;
            if (isset($entity->email) && $entity->email !== null) {
                $this->email = $entity->email;
            }
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
            'id' => $this->userId,
            'name' => $this->name,
            'surname' => $this->surname,
            'username' => $this->username,
            'email' => $this->email,
            'token_expires_in' => (new DateTime('@' . $this->exp))->setTimezone(new DateTimeZone('UTC'))->format(DateTime::ATOM) 
        ];
    }
}
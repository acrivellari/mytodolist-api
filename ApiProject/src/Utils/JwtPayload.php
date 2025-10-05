<?php

class JwtPayload implements \JsonSerializable {

    public int $userId;
    public string $username;
    public int $exp;

    public function __construct(int $userId, string $username, int $exp) {
        $this->userId = $userId;
        $this->username = $username;
        $this->exp = $exp;
    }

    /**
     * Given an associative array, convert it to an instance of JwtPayload. If it's not compatible, return null
     * @param array $payload
     * @return JwtPayload|null
     */
    public static function convert(array $payload): ?JwtPayload
    {
        $valid = false;
        if (isset($payload) && is_array($payload)) {
            if (isset($payload['user_id']) && isset($payload['username']) && isset($payload['exp'])) {
                if (filter_var($payload['user_id'], FILTER_VALIDATE_INT) && filter_var($payload['exp'], FILTER_VALIDATE_INT))
                {
                    $valid = true;
                }
            }
        }

        return $valid == false
            ? null
            : new JwtPayload((int)$payload['user_id'], $payload['username'], (int)$payload['exp']);
    }

    /**
     * If field 'exp' is not initialized or it's lower than actual timestamp in seconds, return true
     * @return bool
     */
    public static function isExpired($payload): bool {
        return !isset($payload->exp) || $payload->exp < time();
    }

    public function jsonSerialize(): mixed
    {
        return [
            'user_id' => $this->userId,
            'username' => $this->username,
            'exp' => $this->exp
        ];
    }
}
<?php

include_once __DIR__ . '/../Controllers/DTOs/Users/AuthenticateResponse.php';
include_once __DIR__ . '/../Controllers/DTOs/Users/GetUserResponse.php';
include_once __DIR__ . '/../Controllers/DTOs/Users/DoLoginRequest.php';
include_once __DIR__ . '/../Controllers/DTOs/Users/SignupRequest.php';
include_once __DIR__ . '/../DataAccess/Repositories/UsersRepository.php';

class UserController {

    /**
     * Routes mapping for users endpoints
     */
    public static array $routes = [
        'POST' => [
            'users/login' => [UserController::class, 'loginWithCredentials'],
            'users/signup' => [UserController::class, 'createNewUser']
        ],
        'GET' => [
            'users' => [UserController::class, 'loginWithToken'],
            'users/validate' => [UserController::class, 'loginWithToken']
        ]
    ];

    public static function main($url): void {
        $urlParts = explode(
            '/', 
            trim ($url, '/'), 
            2
        );

        $callable = UserController::$routes[$_SERVER['REQUEST_METHOD']][$urlParts[1]] ?? null;

        if (!$callable) {
            ResponseBuilder::outputResponse(new NotFoundResponse());
        } else if (!is_callable($callable)) {
            ResponseBuilder::outputResponse(new InternalServerErrorResponse());
        } else {
            call_user_func($callable);
        }
    }

    public static function loginWithToken(): void { 
        $tokenDecoded = JWT::extractFromHeaders(getallheaders());
        if ($tokenDecoded === null) {
            ResponseBuilder::outputResponse(new InvalidTokenResponse());
        } else {
            $userFromDb = UsersRepository::getUserInfoById($tokenDecoded->userId);
            $response = new GetUserResponse($tokenDecoded, $userFromDb);
            ResponseBuilder::outputResponse($response);
        }
    }

    public static function loginWithCredentials(): void {
        $id = null;
        try {
            $rawJson = file_get_contents('php://input');
            $dto = DoLoginRequest::fromRawJson($rawJson);
            $id = UsersRepository::getId($dto->username, $dto->password);

            if ($id === null) {
                ResponseBuilder::outputResponse(new InvalidCredentialsResponse());
            } else {
                // create new token
                $token = JWT::encode(new JwtPayload(
                    $id, 
                    $dto->username, 
                    time()+36000 // expires in 10 hours
                ));

                ResponseBuilder::outputResponse(new AuthenticateResponse($token));
            }
        }
        catch (RuntimeException) {
            $response = new MalformedJsonResponse();
            ResponseBuilder::outputResponse($response);
        }
        catch (MyValidationException $ex) {
            $response = new ValidationFailedResponse($ex->getMessage());
            ResponseBuilder::outputResponse($response);
        }
    }

    public static function createNewUser(): void {
        $dto = null;
        try {
            $rawJson = file_get_contents('php://input');
            $dto = SignupRequest::fromRawJson($rawJson);
        }
        catch(RuntimeException) {
            $response = new MalformedJsonResponse();
            ResponseBuilder::outputResponse($response);
        }
        catch (MyValidationException $ex) {
            $response = new ValidationFailedResponse($ex->getMessage());
            ResponseBuilder::outputResponse($response);
        }
        
        try {
            $id = UsersRepository::insertUser($dto->username, $dto->name, $dto->surname, $dto->password, $dto->email);
            if ($id == null) {
                ResponseBuilder::outputResponse(new Conflict409Response());
            } else {
                ResponseBuilder::outputResponse(new Created201Response($id));
            }
        }
        catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
}
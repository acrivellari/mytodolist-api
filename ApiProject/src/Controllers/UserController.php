<?php

include_once __DIR__ . '/../Controllers/DTOs/Users/AuthenticateResponse.php';
include_once __DIR__ . '/../Controllers/DTOs/Users/GetUserResponse.php';
include_once __DIR__ . '/../Controllers/DTOs/Users/DoLoginRequest.php';
include_once __DIR__ . '/../Controllers/DTOs/Users/SignupRequest.php';
include_once __DIR__ . '/../DataAccess/Repositories/UsersRepository.php';

class UserController {

    private UsersRepository $usersRepository;

    /**
     * Routes mapping for users endpoints
     */
    private static array $routes = [
        'POST' => [
            'users/login' => [UserController::class, 'loginWithCredentials'],
            'users/signup' => [UserController::class, 'createNewUser']
        ],
        'GET' => [
            'users' => [UserController::class, 'loginWithToken'],
            'users/validate' => [UserController::class, 'loginWithToken']
        ]
    ];

    /**
     * Inject users repository, through DI
     * @param UsersRepository $usersRepository
     */
    public function __construct(UsersRepository $usersRepository) {
        $this->usersRepository = $usersRepository;
    }

    public function dispatch($fullUri, $requestMethod): void {
        $requestUri = explode(
            '/', 
            trim ($fullUri, '/'), 
            2
        )[1];

        $callable = UserController::$routes[$requestMethod][$requestUri] ?? null;

        if (!$callable) {
            ResponseBuilder::outputResponse(new NotFoundResponse());
        } else if (!is_callable($callable)) {
            ResponseBuilder::outputResponse(new InternalServerErrorResponse());
        } else {
            call_user_func($callable);
        }
    }

    public function loginWithToken(): void { 
        $tokenDecoded = JWT::extractFromHeaders(getallheaders());
        if ($tokenDecoded === null) {
            ResponseBuilder::outputResponse(new InvalidTokenResponse());
        } else {
            $userFromDb = $this->usersRepository->getUserInfoById($tokenDecoded->userId);
            $response = new GetUserResponse($tokenDecoded, $userFromDb);
            ResponseBuilder::outputResponse($response);
        }
    }

    public function loginWithCredentials(): void {
        $id = null;
        try {
            $rawJson = file_get_contents('php://input');
            $dto = DoLoginRequest::fromRawJson($rawJson);
            $id = $this->usersRepository->getId($dto->username, $dto->password);

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

    public function createNewUser(): void {
        $dto = null;
        try {
            $rawJson = file_get_contents('php://input');
            $dto = SignupRequest::fromRawJson($rawJson);
        }
        catch(RuntimeException) {
            $response = new MalformedJsonResponse();
            ResponseBuilder::outputResponse($response);
            return;
        }
        catch (MyValidationException $ex) {
            $response = new ValidationFailedResponse($ex->getMessage());
            ResponseBuilder::outputResponse($response);
            return;
        }
        
        try {
            $id = $this->usersRepository->insertUser($dto->username, $dto->name, $dto->surname, $dto->password, $dto->email);
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
<?php

include_once __DIR__ . '/../Controllers/DTOs/Users/AuthenticateResponse.php';
include_once __DIR__ . '/../Controllers/DTOs/Users/GetUserResponse.php';
include_once __DIR__ . '/../Controllers/DTOs/Users/DoLoginRequest.php';
include_once __DIR__ . '/../Controllers/DTOs/Users/SignupRequest.php';
include_once __DIR__ . '/../DataAccess/Repositories/UsersRepository.php';

/**
 * Controller for users endpoints
 */
class UserController {

    private UsersRepository $usersRepository;

    /**
     * Inject users repository, through DI
     * @param UsersRepository $usersRepository
     */
    public function __construct(UsersRepository $usersRepository) {
        $this->usersRepository = $usersRepository;
    }

    /**
     * Dispatch /api/users endpoints
     * @param mixed $path
     * @param mixed $requestMethod
     * @return void
     */
    public function dispatch($path, $requestMethod): void {
        if ($path == '/api/users/authenticate' && $requestMethod == 'POST') {
            $this->loginWithCredentials();
        } else if ($path == '/api/users/signup' && $requestMethod == 'POST') {
            $this->createNewUser();
        } else if ($path == '/api/users/validate' && $requestMethod == 'GET') {
            $this->loginWithToken();
        } else {
            ResponseBuilder::outputResponse(new NotFoundResponse());
        }
    }

    /**
     * @openapi
     * path: /users/validate
     * method: GET
     * summary: Get user info, given user jwt token.
     * header: Authorization | string | optional | Bearer token format
     * response: 200 | User successfully retrieved
     * response: 401 | Invalid or absent token
     */
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

    /**
     * @openapi
     * path: /users/authenticate
     * method: POST
     * summary: Authenticate using user credentials.
     * body: username | string | required | The username of the user | JohnDoe
     * body: password | string | required | The password of the user | jabc123
     * response: 200 | User successfully authenticated
     * response: 400 | Bad request
     * response: 401 | Invalid credentials
     */
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

    /**
     * @openapi
     * path: /users/signup
     * method: POST
     * summary: Create new user credentials.
     * body: username | string | required | The username of the user | JohnDoe
     * body: name | string | required | The name of the user | John
     * body: surname | string | required | The surname of the user | Doe
     * body: email | string | optional | The email of the user | john.doe@gmail.com
     * body: password | string | required | The password of the user | john123
     * response: 201 | User successfully created
     * response: 400 | Bad request
     * response: 401 | Invalid credentials
     * response: 409 | Conflict: username already exists
     */
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
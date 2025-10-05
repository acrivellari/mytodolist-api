<?php

include_once __DIR__ . '/../ApiProject/src/Utils/ArrayUtils.php';
include_once __DIR__ . '/../ApiProject/src/Controllers/DTOs/ResponseBuilder.php';
include_once __DIR__ . '/../ApiProject/src/Controllers/DTOs/IResponse.php';
include_once __DIR__ . '/../ApiProject/src/Utils/MyValidationException.php';
include_once __DIR__ . '/../ApiProject/src/DataAccess/MySqlDbConnection.php';
include_once __DIR__ . '/../ApiProject/src/DataAccess/SqLiteDbConnection.php';
include_once __DIR__ . '/../ApiProject/src/Controllers/UserController.php';
include_once __DIR__ . '/../ApiProject/src/Utils/Jwt.php';
include_once __DIR__ . '/../ApiProject/src/Controllers/DTOs/Shared/InvalidCredentialsResponse.php';
include_once __DIR__ . '/../ApiProject/src/Controllers/DTOs/Shared/InvalidTokenResponse.php';
include_once __DIR__ . '/../ApiProject/src/Controllers/DTOs/Shared/MalformedJsonResponse.php';
include_once __DIR__ . '/../ApiProject/src/Controllers/DTOs/Shared/ValidationFailedResponse.php';
include_once __DIR__ . '/../ApiProject/src/Controllers/DTOs/Shared/NotFoundResponse.php';
include_once __DIR__ . '/../ApiProject/src/Controllers/DTOs/Shared/InternalServerErrorResponse.php';
include_once __DIR__ . '/../ApiProject/src/Controllers/DTOs/Shared/Conflict409Response.php';
include_once __DIR__ . '/../ApiProject/src/Controllers/DTOs/Shared/Created201Response.php';


JWT::setSecretKey('prova123');

$requestUri = $_SERVER['REQUEST_URI'];

$urlParts = explode('/', trim ($requestUri, '/'));

$foundEndpoint = false;

if ($urlParts[0] == 'api') {
    if ($urlParts[1] == 'users') {
        UserController::main($requestUri);
        $foundEndpoint = true;
    }
    else if ($urlParts[1] == 'phpinfo') {
        echo phpinfo();
        $foundEndpoint = true;
    }
} 

if ($foundEndpoint == false) {
    ResponseBuilder::outputResponse(new NotFoundResponse());
}
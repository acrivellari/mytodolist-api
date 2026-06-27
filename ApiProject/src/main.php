<?php

include_once __DIR__ . '/DataAccess/MySqlDbConnection.php';
include_once __DIR__ . '/DataAccess/SqLiteDbConnection.php';
include_once __DIR__ . '/Controllers/DTOs/ResponseBuilder.php';
include_once __DIR__ . '/Controllers/DTOs/IResponse.php';
include_once __DIR__ . '/Controllers/DTOs/Shared/InternalServerErrorResponse.php';
include_once __DIR__ . '/Controllers/Router.php';
include_once __DIR__ . '/Controllers/UserController.php';
include_once __DIR__ . '/Controllers/SwaggerController.php';
include_once __DIR__ . '/Utils/Jwt.php';
include_once __DIR__ . '/Controllers/DTOs/Shared/NotFoundResponse.php';
include_once __DIR__ . '/Controllers/DTOs/Shared/InvalidTokenResponse.php';
include_once __DIR__ . '/Utils/ArrayUtils.php';
include_once __DIR__ . '/Utils/MyValidationException.php';
include_once __DIR__ . '/Controllers/DTOs/Shared/InvalidCredentialsResponse.php';
include_once __DIR__ . '/Controllers/DTOs/Shared/MalformedJsonResponse.php';
include_once __DIR__ . '/Controllers/DTOs/Shared/ValidationFailedResponse.php';
include_once __DIR__ . '/Controllers/DTOs/Shared/Conflict409Response.php';
include_once __DIR__ . '/Controllers/DTOs/Shared/Created201Response.php';


$mysqlConnection = null;
$sqliteConnection = null;
try {
    JWT::setSecretKey();
    
    $mysqlConnection = MySqlDbConnection::createConnection();
    $sqliteConnection = SqLiteDbConnection::createConnection();

    $userRepo = new UsersRepository($mysqlConnection);

    $userController = new UserController($userRepo);
    $swaggerController = new SwaggerController();

    $router = new Router($userController, $swaggerController);
}
catch (\Throwable $e) {
    // Global exception handling
    ResponseBuilder::outputResponse(new InternalServerErrorResponse($e->getMessage()));
}
finally {
    if ($mysqlConnection !== null) {
        $mysqlConnection->close();
    }
    $sqliteConnection = null; 
}
<?php

class Router {
    private UserController $userController;
    private SwaggerController $swaggerController;

    // called once per request
    public function __construct(UserController $userController, SwaggerController $swaggerController) {
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestMethod =  $_SERVER['REQUEST_METHOD'];
        
        $path = parse_url($requestUri, PHP_URL_PATH);
        $urlParts = explode('/', trim ($path, '/'));

        $foundEndpoint = false;

        if ($urlParts[0] == 'api') {
            if ($urlParts[1] == 'users') {
                $userController->dispatch($path, $requestMethod);
                $foundEndpoint = true;
            }
            else if ($urlParts[1] == 'phpinfo') {
                echo phpinfo();
                $foundEndpoint = true;
            }
            else if ($urlParts[1] == 'swagger') {
                $swaggerController->dispatch($path, $requestMethod);
                $foundEndpoint = true;
            }
        } 

        if ($foundEndpoint == false) {
            ResponseBuilder::outputResponse(new NotFoundResponse());
        }
    }
}
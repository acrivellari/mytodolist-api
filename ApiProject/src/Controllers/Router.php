<?php

class Router {
    private UserController $userController;

    // called once per request
    public function __construct(UserController $userController) {
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestMethod =  $_SERVER['REQUEST_METHOD'];

        $urlParts = explode('/', trim ($requestUri, '/'));

        $foundEndpoint = false;

        if ($urlParts[0] == 'api') {
            if ($urlParts[1] == 'users') {
                $userController->dispatch($requestUri, $requestMethod);
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
    }
}
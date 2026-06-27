<?php

/**
 * Controller for swagger endpoints
 */
class SwaggerController {
    
    /**
     * Dispatch /api/swagger endpoints
     * @param string $path
     * @param string $requestMethod
     * @return void
     */
    public function dispatch(string $path, string $requestMethod) {
        if ($path == '/api/swagger' && $requestMethod == 'GET') {
            $this->getSwagger();
        } else {
            ResponseBuilder::outputResponse(new NotFoundResponse());
        }
    }

    /**
     * @openapi
     * path: /swagger
     * method: GET
     * summary: Retrieve OpenApi specifications
     * query: spec | boolean | optional | If true show the json file of openapi specs
     * response: 201 | User successfully created
     */
    public function getSwagger() {
        if (isset($_GET['spec']) && $_GET['spec'] == 'true') {
            $this->renderJsonSpec();
        } else {
            $this->renderSwaggerUI();
        }
    }

    private function renderSwaggerUI() {
        header('Content-Type: text/html; charset=utf-8');
        
        // Define the path to your HTML view file
        $templatePath = dirname(__DIR__, 1) . '/Views/swagger-view.html';

        if (file_exists($templatePath)) {
            require $templatePath;
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Swagger UI template file missing.";
            echo $templatePath;
        }
        exit;
    }

    private function renderJsonSpec() {
        header('Content-Type: application/json; charset=utf-8');

        $controllers = ['UserController', 'SwaggerController'];

        $spec = [
            "openapi" => "3.0.0",
            "info" => [
                "title" => "Todolist APIs docs",
                "version" => "1.0.0"
            ],
            "servers" => [["url" => "http://" . $_SERVER['HTTP_HOST'] . "/api"]],
            "paths" => $this->fetchOpenApiSpecsFromControllers($controllers),
            
        ];
        echo json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
    /**
     * Scans reflection metadata across classes and returns a compiled OpenAPI paths array.
     */
    private function fetchOpenApiSpecsFromControllers(array $controllers): array {
        $pathsTree = [];

        foreach ($controllers as $controllerClass) {
            if (!class_exists($controllerClass)) {
                continue;
            }

            $reflectionClass = new ReflectionClass($controllerClass);
            foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $docComment = $method->getDocComment();
                if (!$docComment || strpos($docComment, '@openapi') === false) {
                    continue;
                }

                // Initial clear configuration block state trackers for this current routing method
                $path = null;
                $httpMethod = null;
                $summary = "No summary provided";
                $parameters = [];
                $bodyProperties = [];
                $requiredBodyFields = [];
                $responses = [];

                $lines = explode("\n", $docComment);
                foreach ($lines as $line) {
                    $cleanLine = trim($line, "/* \t\r\n");
                    if ($cleanLine === '@openapi' || strpos($cleanLine, ':') === false) {
                        continue;
                    }

                    list($key, $value) = explode(':', $cleanLine, 2);
                    $key = trim($key);
                    $value = trim($value);

                    if ($key === 'path')    $path = $value;
                    if ($key === 'method')  $httpMethod = strtolower($value);
                    if ($key === 'summary') $summary = $value;

                    // 1. HEADERS
                    if ($key === 'header') {
                        $parts = array_map('trim', explode('|', $value));
                        if (count($parts) >= 2) {
                            $parameters[] = [
                                "name" => $parts[0],
                                "in" => "header",
                                "required" => ($parts[2] ?? 'optional') === 'required',
                                "description" => $parts[3] ?? '',
                                "schema" => ["type" => $parts[1]]
                            ];
                        }
                    }

                    // 2. QUERY PARAMETERS
                    if ($key === 'query') {
                        $parts = array_map('trim', explode('|', $value));
                        if (count($parts) >= 2) {
                            $parameters[] = [
                                "name" => $parts[0],
                                "in" => "query",
                                "required" => ($parts[2] ?? 'optional') === 'required',
                                "description" => $parts[3] ?? '',
                                "schema" => ["type" => $parts[1]]
                            ];
                        }
                    }

                    // 3. JSON REQUEST BODY PAYLOAD
                    if ($key === 'body') {
                        $parts = array_map('trim', explode('|', $value));
                        if (count($parts) >= 2) {
                            $fieldName = $parts[0];
                            if (isset($parts[2]) && $parts[2] === 'required') {
                                $requiredBodyFields[] = $fieldName;
                            }

                            $bodyProperties[$fieldName] = [
                                "type" => $parts[1],
                                "description" => $parts[3] ?? $parts[2] ?? '',
                                "example" => $parts[4] ?? ''
                            ];
                        }
                    }

                    // 4. ROUTE OUTCOME RESPONSES
                    if ($key === 'response') {
                        $parts = array_map('trim', explode('|', $value));
                        if (count($parts) >= 2) {
                            $responses[$parts[0]] = ["description" => $parts[1]];
                        }
                    }
                }

                // If path markers matched up, package configurations neatly onto the matrix tree
                if ($path && $httpMethod) {
                    if (!isset($pathsTree[$path])) {
                        $pathsTree[$path] = [];
                    }

                    $pathsTree[$path][$httpMethod] = [
                        "summary" => $summary,
                        "responses" => $responses,
                        "tags" => [$controllerClass]
                    ];

                    if (!empty($parameters)) {
                        $pathsTree[$path][$httpMethod]['parameters'] = $parameters;
                    }

                    if (!empty($bodyProperties)) {
                        $schemaDefinition = ["type" => "object", "properties" => $bodyProperties];
                        if (!empty($requiredBodyFields)) {
                            $schemaDefinition["required"] = $requiredBodyFields;
                        }
                        $pathsTree[$path][$httpMethod]['requestBody'] = [
                            "required" => true,
                            "content" => ["application/json" => ["schema" => $schemaDefinition]]
                        ];
                    }
                }
            }
        }

        return $pathsTree;
    }
}
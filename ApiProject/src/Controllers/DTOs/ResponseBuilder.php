<?php

/**
 * Contains static methods to standardize API responses (output, headers and status codes)
 */
final class ResponseBuilder {
    
    /**
     * Sets the response headers, the http status code and the output as json, all defined in the $response
     * @param IResponse $response
     * @return void
     */
    public static function outputResponse(IResponse $response): void {
        foreach ($response->getHeaders() as $header) {
            header($header); 
        }
        http_response_code($response->getHttpCode());
        echo json_encode($response);
    }
}
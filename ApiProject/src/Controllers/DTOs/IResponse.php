<?php
/**
 * Abstract class that every json response have to extend (handles status codes and headers)
 */
abstract class IResponse implements \JsonSerializable {
    /**
     * @var array<string, string> An associative array that holds key-values for all HTTP headers.
     */
    private array $headers = ['Content-Type'=>'application/json; charset=utf-8'];

    /**
     * Abstract method: forces all child classes to define their response HTTP status code
     */
    abstract public function getHttpCode() : int;

    /**
     * Concrete method accessible only to child classes: allows them to add a header
     * @param string $key
     * @param string $value
     * @return void
     */
    protected function addHeader(string $key, string $value): void {
        $this->headers[$key] = $value;
    }
    
    /**
     * Public concrete method: retrieve an array of strings (key: value), ready for the function header()
     * @return string[] list of strings
     */
    public function getHeaders(): array {
        $result = [];
        foreach ($this->headers as $x=>$y) {
            $result[] = "$x: $y";
        }
        return $result;
    }
}
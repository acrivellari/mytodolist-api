<?php

/**
 * Contains various helpers methods, for arrays
 */
class ArrayUtils {
    /**
     * Helper method, to check if a key-value is a valid string
     * @param array $array
     * @param mixed $key
     * @return bool True, if valid
     */
    public static function checkIfValueIsString(array $array, $key) {
        return isset($array[$key]) && is_string($array[$key]);
    }

    /**
     * Helper method, to check if a key-value is a valid integer
     * @param array $array
     * @param mixed $key
     * @return bool True, if valid
     */
    public static function checkIfValueIsInt(array $array, $key) {
        return isset($array[$key]) && is_int($array[$key]);
    }
}
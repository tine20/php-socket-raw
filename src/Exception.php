<?php

namespace Socket\Raw;

use RuntimeException;

class Exception extends RuntimeException
{
    /**
     * Create an Exception after a socket operation on the given $resource failed
     *
     * @param resource $resource
     * @param string   $messagePrefix
     * @return self
     * @uses socket_last_error() to get last socket error code
     * @uses socket_clear_error() to clear socket error code
     * @uses self::createFromCode() to automatically construct exception with full error message
     */
    public static function createFromSocketResource($resource, $messagePrefix = 'Socket operation failed')
    {
        $code = socket_last_error($resource);
        socket_clear_error($resource);

        return self::createFromCode($code, $messagePrefix);
    }

    /**
     * Create an Exception after a global socket operation failed (like socket creation)
     *
     * @param string $messagePrefix
     * @return self
     * @uses socket_last_error() to get last global error code
     * @uses socket_clear_error() to clear global error code
     * @uses self::createFromCode() to automatically construct exception with full error message
     */
    public static function createFromGlobalSocketOperation($messagePrefix = 'Socket operation failed')
    {
        $code = socket_last_error();
        socket_clear_error();

        return self::createFromCode($code, $messagePrefix);
    }

    /**
     * Create an Exception for given error $code
     *
     * @param int    $code
     * @param string $messagePrefix
     * @return self
     * @throws Exception if given $val is boolean false
     * @uses self::getErrorMessage() to translate error code to error message
     */
    public static function createFromCode($code, $messagePrefix = 'Socket error')
    {
        return new self($messagePrefix . ': ' . self::getErrorMessage($code), $code);
    }

    /**
     * get error message for given error code
     *
     * @param int $code error code
     * @return string
     * @uses socket_strerror() to translate error code to error message
     * @uses get_defined_constants() to check for related error constant
     */
    protected static function getErrorMessage($code)
    {
        if (null === $code || false === $code) {
            return 'not a valid resource';
        }
        
        $string = socket_strerror($code);

        // search constant starting with SOCKET_ for this error code
        foreach (get_defined_constants() as $key => $value) {
            if($value === $code && strpos($key, 'SOCKET_') === 0) {
                $string .= ' (' . $key . ')';
                break;
            }
        }

        return $string;
    }
}

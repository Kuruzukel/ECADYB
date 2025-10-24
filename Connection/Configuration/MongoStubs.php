<?php
/**
 * Stubs for MongoDB PHP Extension (ext-mongodb)
 * This file provides IDE autocompletion for MongoDB extension classes
 * @see https://www.php.net/manual/en/book.mongodb.php
 */

// BSON Type Stubs
namespace MongoDB\BSON {
    
    class UTCDateTime
    {
        public function __construct(int $milliseconds = 0) {}
    }

    class ObjectId
    {
        public function __construct(string $id = '') {}
    }
}

// MongoDB Driver Exception Stubs
namespace MongoDB\Driver\Exception {

    /**
     * Base exception interface for all driver exceptions
     */
    interface Exception extends \Throwable
    {
    }

    /**
     * Base class for all runtime exceptions thrown by the extension
     */
    class RuntimeException extends \RuntimeException implements Exception
    {
    }

    /**
     * Base class for exceptions thrown by the server
     */
    class ServerException extends RuntimeException
    {
    }

    /**
     * Thrown when a query or command fails to complete within a specified time limit
     */
    class ExecutionTimeoutException extends ServerException
    {
    }

    /**
     * Base class for exceptions thrown when the driver fails to establish a database connection
     */
    class ConnectionException extends RuntimeException
    {
    }

    /**
     * Thrown when a command fails
     */
    class CommandException extends ServerException
    {
    }

    /**
     * Thrown when the driver is incorrectly used
     */
    class InvalidArgumentException extends \InvalidArgumentException implements Exception
    {
    }

    /**
     * Thrown when the driver encounters a runtime error
     */
    class UnexpectedValueException extends \UnexpectedValueException implements Exception
    {
    }

    /**
     * Thrown when a write operation fails
     */
    class WriteException extends ServerException
    {
    }

    /**
     * Thrown when a bulk write operation fails
     */
    class BulkWriteException extends WriteException
    {
    }

    /**
     * Thrown when the driver fails to authenticate with the server
     */
    class AuthenticationException extends ConnectionException
    {
    }

    /**
     * Thrown when the driver fails to establish an SSL connection
     */
    class SSLConnectionException extends ConnectionException
    {
    }
}
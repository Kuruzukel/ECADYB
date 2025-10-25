<?php

/**
 * Stubs for MongoDB PHP Extension (ext-mongodb) and PHP Built-in Functions
 * This file provides IDE autocompletion for MongoDB extension classes and PHP functions
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
    interface Exception extends \Throwable {}

    /**
     * Base class for all runtime exceptions thrown by the extension
     */
    class RuntimeException extends \RuntimeException implements Exception {}

    /**
     * Base class for exceptions thrown by the server
     */
    class ServerException extends RuntimeException {}

    /**
     * Thrown when a query or command fails to complete within a specified time limit
     */
    class ExecutionTimeoutException extends ServerException {}

    /**
     * Base class for exceptions thrown when the driver fails to establish a database connection
     */
    class ConnectionException extends RuntimeException {}

    /**
     * Thrown when a command fails
     */
    class CommandException extends ServerException {}

    /**
     * Thrown when the driver is incorrectly used
     */
    class InvalidArgumentException extends \InvalidArgumentException implements Exception {}

    /**
     * Thrown when the driver encounters a runtime error
     */
    class UnexpectedValueException extends \UnexpectedValueException implements Exception {}

    /**
     * Thrown when a write operation fails
     */
    class WriteException extends ServerException {}

    /**
     * Thrown when a bulk write operation fails
     */
    class BulkWriteException extends WriteException {}

    /**
     * Thrown when the driver fails to authenticate with the server
     */
    class AuthenticationException extends ConnectionException {}

    /**
     * Thrown when the driver fails to establish an SSL connection
     */
    class SSLConnectionException extends ConnectionException {}
}

// MongoDB Library Stubs
namespace MongoDB {

    /**
     * MongoDB Client
     */
    class Client
    {
        public function __construct(string $uri = 'mongodb://localhost:27017', array $uriOptions = [], array $driverOptions = []) {}

        public function __get(string $databaseName): Database
        {
            return new Database();
        }

        public function selectDatabase(string $databaseName, array $options = []): Database
        {
            return new Database();
        }
    }

    /**
     * MongoDB Database
     */
    class Database
    {
        public function __construct($manager = null, $databaseName = '') {}

        public function __get(string $collectionName): Collection
        {
            return new Collection(null, '', '');
        }

        public function selectCollection(string $collectionName, array $options = []): Collection
        {
            return new Collection(null, '', '');
        }
    }

    /**
     * MongoDB Collection
     */
    class Collection
    {
        public function __construct($manager = null, $databaseName = '', $collectionName = '') {}

        public function findOne($filter = [], array $options = []): ?array
        {
            return null;
        }

        public function find($filter = [], array $options = []): \MongoDB\Driver\Cursor
        {
            return new \MongoDB\Driver\Cursor(null, null);
        }

        public function insertOne($document, array $options = []): \MongoDB\InsertOneResult
        {
            return new \MongoDB\InsertOneResult(null);
        }

        public function insertMany(array $documents, array $options = []): \MongoDB\InsertManyResult
        {
            return new \MongoDB\InsertManyResult(null);
        }

        public function updateOne($filter, $update, array $options = []): \MongoDB\UpdateResult
        {
            return new \MongoDB\UpdateResult(null);
        }

        public function updateMany($filter, $update, array $options = []): \MongoDB\UpdateResult
        {
            return new \MongoDB\UpdateResult(null);
        }

        public function deleteOne($filter, array $options = []): \MongoDB\DeleteResult
        {
            return new \MongoDB\DeleteResult(null);
        }

        public function deleteMany($filter, array $options = []): \MongoDB\DeleteResult
        {
            return new \MongoDB\DeleteResult(null);
        }
    }

    /**
     * MongoDB Insert One Result
     */
    class InsertOneResult
    {
        public function __construct($writeResult) {}
        public function getInsertedId() {}
        public function getInsertedCount(): int
        {
            return 0;
        }
    }

    /**
     * MongoDB Insert Many Result
     */
    class InsertManyResult
    {
        public function __construct($writeResult) {}
        public function getInsertedIds(): array
        {
            return [];
        }
        public function getInsertedCount(): int
        {
            return 0;
        }
    }

    /**
     * MongoDB Update Result
     */
    class UpdateResult
    {
        public function __construct($writeResult) {}
        public function getMatchedCount(): int
        {
            return 0;
        }
        public function getModifiedCount(): int
        {
            return 0;
        }
        public function getUpsertedCount(): int
        {
            return 0;
        }
        public function getUpsertedId() {}
    }

    /**
     * MongoDB Delete Result
     */
    class DeleteResult
    {
        public function __construct($writeResult) {}
        public function getDeletedCount(): int
        {
            return 0;
        }
    }
}

// MongoDB Driver Stubs
namespace MongoDB\Driver {

    /**
     * MongoDB Cursor
     */
    class Cursor implements \Iterator
    {
        public function __construct($server, $cursorId) {}
        public function current(): mixed
        {
            return null;
        }
        public function key(): mixed
        {
            return null;
        }
        public function next(): void {}
        public function rewind(): void {}
        public function valid(): bool
        {
            return false;
        }
        public function toArray(): array
        {
            return [];
        }
    }
}

// PHP Built-in Function Stubs (Global Namespace)
namespace {
    if (!function_exists('random_int')) {
        /**
         * Generates cryptographically secure pseudo-random integers
         * @param int $min The lowest value to be returned
         * @param int $max The highest value to be returned
         * @return int Returns a cryptographically secure random integer
         * @throws Exception If an appropriate source of randomness cannot be found
         */
        function random_int(int $min, int $max): int
        {
            return 0;
        }
    }

    if (!function_exists('mt_rand')) {
        /**
         * Generate a random integer using the Mersenne Twister algorithm
         * @param int $min Optional lowest value to be returned (default: 0)
         * @param int $max Optional highest value to be returned (default: mt_getrandmax())
         * @return int A random integer value between min and max
         */
        function mt_rand(int $min = 0, int $max = 0): int
        {
            return 0;
        }
    }

    if (!function_exists('str_pad')) {
        /**
         * Pad a string to a certain length with another string
         * @param string $string The input string
         * @param int $length The desired length after padding
         * @param string $pad_string The string to use for padding
         * @param int $pad_type Can be STR_PAD_RIGHT, STR_PAD_LEFT, or STR_PAD_BOTH
         * @return string Returns the padded string
         */
        function str_pad(string $string, int $length, string $pad_string = ' ', int $pad_type = STR_PAD_RIGHT): string
        {
            return '';
        }
    }
}

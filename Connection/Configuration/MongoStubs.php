<?php

/**
 * Stubs for MongoDB PHP Extension (ext-mongodb) and PHP Built-in Functions
 * This file provides IDE autocompletion for MongoDB extension classes and PHP functions
 * @see https://www.php.net/manual/en/book.mongodb.php
 */

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

/**
 * PSR-6 Cache Interface Stubs
 * For Firebase JWT CachedKeySet
 */

namespace Psr\Cache {

    /**
     * CacheItemPoolInterface generates CacheItemInterface objects.
     */
    interface CacheItemPoolInterface
    {
        /**
         * Returns a Cache Item representing the specified key.
         */
        public function getItem(string $key): CacheItemInterface;

        /**
         * Returns a traversable set of cache items.
         */
        public function getItems(array $keys = []): iterable;

        /**
         * Confirms if the cache contains specified cache item.
         */
        public function hasItem(string $key): bool;

        /**
         * Deletes all items in the pool.
         */
        public function clear(): bool;

        /**
         * Removes the item from the pool.
         */
        public function deleteItem(string $key): bool;

        /**
         * Removes multiple items from the pool.
         */
        public function deleteItems(array $keys): bool;

        /**
         * Persists a cache item immediately.
         */
        public function save(CacheItemInterface $item): bool;

        /**
         * Sets a cache item to be persisted later.
         */
        public function saveDeferred(CacheItemInterface $item): bool;

        /**
         * Persists any deferred cache items.
         */
        public function commit(): bool;
    }

    /**
     * CacheItemInterface defines an interface for interacting with objects inside a cache.
     */
    interface CacheItemInterface
    {
        /**
         * Returns the key for the current cache item.
         */
        public function getKey(): string;

        /**
         * Retrieves the value of the item from the cache associated with this object's key.
         */
        public function get(): mixed;

        /**
         * Confirms if the cache item lookup resulted in a cache hit.
         */
        public function isHit(): bool;

        /**
         * Sets the value represented by this cache item.
         */
        public function set(mixed $value): static;

        /**
         * Sets the expiration time for this cache item.
         */
        public function expiresAt(?\DateTimeInterface $expiration): static;

        /**
         * Sets the expiration time for this cache item.
         */
        public function expiresAfter(int|\DateInterval|null $time): static;
    }
}

/**
 * PSR-18 HTTP Client Interface Stubs
 * For Firebase JWT CachedKeySet
 */

namespace Psr\Http\Client {

    use Psr\Http\Message\RequestInterface;
    use Psr\Http\Message\ResponseInterface;

    /**
     * Client Interface
     */
    interface ClientInterface
    {
        /**
         * Sends a PSR-7 request and returns a PSR-7 response.
         */
        public function sendRequest(RequestInterface $request): ResponseInterface;
    }
}

/**
 * PSR-17 HTTP Factory Interface Stubs
 * For Firebase JWT CachedKeySet
 */

namespace Psr\Http\Message {

    /**
     * Request Factory Interface
     */
    interface RequestFactoryInterface
    {
        /**
         * Create a new request.
         */
        public function createRequest(string $method, $uri): RequestInterface;
    }

    /**
     * Request Interface
     */
    interface RequestInterface extends MessageInterface
    {
        /**
         * Retrieves the message's request target.
         */
        public function getRequestTarget(): string;

        /**
         * Return an instance with the specific request-target.
         */
        public function withRequestTarget(string $requestTarget): static;

        /**
         * Retrieves the HTTP method of the request.
         */
        public function getMethod(): string;

        /**
         * Return an instance with the provided HTTP method.
         */
        public function withMethod(string $method): static;

        /**
         * Retrieves the URI instance.
         */
        public function getUri(): UriInterface;

        /**
         * Returns an instance with the provided URI.
         */
        public function withUri(UriInterface $uri, bool $preserveHost = false): static;
    }

    /**
     * Response Interface
     */
    interface ResponseInterface extends MessageInterface
    {
        /**
         * Gets the response status code.
         */
        public function getStatusCode(): int;

        /**
         * Return an instance with the specified status code and, optionally, reason phrase.
         */
        public function withStatus(int $code, string $reasonPhrase = ''): static;

        /**
         * Gets the response reason phrase associated with the status code.
         */
        public function getReasonPhrase(): string;
    }

    /**
     * Message Interface
     */
    interface MessageInterface
    {
        /**
         * Retrieves the HTTP protocol version as a string.
         */
        public function getProtocolVersion(): string;

        /**
         * Return an instance with the specified HTTP protocol version.
         */
        public function withProtocolVersion(string $version): static;

        /**
         * Retrieves all message header values.
         */
        public function getHeaders(): array;

        /**
         * Checks if a header exists by the given case-insensitive name.
         */
        public function hasHeader(string $name): bool;

        /**
         * Retrieves a message header value by the given case-insensitive name.
         */
        public function getHeader(string $name): array;

        /**
         * Retrieves a comma-separated string of the values for a single header.
         */
        public function getHeaderLine(string $name): string;

        /**
         * Return an instance with the provided value replacing the specified header.
         */
        public function withHeader(string $name, $value): static;

        /**
         * Return an instance with the specified header appended with the given value.
         */
        public function withAddedHeader(string $name, $value): static;

        /**
         * Return an instance without the specified header.
         */
        public function withoutHeader(string $name): static;

        /**
         * Gets the body of the message.
         */
        public function getBody(): StreamInterface;

        /**
         * Return an instance with the specified message body.
         */
        public function withBody(StreamInterface $body): static;
    }

    /**
     * Stream Interface
     */
    interface StreamInterface
    {
        /**
         * Reads all data from the stream into a string, from the beginning to end.
         */
        public function __toString(): string;

        /**
         * Closes the stream and any underlying resources.
         */
        public function close(): void;

        /**
         * Separates any underlying resources from the stream.
         */
        public function detach();

        /**
         * Get the size of the stream if known.
         */
        public function getSize(): ?int;

        /**
         * Returns the current position of the file read/write pointer
         */
        public function tell(): int;

        /**
         * Returns true if the stream is at the end of the stream.
         */
        public function eof(): bool;

        /**
         * Returns whether or not the stream is seekable.
         */
        public function isSeekable(): bool;

        /**
         * Seek to a position in the stream.
         */
        public function seek(int $offset, int $whence = SEEK_SET): void;

        /**
         * Seek to the beginning of the stream.
         */
        public function rewind(): void;

        /**
         * Returns whether or not the stream is writable.
         */
        public function isWritable(): bool;

        /**
         * Write data to the stream.
         */
        public function write(string $string): int;

        /**
         * Returns whether or not the stream is readable.
         */
        public function isReadable(): bool;

        /**
         * Read data from the stream.
         */
        public function read(int $length): string;

        /**
         * Returns the remaining contents in a string
         */
        public function getContents(): string;

        /**
         * Get stream metadata as an associative array or retrieve a specific key.
         */
        public function getMetadata(?string $key = null);
    }

    /**
     * URI Interface
     */
    interface UriInterface
    {
        /**
         * Retrieve the scheme component of the URI.
         */
        public function getScheme(): string;

        /**
         * Retrieve the authority component of the URI.
         */
        public function getAuthority(): string;

        /**
         * Retrieve the user information component of the URI.
         */
        public function getUserInfo(): string;

        /**
         * Retrieve the host component of the URI.
         */
        public function getHost(): string;

        /**
         * Retrieve the port component of the URI.
         */
        public function getPort(): ?int;

        /**
         * Retrieve the path component of the URI.
         */
        public function getPath(): string;

        /**
         * Retrieve the query string of the URI.
         */
        public function getQuery(): string;

        /**
         * Retrieve the fragment component of the URI.
         */
        public function getFragment(): string;

        /**
         * Return an instance with the specified scheme.
         */
        public function withScheme(string $scheme): static;

        /**
         * Return an instance with the specified user information.
         */
        public function withUserInfo(string $user, ?string $password = null): static;

        /**
         * Return an instance with the specified host.
         */
        public function withHost(string $host): static;

        /**
         * Return an instance with the specified port.
         */
        public function withPort(?int $port): static;

        /**
         * Return an instance with the specified path.
         */
        public function withPath(string $path): static;

        /**
         * Return an instance with the specified query string.
         */
        public function withQuery(string $query): static;

        /**
         * Return an instance with the specified URI fragment.
         */
        public function withFragment(string $fragment): static;

        /**
         * Return the string representation as a URI reference.
         */
        public function __toString(): string;
    }
}

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

    if (!function_exists('rand')) {
        /**
         * Generate a random integer
         * @param int $min Optional lowest value to be returned (default: 0)
         * @param int $max Optional highest value to be returned (default: getrandmax())
         * @return int A random integer value between min and max
         */
        function rand(int $min = 0, int $max = 0): int
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

    if (!function_exists('random_bytes')) {
        /**
         * Generates cryptographically secure pseudo-random bytes
         * @param int $length The length of the random string that should be returned in bytes
         * @return string Returns a string containing the requested number of cryptographically secure random bytes
         * @throws Exception If an appropriate source of randomness cannot be found
         */
        function random_bytes(int $length): string
        {
            return '';
        }
    }
}

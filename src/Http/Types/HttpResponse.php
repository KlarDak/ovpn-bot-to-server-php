<?php

namespace CNS\BotToServer\Http\Types;

/**
 * Represents an HTTP response received from a remote server.
 *
 * Provides access to the response status code, headers,
 * raw body, decoded JSON data, and status information.
 */
class HttpResponse
{
    /**
     * Initializes a new HTTP response instance.
     *
     * @param int $statusCode HTTP response status code.
     * @param array $headers Response headers.
     * @param string $body Raw response body.
     */
    function __construct(
        private int $statusCode,
        private array $headers,
        private string $body
    ) {}

    /**
     * Returns the HTTP response status code.
     *
     * @return int HTTP status code.
     */
    public function getStatusCode(): int {
        return $this->statusCode;
    }

    /**
     * Returns all HTTP response headers.
     *
     * @return array Response headers.
     */
    public function getHeaders(): array {
        return $this->headers;
    }

    /**
     * Returns the raw HTTP response body.
     *
     * @return string Response body.
     */
    public function getBody(): string {
        return $this->body;
    }

    /**
     * Decodes the HTTP response body from JSON into an associative array.
     *
     * @return array Decoded JSON response data.
     */
    public function getJsonBody(): array {
        return json_decode($this->body, true);
    }

    /**
     * Determines whether the HTTP request was successful.
     *
     * A response is considered successful when its status code
     * is between 200 and 299.
     *
     * @return bool True if the response is successful, false otherwise.
     */
    public function isSuccessful(): bool {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Retrieves the value of a specific response header.
     *
     * @param string $name Header name.
     *
     * @return string|null Header value, or an empty string if not found.
     */
    public function getHeader(string $name): ?string {
        return $this->headers[$name] ?? '';
    }

    /**
     * Returns a response header as a comma-separated string.
     *
     * @param string $name Header name.
     *
     * @return string Formatted header value.
     */
    public function getHeaderLine(string $name) {
        return implode(', ', (array) $this->getHeader($name));
    }

    /**
     * Checks whether a response header exists.
     *
     * @param string $name Header name.
     *
     * @return bool True if the header exists, false otherwise.
     */
    public function hasHeader(string $name): bool {
        return isset($this->headers[$name]);
    }
}
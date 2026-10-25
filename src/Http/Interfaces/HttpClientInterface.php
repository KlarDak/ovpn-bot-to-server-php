<?php

namespace CNS\BotToServer\Http\Interfaces;

use CNS\BotToServer\Http\Types\HttpResponse;

/**
 * Defines the interface for HTTP clients used by the BTS library.
 *
 * Allows custom HTTP client implementations to communicate
 * with remote server APIs.
 */
interface HttpClientInterface
{
    /**
     * Sends an HTTP request to the specified endpoint.
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE, etc.).
     * @param string $endpoint Target URL or API endpoint.
     * @param array $options Additional request options.
     * @param array $headers HTTP headers to include in the request.
     *
     * @return HttpResponse The HTTP response.
     */
    public function request(
        string $method,
        string $endpoint,
        array $options = [],
        array $headers = []
    ): HttpResponse;

    /**
     * Sends an HTTP request and retrieves the response as an array.
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE, etc.).
     * @param string $endpoint Target URL or API endpoint.
     * @param array $options Additional request options.
     * @param array $headers HTTP headers to include in the request.
     *
     * @return array The response data.
     */
    public function stream(
        string $method,
        string $endpoint,
        array $options = [],
        array $headers = []
    ): array;
}
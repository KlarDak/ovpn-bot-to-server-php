<?php

namespace CNS\BotToServer\Http\Types;

class HttpResponse {
    function __construct(private int $statusCode, private array $headers, private string $body) {}

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function getBody() : string {
        return $this->body;
    }

    public function getJsonBody(): array {
        return json_decode($this->body, true);
    }

    public function isSuccessful(): bool {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function getHeader(string $name): ?string {
        return $this->headers[$name] ?? '';
    }
    
    public function getHeaderLine(string $name) {
        return implode(', ', (array) $this->getHeader($name));
    }

    public function hasHeader(string $name): bool {
        return isset($this->headers[$name]);
    }
}
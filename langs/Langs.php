<?php

declare(strict_types=1);

namespace CNS\OvpnBotToServer\Langs;

use JsonException;
use RuntimeException;

class Langs
{
    private string $path;
    private string $language;
    private array $dict = [];

    public function __construct(
        string $language = 'en',
        ?string $path = null
    ) {
        $this->path = rtrim(
            $path ?? __DIR__ . '/packs',
            '/\\'
        ) . DIRECTORY_SEPARATOR;

        if (!$this->setLanguage($language)) {
            throw new RuntimeException(
                "Language pack '{$language}' not found."
            );
        }
    }

    public function setLanguage(string $enc): bool
    {
        // Prevent directory traversal
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $enc)) {
            return false;
        }

        $file = $this->path . $enc . '.json';

        if (!is_file($file) || !is_readable($file)) {
            return false;
        }

        $contents = file_get_contents($file);

        if ($contents === false) {
            throw new RuntimeException(
                "Unable to read language pack '{$enc}'."
            );
        }

        try {
            $data = json_decode(
                $contents,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new RuntimeException(
                "Invalid JSON in language pack '{$enc}'.",
                previous: $e
            );
        }

        if (!is_array($data) || array_is_list($data)) {
            throw new RuntimeException(
                "Language pack '{$enc}' must contain a JSON object."
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw new RuntimeException(
                    "Invalid translation value for key '{$key}'."
                );
            }
        }

        $this->dict = $data;
        $this->language = $enc;

        return true;
    }

    public function setDirectory(string $dir): bool
    {
        if (!is_dir($dir) || !is_readable($dir)) {
            return false;
        }

        $this->path = rtrim($dir, '/\\')
            . DIRECTORY_SEPARATOR;

        return true;
    }

    public function get(
        string $ident,
        array $replacements = []
    ): string {
        $translation = $this->dict[$ident] ?? '';

        return strtr($translation, $replacements);
    }

    public function has(string $ident): bool
    {
        return array_key_exists($ident, $this->dict);
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getDictionary(): array
    {
        return $this->dict;
    }
}
<?php

namespace CNS\BotToServer\Types;

final class Env {
    public static function getString(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? getenv($key);

        return $value === false ? $default : (string) $value;
    }

    public static function getInt(string $key, int $default = 0): int
    {
        $value = self::getString($key);

        if ($value === '') {
            return $default;
        }

        $result = filter_var($value, FILTER_VALIDATE_INT);

        if ($result === false) {
            throw new \UnexpectedValueException(
                "Environment variable '$key' must be an integer."
            );
        }

        return $result;
    }

    public static function getFloat(string $key, float $default = 0.0): float
    {
        $value = self::getString($key);

        if ($value === '') {
            return $default;
        }

        if (!is_numeric($value)) {
            throw new \UnexpectedValueException(
                "Environment variable '$key' must be numeric."
            );
        }

        return (float) $value;
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::getString($key);

        if ($value === '') {
            return $default;
        }

        $result = filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );

        if ($result === null) {
            throw new \UnexpectedValueException(
                "Environment variable '$key' must be boolean."
            );
        }

        return $result;
    }

    public static function getSubsUrls(): array
    {
        $result = [];

        foreach (array_merge(getenv() ?: [], $_ENV) as $key => $value) {
            if (preg_match('/^SUB_(.+)_URL$/', $key, $matches)) {
                $result[$matches[1]] = (string) $value;
            }
        }

        return $result;
    }
}
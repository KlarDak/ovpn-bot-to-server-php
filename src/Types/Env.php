<?php

namespace CNS\BotToServer\Types;

/**
 * Provides typed access to environment variables.
 *
 * Reads values from $_ENV or the process environment
 * and converts them to the requested PHP type.
 *
 * Supports string, integer, float, and boolean values.
 */
final class Env
{
    /**
     * Retrieves an environment variable as a string.
     *
     * Checks $_ENV first, then falls back to getenv().
     *
     * @param string $key Environment variable name.
     * @param string $default Default value if the variable is unavailable.
     *
     * @return string Environment variable value.
     */
    public static function getString(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? getenv($key);

        return $value === false ? $default : (string) $value;
    }

    /**
     * Retrieves an environment variable as an integer.
     *
     * Returns the default value if the variable is empty
     * or unavailable.
     *
     * @param string $key Environment variable name.
     * @param int $default Default integer value.
     *
     * @return int Parsed integer value.
     *
     * @throws \UnexpectedValueException If the value is not a valid integer.
     */
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

    /**
     * Retrieves an environment variable as a floating-point number.
     *
     * Returns the default value if the variable is empty
     * or unavailable.
     *
     * @param string $key Environment variable name.
     * @param float $default Default floating-point value.
     *
     * @return float Parsed floating-point value.
     *
     * @throws \UnexpectedValueException If the value is not numeric.
     */
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

    /**
     * Retrieves an environment variable as a boolean.
     *
     * Accepts boolean representations recognized by PHP,
     * including true, false, yes, no, on, off, 1, and 0.
     *
     * Returns the default value if the variable is empty
     * or unavailable.
     *
     * @param string $key Environment variable name.
     * @param bool $default Default boolean value.
     *
     * @return bool Parsed boolean value.
     *
     * @throws \UnexpectedValueException If the value cannot be interpreted as boolean.
     */
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
}
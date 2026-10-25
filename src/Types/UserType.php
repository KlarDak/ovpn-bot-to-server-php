<?php

namespace CNS\BotToServer\Types;

/**
 * Defines the supported user identifier types.
 *
 * Each enum value corresponds to a database column
 * used to identify and retrieve users.
 */
enum UserType: string
{
    /** Identifies a user by their Telegram ID. */
    case TELEGRAM = 'telegram_id';

    /** Identifies a user by their email address. */
    case EMAIL = 'email';
}
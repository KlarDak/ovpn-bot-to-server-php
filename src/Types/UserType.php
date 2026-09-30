<?php

namespace CNS\BotToServer\Types;

enum UserType: string {
    case TELEGRAM = 'telegram_id';
    case EMAIL = 'email';
}
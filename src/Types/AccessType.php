<?php

namespace CNS\BotToServer\Types;

enum AccessType: string {
    case TELEGRAM = 'telegram_id';
    case EMAIL = 'email';
}
<?php

namespace CNS\BotToServer\Types;

enum ServerStatusType : string {
    case ACTIVE = "active";
    case DISABLED = "disabled";
    case MAINTENANCE = "maintenance";
    case DRAINING = "draining";
    case DROPPED = "dropped";
}
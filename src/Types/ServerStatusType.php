<?php

namespace CNS\BotToServer\Types;

/**
 * Defines the available operational statuses of a VPN server.
 */
enum ServerStatusType: string
{
    /** Server is active and available for connections. */
    case ACTIVE = "active";

    /** Server is administratively disabled. */
    case DISABLED = "disabled";

    /** Server is undergoing maintenance. */
    case MAINTENANCE = "maintenance";

    /** Server is draining existing connections. */
    case DRAINING = "draining";

    /** Server has been marked as dropped. */
    case DROPPED = "dropped";
}
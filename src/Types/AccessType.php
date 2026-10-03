<?php

namespace CNS\BotToServer\Types;

enum AccessType: string {
    case OPENVPN = 'openvpn';
    case XRAY = 'xray';
}
<?php

namespace CNS\BotToServer\Types;

enum VPNType: string {
    case OPENVPN = 'openvpn';
    case XRAY = 'xray';
}
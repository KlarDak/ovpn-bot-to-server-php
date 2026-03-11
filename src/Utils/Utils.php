<?php

namespace CNS\OvpnBotToServer\Utils;
use CNS\OvpnBotToServer\Types\Env;

class Utils {
    public static function timeGenerator(int $time, int $minutes = 0) : string {
        return (new \DateTime('now', new \DateTimeZone('UTC')))->modify('+'.$minutes.' minutes')->format('Y-m-d H:i:s');
    }

    public static function saveOvpnFile(string $uuid, string $file) : bool {
        $path = Env::getConfigsDir() . "$uuid.ovpn";
        return file_put_contents($path, $file);
    }
    public static function removeOvpnFile(string $uuid) : bool {
        $path = Env::getConfigsDir() . "$uuid.ovpn";

        return unlink($path);
    }

    public static function isOvpnFileExists(string $uuid) : bool {
        return (file_exists(Env::getConfigsDir() . "$uuid.ovpn")) ? true : false;
    }
}
<?php

namespace CNS\OvpnBotToServer\Utils;
use CNS\OvpnBotToServer\Types\Env;

class Utils {
    /**
     * Return DATETIME format
     * 
     * @param int $time UNIX time
     * @param int $minutes Additional time
     * @return string
     */
    public static function timeGenerator(int $time, int $minutes = 0) : string {
        return (new \DateTime('now', new \DateTimeZone('UTC')))->modify('+'.$minutes.' minutes')->format('Y-m-d H:i:s');
    }

    /**
     * Save config to .ovpn-file
     * 
     * @param string $uuid UUID identify
     * @param string $file Content for .ovpn file
     * @return bool
     */
    public static function saveOvpnFile(string $uuid, string $file) : bool {
        $path = Env::getConfigsDir() . "$uuid.ovpn";
        return file_put_contents($path, $file);
    }

    /**
     * Remove .ovpn-config
     * 
     * @param string $uuid UUID identify
     * @return bool
     */
    public static function removeOvpnFile(string $uuid) : bool {
        $path = Env::getConfigsDir() . "$uuid.ovpn";

        return unlink($path);
    }

    /**
     * Check, is .ovpn-file exists
     * 
     * @param string $uuid UUID identify
     * @return bool
     */
    public static function isOvpnFileExists(string $uuid) : bool {
        return (file_exists(Env::getConfigsDir() . "$uuid.ovpn")) ? true : false;
    }
}
<?php

namespace CNS\OvpnBotToServer\Langs;

class Langs {
    protected static string $path = __DIR__ . "/packs/";
    protected static string $lang_enc;
    public static array $dict = [];

    public static function setLanguage(string $enc) : bool {
        $path = self::$path . $enc . ".json";

        if (file_exists($path)) {
            self::$lang_enc = $enc;
            self::$dict = json_decode(file_get_contents($path), true);

            return true;
        }
        else {
            return false;
        }
    }

    public static function getWords(string $ident) : string {
        return self::$dict[$ident] ?? "";
    }

    public static function getModifiedWorlds(string $ident, array $modificated) : string {
        $string = self::getWords($ident);

        foreach ($modificated as $mask => $word) {
            $string = str_replace($mask, $word, $string);
        }

        return $string;
    }
}
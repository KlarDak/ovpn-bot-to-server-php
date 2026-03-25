<?php

namespace CNS\OvpnBotToServer\Langs;

class Langs {
    /**
     * Path to language template files
     * 
     * @var string
     */
    protected static string $path = __DIR__ . "/packs/";
    
    /**
     * Language identifier
     * 
     * @var string
     */
    protected static string $lang_enc;

    /**
     * Array of dictionary
     * 
     * @var array
     */
    public static array $dict = [];

    /**
     * Set default language pack
     * 
     * @param string $enc Language identifier
     * @return boolean
     */
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

    /**
     * Get expression by its key
     * 
     * @param string $ident Ley of expression
     * @return string
     */
    public static function getWords(string $ident) : string {
        return self::$dict[$ident] ?? "";
    }

    /**
     * Get expression with its template-based processing
     * 
     * @param string $ident Key of expression
     * @param array $modificated Array of values for template processing
     * @return string
     */
    public static function getModifiedWords(string $ident, array $modificated) : string {
        $string = self::getWords($ident);

        foreach ($modificated as $mask => $word) {
            $string = str_replace($mask, $word, $string);
        }

        return $string;
    }
}
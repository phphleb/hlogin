<?php


namespace Phphleb\Hlogin\App;


final class Translate
{   
    protected static $lang = "en";

    private static $langData = [];

    private function __construct(){}

    public static function setLang(string $type) {
        self::$lang = $type;
    }

    public static function getLang() {
        return self::$lang;
    }

    public static function get(string $name, $lang = null) {
        if (empty($lang)) $lang = self::$lang;
        $data = self::load($lang);
        if (!empty($data[$lang][$name])) {
            return $data[$lang][$name];
        };
        return $name;
    }

    // Подстановка в строку значений из массива.
    public static function replace(string $str, array $values) {
        $str = self::get($str);
        foreach ($values as $key => $value){
            $str = strtr($str, ['{{' . $key . '}}' => $value]);
        }
        return $str;
    }

    // Возвращает вариативный запрос по шаблону/языку/подстановкам
    public static function getMailData($teplate, $name, array $values = [], $lang = null) {
        if (empty($lang)) $lang = self::$lang;
        $data = self::load($lang);
        $parts = isset($data[$lang][$name]) ? $data[$lang][$name] : null;
        $str = $parts[$teplate] ?? $parts['universal'] ?? null;
        if ($str) {
            return self::replace($str, $values);
        }
    }

    // Подгружает языковые файлы
    public static function load(string $lang) {
        if(isset(self::$langData[$lang])) {
            return self::$langData;
        }
        require_once __DIR__ . '/Langs/' . $lang . '.php';
        self::$langData[$lang] = $data;
        return self::$langData;
    }
}


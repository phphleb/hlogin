<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Content;

use Hleb\Static\Settings;

class AuthLang
{
    private static array $trans = [];

    /**
     * Returns a list of all language translations available for authorization.
     * This list overlaps with the allowed languages from the framework settings.
     * Thus, there must be a translation for the default language
     * and for those allowed in the settings.
     * Translations can be not only basic ones from the HLOGIN library,
     * but also copied/added to the /app/Bootstrap/Auth/Resources/ folder.
     *
     * Возвращает список всех доступных для авторизации языковых переводов.
     * Этот список пересекается с разрешенными языками из настроек фреймворка.
     * Таким образом, обязательно должен существовать перевод для дефолтного языка
     * и для разрешенных в настройках.
     * Переводы могут быть не только базовыми из библиотеки HLOGIN, но и
     * скопированными/добавленными в папку /app/Bootstrap/Auth/Resources/.
     */
    public static function getAll(): array
    {
        return \array_keys(self::getAllowedData());
    }

    /**
     * Returns a named array where the keys are the supported language abbreviations
     * for authorization and the values are the paths to the language files.
     *
     * Возвращает именованный массив, в котором ключи - это поддерживаемые
     * сокращения языков для авторизации, а значения - пути к языковым файлам.
     */
    public static function getAllowedData(): array
    {
        $result = [];
        $all = Settings::getParam('main', 'allowed.languages');
        foreach ($all as $lang) {
            if ($path = Settings::getRealPath("@app/Bootstrap/Auth/Resources/js/hloginlang{$lang}.js")) {
                $result[$lang] = $path;
            } else if ($path = Settings::getRealPath("@library/hlogin/web/js/hloginlang{$lang}.js")) {
                $result[$lang] = $path;
            }
        }
        return $result;
    }

    /**
     * Returns the found translation or source tag.
     * To add your own translation, you need to create a file based
     * on the standard one in the `app/Bootstrap/Auth/Resources/php/` folder.
     *
     * Возвращает найденный перевод или исходный тег.
     * Чтобы добавить собственный перевод, необходимо создать
     * на основе стандартного файл в папке `app/Bootstrap/Auth/Resources/php/`.
     */
    public static function trans(string $lang, string $tag): string|array
    {
        if (!isset(self::$trans[$lang])) {
            if (!\in_array($lang, Settings::getParam('main', 'allowed.languages'))) {
                throw new \RuntimeException('Unsupported language');
            }
            $path = Settings::getRealPath("@app/Bootstrap/Auth/Resources/php/$lang.php");
            if (!$path) {
                $path = Settings::getRealPath("@library/hlogin/App/BackendTranslation/$lang.php");
            }
            self::$trans[$lang] = require $path;
        }
        return self::$trans[$lang][$tag] ?? $tag;
    }
}
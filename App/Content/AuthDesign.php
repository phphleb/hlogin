<?php

namespace Phphleb\Hlogin\App\Content;

use Hleb\Static\Cookies;
use Hleb\Static\Settings;
use Phphleb\Hlogin\App\Data\ConfigStorage;

class AuthDesign
{
    private const DESIGN_ID = 'hlogin_design';

    /**
     * Since the original design type can be changed by the user,
     * the changed value is stored in browser Cookies.
     * Returns the final value of the current design type.
     *
     * Так как изначальный тип дизайна может быть изменен пользователем,
     * то изменённое значение сохраняется в Cookies браузера.
     * Возвращает конечное значение текущего типа дизайна.
     */
    public static function getActual(): string
    {
       $config = ConfigStorage::getConfig();
       $all = $config['registration']['design-options'];
       $userDesign = Cookies::get(self::DESIGN_ID)->value();
       if ($userDesign && \in_array($userDesign, $all)) {
           return $userDesign;
       }
       $default = $config['design'];
       if (!\in_array($default, $all)) {
           throw new \RuntimeException('The main `design` must match in `design-options`');
       }
       return  $default;
    }

    /**
     * Saving the design type in custom Cookies.
     *
     * Сохранение типа дизайна в пользовательские Cookies.
     */
    public static function set(string $design): void
    {
        $config = ConfigStorage::getConfig();
        if (!\in_array($design, $config['registration']['design-options'])) {
            $design = $config['design'];
        }
        Cookies::set(self::DESIGN_ID, $design, ['expires' => time() + $config['registration']['session-duration']]);
    }

    /**
     * Returns a list of all design types available for authorization.
     * This list overlaps with the allowed types from the library settings.
     * Thus, there must be a style file for the `design-options` types allowed
     * in the settings (/storage/lib/phphleb/hlogin/config.json).
     * Styles can be not only basic from the HLOGIN library, but also
     * copied/added to the /app/Bootstrap/Auth/Resources/ folder.
     *
     * Возвращает список всех доступных для авторизации типов дизайна.
     * Этот список пересекается с разрешенными типами из настроек библиотеки.
     * Таким образом, обязательно должен существовать файл стилей для разрешенных
     * в настройках (/storage/lib/phphleb/hlogin/config.json) типов `design-options`.
     * Стили могут быть не только базовыми из библиотеки HLOGIN, но и
     * скопированными/добавленными в папку /app/Bootstrap/AuthResources/.
     */
    public static function getAll(): array
    {
        return \array_keys(self::getAllowedData());
    }

    /**
     * Returns a named array where the keys are the supported design types
     * for authorization and the values are the paths to the style files.
     *
     * Возвращает именованный массив, в котором ключи - это поддерживаемые
     * типы дизайна для авторизации, а значения - пути к файлам стилей.
     */
    public static function getAllowedData(): array
    {
        $result = [];
        $config = ConfigStorage::getConfig();
        $all = $config['registration']['design-options'];
        foreach ($all as $design) {
            if ($path = Settings::getRealPath("@app/Bootstrap/Auth/Resources/css/hloginstyle{$design}.css")) {
                $result[$design] = $path;
            } else if ($path = Settings::getRealPath("@library/hlogin/web/css/hloginstyle{$design}.css")) {
                $result[$design] = $path;
            }
        }
        return $result;
    }
}
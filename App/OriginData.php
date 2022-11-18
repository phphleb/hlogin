<?php


namespace Phphleb\Hlogin\App;


class OriginData
{
    const EMAIL_PATTERN = '/^[-_+\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,10}$/';

    const PASSWORD_PATTERN = '/^[a-zA-Z0-9]{6,}$/';

    const CAPTCHA_PATTERN = '/^[a-zA-Z0-9]{5,6}$/';

    const NAME_PATTERN = '/^[\w\._\-\s\№\?\@\:\$\+\!\;]{0,150}$/iu';

    const MESSAGE_PATTERN = '/^[^<>]{5,10000}$/';

    /** @internal  */
    const LANGUAGES = ['ru', 'en', 'de', 'es', 'zh']; // For compatibility with older versions

    const GLOBAL_PATTERNS = ['base', 'dark', 'light', 'special', 'sport', 'game', 'blank'];

    const HIDDEN_PATTERNS = ['blank'];

    const  USER_REGISTER_TYPE = 'UserRegister';

    const  USER_PASSWORD_TYPE = 'UserPassword';

    const  USER_ENTER_TYPE = 'UserEnter';

    const  NEW_PASSWORD_TYPE = 'NewPassword';

    const  USER_PROFILE_TYPE = 'UserProfile';

    const  CONTACT_MESSAGE = 'ContactMessage';

    const  USERS_TABLE_NAME = 'users';

    const HIDE_PANELS = 0;

    const SHOW_PANELS = 1;

    const HIDE_BUTTONS = 3;

    const FROM_API = 4;

    private static $design = null;

    private static bool $showPanels = true;

    private static $localLang = null;

    private static array $languages = [];

    /**
     * Sets the language type for the current script.
     *
     * Устанавливает тип языка для текущего скрипта.
     *
     * @param string $lang
     */
    public static function setLocalLang(string $lang) {
        if(in_array($lang, self::getLanguages())) {
            self::$localLang = $lang;
        }
    }

    /**
     * Returns the language type for the current script, set by the method setLocalLang().
     *
     * Возвращает тип языка для текущего скрипта, установленный методом setLocalLang().
     *
     * @return string|null
     */
    public static function getLocalLang() {
        return self::$localLang;
    }

    /**
     * Sets the design type for the current script.
     *
     * Устанавливает тип дизайна для текущего скрипта.
     *
     * @param string $design
     */
    public static function setLocalDesign(string $design) {
        if($design && is_null(self::$design) && in_array($design,self::GLOBAL_PATTERNS)) {
            self::$design = $design;
        }
    }

    /**
     * Returns the type of design.
     *
     * Возвращает тип дизайна.
     *
     * @return string
     */
    public static function getDesign() {
        return is_null(self::$design) ?  Main::getConfig()['hlogin']['reg_data']['design'] : self::$design;
    }

    /**
     * @return bool
     */
    public static function isRegPanel(): bool
    {
        return self::$showPanels;
    }

    public static function setRegPanelOn(bool $status) {
        self::$showPanels = $status;
    }

    public static function activeTypes() {
        return array_diff(self::GLOBAL_PATTERNS, self::HIDDEN_PATTERNS);
    }

    /**
     * Initializer of registration panels in <body>...</body> of the page.
     * @param int $type-sets the display type, if not specified in routing.
     * @return string
     *//**
     * Инициализатор панелей регистрации в <body>...</body> страницы.
     * @param int $type - устанавливает тип отображения, если не задан в роутинге.
     * @return string
     * */
    public static function initRegistrationPanels($type = self::SHOW_PANELS) {
        $panels = hleb_insert_template('hlogin/templates/add', ['type' => $type], true);
        return is_string($panels) ? $panels : '';
    }

    /**
     * Returns the result of a search for all used language abbreviations.
     *
     * Возвращает результат поиска всех тспользуемых языковых сокращений.
     *
     * @return array
     */
    public static function getLanguages(): array {
        if (empty(self::$languages)) {
            self::$languages = self::LANGUAGES;
            if (defined('HLOGIN_LOCALIZE_BACKEND_DIR')) {
                self::$languages = array_unique(array_merge(self::$languages, self::searchFileNamesInDirectory( HLEB_GLOBAL_DIRECTORY . DIRECTORY_SEPARATOR . trim(HLOGIN_LOCALIZE_BACKEND_DIR, '\\/ '))));
            }
        }
        return self::$languages;
    }

    private static function searchFileNamesInDirectory(string $directory) {
        $langList = [];
        if (is_dir($directory)) {
            foreach (scandir($directory) as $file) {
                if ($file !== '.' && $file !== '..') {
                    $langList[] = explode('.', $file)[0];
                }
            }
        }
        return $langList;
    }
}


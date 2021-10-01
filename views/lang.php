<?php
function language_block() {
    $language = \Phphleb\Hlogin\App\Translate::getLang();
    $urlList = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'));
    $options = [];
    foreach (Phphleb\Hlogin\App\OriginData::LANGUAGES as $lang) {
        $urlList[0] = $lang;
        $url = '//' . $_SERVER['HTTP_HOST'] . '/' . implode('/', $urlList);
        $viewLang = strtoupper($lang);
        $options[] = "<option " . ($lang == $language ? 'selected' : '') . " value='{$url}'>{$viewLang}</option>";
    }
    return '<select onchange="document.location.href = this.value">' . implode("\n", $options) . '</select>';
}

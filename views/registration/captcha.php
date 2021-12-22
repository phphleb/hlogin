<?php

require __DIR__ . "/functions.php";
require_once __DIR__ . '/../lang.php';

use Phphleb\Hlogin\App\Translate;
use Phphleb\Hlogin\App\Converter;
use Hleb\Constructor\Handlers\URL;
use Hleb\Constructor\Handlers\Request;
use Phphleb\Ucaptcha\Captcha;

$path = HLEB_PROJECT_STORAGE_DIR . "/lib/ucaptcha/config.json";
$data = Converter::getData($path, 'ucaptcha');
$regData = $data['ucaptcha']['data'] ?? [];
$request = Request::getPost();
$active = isset($data['ucaptcha']['data']['active']) && $data['ucaptcha']['data']['active'] === 'on';

if(!empty(Request::getPost())) {
    check_csrf_protection($request['hlogin_csrf_protection'] ?? null);
    if (Converter::testCaptchaJson($path)) {
        $data['ucaptcha']['data']['active'] = isset($request['active']) && $request['active'] === 'on'  ? "on" : "off";
        $data['ucaptcha']['data']['design'] = isset($request['design']) && in_array($request['design'], array_merge(Captcha::TYPES, ['auto']))  ? $request['design'] : "base";
        Converter::saveData($data, $path, 'ucaptcha');
        Converter::testCaptchaJson($path);
        save_data(true);
    } else {
        save_data(false);
    }
}
show_message_log();

?>
<br><div class="hlogin-lang"><?= language_block(); ?></div>
<div class="hlogin-link-base"><a href="https://github.com/phphleb/ucaptcha">github.com/phphleb/ucaptcha</a></div>
<div class="hlogin-captcha-over">
    <h2>Universal Captcha v.<?=  $data['ucaptcha']['version'] ?></h2>
<form name="block_position" action="" method="post">
    <input type="hidden" name="hlogin_csrf_protection" value="<?= $csrfToken ?>">
<p><b><?php echo Translate::get('captcha'); ?> <span class="hlogin-a7e-danger"><?= $active ? '' : '&#9888;'; ?></span></b></p>
<p>
    <label  class="hlogin-a7e-str"><input name="active"  type="checkbox" value="on" <?= hl_checkbox_on('active', $regData); ?> > <?php echo Translate::get('standard_captcha_on'); ?></label>
</p><br>
    <p><b><?php echo Translate::get('design'); ?></b></p>
    <p>
        <label  class="hlogin-a7e-str">
            <select class="hlogin-a7e-text" style="max-width: 100px" name="design">
                <?php
                foreach(array_merge(Captcha::TYPES, ['auto']) as $design){
                    print "<option " . " value='$design' " . ($data['ucaptcha']['data']['design'] == $design ? "selected" : "") . ">$design</option>";
                }
                ?>
            </select>
        </label>
    </p>
<br>
    <p><input type="submit" value="<?php echo Translate::get('apply_changes'); ?>" class="hlogin-a7e-button" id="hloginSendButton"></p>
</form>
</div>

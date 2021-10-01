<?php

require __DIR__ . "/functions.php";
require_once __DIR__ . '/../lang.php';

use Phphleb\Hlogin\App\Translate;
use Phphleb\Hlogin\App\Converter;
use Phphleb\Hlogin\App\Main;
use Hleb\Constructor\Handlers\Request;

$path = HLEB_PROJECT_STORAGE_DIR . "/register/contact_config.json";
$data = Converter::getData($path);
$regData = $data['contact']['data'] ?? [];
$request = Request::getPost();
$email = $baseEmail = 'admin@' . Request::getDomain();
$config = Main::getConfigContact();
if (!empty($config['data']['mail_to'])) {
    $email = $config['data']['mail_to'];
}

if(!empty(Request::getPost())) {
    check_csrf_protection($request['hlogin_csrf_protection'] ?? null);
    if (Converter::testContactJson($path)) {
        $data['contact']['data']['active'] = isset($request['active']) && $request['active'] === 'on'  ? "on" : "off";
        $data['contact']['data']['mail_to'] = isset($request['mail_to']) && strpos($request['mail_to'], '@') !== false ? $request['mail_to'] : $baseEmail;
        Converter::saveData($data, $path);
        Converter::testContactJson($path);
        save_data(true);
    } else {
        save_data(false);
    }
}
show_message_log();

?>
<br>
<div class="hlogin-lang"><?= language_block(); ?></div>
<div class="hlogin-contact-over">
    <h2><?=  Translate::get('feedback') ?></h2>
<form name="block_position" action="" method="post">
    <input type="hidden" name="hlogin_csrf_protection" value="<?= $csrfToken ?>">
    <p>
        <label  class="hlogin-a7e-str"><input name="active"  type="checkbox" value="on" <?= hl_checkbox_on('active', $regData); ?> > <?php echo Translate::get('feedback_on'); ?></label>
    </p><br>
<p><b>Mail to</b></p>
<p>
    <label  class="hlogin-a7e-str"><input name="mail_to" class="hlogin_mail_text"  type="text" value="<?= $email; ?>"></label>
</p>
    <br>
    <p><input type="submit" value="<?php echo Translate::get('apply_changes'); ?>" class="hlogin-a7e-button" id="hloginSendButton"></p>
</form>
</div>

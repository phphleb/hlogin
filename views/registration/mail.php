<?php

require __DIR__ . "/functions.php";
require_once __DIR__ . '/../lang.php';

use Phphleb\Hlogin\App\Translate;
use Phphleb\Hlogin\App\Converter;
use Hleb\Constructor\Handlers\URL;
use Phphleb\Hlogin\App\Main;
use Hleb\Constructor\Handlers\Request;

$path = HLEB_PROJECT_STORAGE_DIR . "/lib/muller/config.json";
$data = Converter::getData($path, 'muller');
$regData = $data['muller']['data'] ?? [];
$request = Request::getPost();
$types = ['base', 'dark'];
$email = $baseEmail = 'no-reply@' . Request::getDomain();
$config = Main::getConfigMuller();
if (!empty($config['data']['mail_from'])) {
    $email = $config['data']['mail_from'];
}

if(!empty(Request::getPost())) {
    check_csrf_protection($request['hlogin_csrf_protection'] ?? null);
    if (Converter::testMullerJson($path)) {
        $data['muller']['data']['design'] = isset($request['design']) && in_array($request['design'], $types)  ? $request['design'] : "";
        $data['muller']['data']['mail_from'] = isset($request['mail_from']) && strpos($request['mail_from'], '@') !== false ? $request['mail_from'] : $baseEmail;
        $data['muller']['data']['save_log'] = isset($request['save_log']) && $request['save_log'] === 'on'  ? 'on' : 'off';
        $data['muller']['data']['not_send_by_email'] = isset($request['not_send_by_email']) && $request['not_send_by_email'] === 'on'  ? 'on' : 'off';
        $data['muller']['data']['regards_block'] = isset($request['regards_block']) ? $request['regards_block'] : "";
        $data['muller']['data']['duplicate-en-text'] = isset($request['duplicate-en-text']) && $request['duplicate-en-text'] === 'on'  ? 'on' : 'off';

        Converter::saveData($data, $path, 'muller');
        Converter::testMullerJson($path);
        save_data(true);
    } else {
        save_data(false);
    }
}
show_message_log();

?>
<br>
<div class="hlogin-lang"><?= language_block(); ?></div>
<div class="hlogin-link-base"><a href="https://github.com/phphleb/muller">github.com/phphleb/muller</a></div>
<div><?= add_type_bd_status() ?></div>
<div class="hlogin-mail-over">
    <h2>Mail Sender v.<?=  $data['muller']['version'] ?></h2>
<form name="block_position" action="" method="post">
    <input type="hidden" name="hlogin_csrf_protection" value="<?= $csrfToken ?>">
<p><b>Mail from</b></p>
<p>
    <label  class="hlogin-a7e-str"><input name="mail_from" class="hlogin_mail_text"  type="text" value="<?= $email; ?>"></label>
</p>
    <label  class="hlogin-a7e-str"><input name="save_log"  type="checkbox" value="on" <?= hl_checkbox_on('not_send_by_email', $regData); ?> > <?php echo Translate::get('not_send_by_email'); ?></label><br>
    <label  class="hlogin-a7e-str"><input name="save_log"  type="checkbox" value="on" <?= hl_checkbox_on('save_log', $regData); ?> > <?php echo Translate::get('save_post_to_log'); ?></label><br>
    <label  class="hlogin-a7e-str"><input name="duplicate-en-text"  type="checkbox" value="on" <?= hl_checkbox_on('duplicate-en-text', $regData); ?> > <?php echo Translate::get('duplicate-english'); ?></label>
    <br>
    <p><b><?php echo Translate::get('design'); ?></b></p>
    <p>
        <label  class="hlogin-a7e-str">
            <select class="hlogin-a7e-text" style="max-width: 100px" name="design">
                <?php
                foreach(array_merge(['auto'], $types) as $design){
                    print "<option " . " value='$design' " . ($data['muller']['data']['design'] == $design ? "selected" : "") . ">$design</option>";
                }
                ?>
            </select>
        </label>
    </p>
    <br>
    <p><b><?php echo Translate::get('regards_block'); ?></b></p>
    <p><textarea name="regards_block" class="hlogin-textarea-min"><?= html_entity_decode($regData['regards_block'] ?? '') ?></textarea></p>
    <br>
    <p><input type="submit" value="<?php echo Translate::get('apply_changes'); ?>" class="hlogin-a7e-button" id="hloginSendButton"></p>
</form>
</div>

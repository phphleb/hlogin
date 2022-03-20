<?php

require __DIR__ . "/functions.php";
require_once __DIR__ . '/../lang.php';

use Phphleb\Hlogin\App\Translate;
use Phphleb\Hlogin\App\Converter;
use Hleb\Constructor\Handlers\URL;
use Hleb\Constructor\Handlers\Request;
use Phphleb\Hlogin\App\OriginData;

$path = HLEB_PROJECT_STORAGE_DIR . "/register/config.json";
$data = Converter::getData($path, 'hlogin');
$request = Request::getPost();
$regData = $data['hlogin']['reg_data'] ?? [];
$version = $data['hlogin']['version'] ?? null;

if(!empty(Request::getPost())) {
    check_csrf_protection($request['hlogin_csrf_protection'] ?? null);
    if (Converter::testJson($path)) {
        $data['hlogin']['reg_data'] = [];
        $checkList = ['design', 'lang', 'cell_reg_types', 'cell_reg_page_head', 'font_base', 'block_orient', 'cell_passport', 'cell_phone', 'cell_name', 'cell_surname', 'cell_address', 'cell_promocode', 'cell_subscription', 'cell_terms', 'cell_privacy_policy', 'required_phone', 'required_name', 'required_surname', 'required_address', 'cell_password', 'profile_phone', 'profile_name', 'profile_surname', 'profile_address', 'terms_url', 'privacy_policy_url', 'get_url_after_reg'];
        foreach ($request as $key => $value) {
            if (!in_array($key, $checkList)) {
                continue;
            }
            if (!(preg_match("/[^a-zA-Z0-9\-_:?\/=&.\#\s,!$]+/", $key . $value))) {
                $data['hlogin']['reg_data'][$key] = $request[$key];
            }
            if ($key === 'design' && in_array($value, OriginData::GLOBAL_PATTERNS)) {
                $data['hlogin']['reg_data']['design'] = $value;
            }
            if ($key === 'lang' && in_array($value, OriginData::getLanguages())) {
                $data['hlogin']['reg_data']['lang'] = $value;
            }
            if ($key === 'cell_reg_types') {
                $data['hlogin']['reg_data']['lang'] = $value;
            }
            if ($key == 'cell_reg_page_head' || $key == 'font_base') {
                $data['hlogin']['reg_data'][$key] = htmlentities($value, ENT_QUOTES);
            }
            if ($key == 'get_url_after_reg') {
                if(empty($value)) {
                    $data['hlogin']['reg_data'][$key] = '/$LANG/login/profile/';
                } else {
                    $data['hlogin']['reg_data'][$key] = preg_match("#^[^\:\<\.]+$#ui", $value) ? $value : '/$LANG/login/profile/';
                }
            }
        }
        if (empty($data['hlogin']['reg_data']['reg_table_name'])) {
            $data['hlogin']['reg_data']['reg_table_name'] = OriginData::USERS_TABLE_NAME;
        }

        Converter::saveData($data, $path, 'hlogin');
        Converter::testJson($path);
        save_data(true);
    } else {
        save_data(false);
    }
}
show_message_log();

?>
<div><?= add_type_bd_status() ?></div>
<div class='hlogin-a7e-page'><div class="hlogin-lang"><?= language_block(); ?></div>
    <div class="hlogin-link-base"><a href="https://github.com/phphleb/hlogin">github.com/phphleb/hlogin</a></div>

    <div><span class="hlogin-h2">HLOGIN</span> <span class="hlogin-h2-version">v<?=  $version ?> Beta</span></div><br>

    <form name="block_position" action="" method="post">
        <input type="hidden" name="hlogin_csrf_protection" value="<?= $csrfToken ?>">
    <span class="hlogin-a7e-dynamic-block">
     <p><b><?php echo Translate::get('design'); ?></b></p>
     <p>
         <label  class="hlogin-a7e-str">
             <select class="hlogin-a7e-text" name="design">
                 <?php
                 foreach(OriginData::GLOBAL_PATTERNS as $design){
                     print "<option " . ($design == 'blank' ? 'style="color:#999"' : '') . " value='$design' " . ($data['hlogin']['reg_data']['design'] == $design ? "selected" : "") . ">$design</option>";
                 }
                 ?>
             </select>
         </label>
     </p>
     </span>
     <span class="hlogin-a7e-dynamic-block">
     <p><b><?php echo Translate::get('select_lang'); ?></b></p>
     <p>
         <label  class="hlogin-a7e-str">
             <select class="hlogin-a7e-text" name="lang">
                 <?php
                 foreach(OriginData::getLanguages() as $lang){
                     print "<option value='$lang' " . ($data['hlogin']['reg_data']['lang'] == $lang ? "selected" : "") . ">$lang</option>";
                 }
                 ?>
             </select>
         </label>
     </p>
     </span>
    <span class="hlogin-a7e-dynamic-block">
    <p><b><?php echo Translate::get('block_position'); ?></b></p>
    <p>
        <label  class="hlogin-a7e-str"><input type="radio" name="block_orient" <?= hl_radio_checked('block_orient', 'right', $regData); ?> value="right"><?php echo Translate::get('on_right'); ?></label>
        <label  class="hlogin-a7e-str"><input type="radio"  name="block_orient" <?= hl_radio_checked('block_orient','top', $regData); ?> value="top"><?php echo Translate::get('up'); ?></label>
        <label  class="hlogin-a7e-str"><input type="radio"  name="block_orient" <?= hl_radio_checked('block_orient','left', $regData); ?>  value="left"><?php echo Translate::get('left'); ?></label>
        <label  class="hlogin-a7e-str"><input type="radio"  name="block_orient" <?= hl_radio_checked('block_orient','none', $regData); ?>  value="none"><?php echo Translate::get('none'); ?></label>
    </p>
    </span>
    <br><hr><br>

    <p><b><?php echo Translate::get('fields_for_registration'); ?></b></p>
    <p>
        <label  class="hlogin-a7e-str"><input name="cell_email" type="checkbox" value="on" disabled checked><?php echo Translate::get('E-mail'); ?></label>
        <label  class="hlogin-a7e-str"><input name="cell_phone"  type="checkbox" value="on" <?= hl_checkbox_on('cell_phone', $regData); ?> ><?php echo Translate::get('phone'); ?></label>
        <label  class="hlogin-a7e-str"><input name="cell_name" type="checkbox" value="on" <?= hl_checkbox_on('cell_name',  $regData); ?> ><?php echo Translate::get('name'); ?></label>
        <label  class="hlogin-a7e-str"><input name="cell_surname" type="checkbox" value="on" <?= hl_checkbox_on('cell_surname',  $regData); ?> ><?php echo Translate::get('surname'); ?></label>
        <label  class="hlogin-a7e-str"><input name="cell_address" type="checkbox" value="on" <?= hl_checkbox_on('cell_address', $regData); ?> ><?php echo Translate::get('address'); ?></label>
        <label  class="hlogin-a7e-str"><input name="cell_password"  type="checkbox" value="on" <?= hl_checkbox_on('cell_password', $regData); ?> ><?php echo Translate::get('password'); ?></label>
        <label  class="hlogin-a7e-str"><input name="cell_promocode" type="checkbox" value="on" <?= hl_checkbox_on('cell_promocode', $regData); ?> ><?php echo Translate::get('promo_code'); ?></label>
        <label  class="hlogin-a7e-str"><input name="cell_subscription" type="checkbox" value="on" <?= hl_checkbox_on('cell_subscription', $regData); ?> ><?php echo Translate::get('subscription'); ?></label>
        <label  class="hlogin-a7e-str"><input name="cell_terms" type="checkbox" value="on" <?= hl_checkbox_on('cell_terms', $regData); ?> ><?php echo Translate::get('terms_of_use'); ?></label>
        <label  class="hlogin-a7e-str"><input name="cell_privacy_policy" type="checkbox" value="on" <?= hl_checkbox_on('cell_privacy_policy', $regData); ?> ><?php echo Translate::get('privacy_policy'); ?></label>
    </p>
    <br>

    <p><b><?php echo Translate::get('required_fields'); ?></b></p>
    <p>
        <label  class="hlogin-a7e-str"><input name="required_email" type="checkbox" value="on" disabled checked><?php echo Translate::get('E-mail'); ?>*</label>
        <label  class="<?= hl_checkbox_hidden('cell_password', $regData); ?> hlogin-a7e-str"><input name="required_password" type="checkbox" value="on" disabled checked><?php echo Translate::get('password'); ?>*</label>
        <label  class="<?= hl_checkbox_hidden('cell_phone', $regData); ?> hlogin-a7e-str"><input name="required_phone" type="checkbox" value="on" <?= hl_checkbox_on('required_phone', $regData); ?> ><?php echo Translate::get('phone'); ?>*</label>
        <label  class="<?= hl_checkbox_hidden('cell_name', $regData); ?> hlogin-a7e-str"><input name="required_name" type="checkbox" value="on" <?= hl_checkbox_on('required_name',  $regData); ?> ><?php echo Translate::get('name'); ?>*</label>
        <label  class="<?= hl_checkbox_hidden('cell_surname', $regData); ?> hlogin-a7e-str"><input name="required_surname" type="checkbox" value="on" <?= hl_checkbox_on('required_surname',  $regData); ?> ><?php echo Translate::get('surname'); ?>*</label>
        <label  class="<?= hl_checkbox_hidden('cell_address', $regData); ?> hlogin-a7e-str"><input name="required_address" type="checkbox" value="on" <?= hl_checkbox_on('required_address',  $regData); ?> ><?php echo Translate::get('address'); ?>*</label>
    </p>
    <br>

        <p><b><?php echo Translate::get('show_in_profile'); ?></b></p>
        <p>
            <label  class="hlogin-a7e-str"><input name="profile_email" type="checkbox" value="on" disabled checked><?php echo Translate::get('E-mail'); ?>*</label>
            <label  class="hlogin-a7e-str"><input name="profile_phone" type="checkbox" value="on" <?= hl_checkbox_on('profile_phone', $regData); ?> ><?php echo Translate::get('phone'); ?></label>
            <label  class="hlogin-a7e-str"><input name="profile_name" type="checkbox" value="on" <?= hl_checkbox_on('profile_name',  $regData); ?> ><?php echo Translate::get('name'); ?></label>
            <label  class="hlogin-a7e-str"><input name="profile_surname" type="checkbox" value="on" <?= hl_checkbox_on('profile_surname',  $regData); ?> ><?php echo Translate::get('surname'); ?></label>
            <label  class="hlogin-a7e-str"><input name="profile_address" type="checkbox" value="on" <?= hl_checkbox_on('profile_address',  $regData); ?> ><?php echo Translate::get('address'); ?></label>
        </p>
        <hr><br>

     <span class="hlogin-a7e-dynamic-block">
     <p><b><?php echo Translate::get('link_to_user_agreement'); ?></b></p>
     <p>
         <label  class="hlogin-a7e-str"><input name="terms_url" type="text" class="hlogin-a7e-text"  maxlength="255" minlength="0" value="<?= $regData['terms_url'] ?? '' ?>" ></label>
     </p>
     </span>

     <span class="hlogin-a7e-dynamic-block">
     <p><b><?php echo Translate::get('link_to_privacy policy'); ?></b></p>
     <p>
         <label  class="hlogin-a7e-str"><input name="privacy_policy_url" type="text" class="hlogin-a7e-text"  maxlength="255" minlength="0" value="<?= $regData['privacy_policy_url'] ?? '' ?>" ></label>
     </p>
     </span>

     <span class="hlogin-a7e-dynamic-block">
     <p><b><?php echo Translate::get('font_base'); ?></b></p>
     <p>
         <label  class="hlogin-a7e-str"><input name="font_base" type="text" class="hlogin-a7e-text" placeholder="font-family:" pattern="[^:;]+" maxlength="255" minlength="0" value="<?= $regData['font_base'] ?? '' ?>" ></label>
     </p>
     </span>

     <span class="hlogin-a7e-dynamic-block">
     <p><b><?php echo Translate::get('get_url_after_reg'); ?></b></p>
     <p>
         <label  class="hlogin-a7e-str"><input name="get_url_after_reg" type="text" class="hlogin-a7e-text"  maxlength="500" minlength="0" placeholder="<?= $regData['get_url_after_reg'] ?? '/home/' ?>" value="<?= $regData['get_url_after_reg'] ?? '' ?>" ></label>
     </p>
     </span>


        <br>
        <br>
        <p><b><?php echo Translate::get('header_code'); ?>: </b>
            <a href="/<?php echo Request::get('lang'); ?>/login/action/enter/" class="hlogin-a7e-link"><?php echo Translate::get('enter_page'); ?></a>
            <a href="/<?php echo Request::get('lang'); ?>/login/action/registration/" class="hlogin-a7e-link"><?php echo Translate::get('register_page'); ?></a>
            <a href="/<?php echo Request::get('lang'); ?>/login/action/contact/" class="hlogin-a7e-link"><?php echo Translate::get('feedback'); ?></a>
            <a href="/<?php echo Request::get('lang'); ?>/login/profile/" class="hlogin-a7e-link"><?php echo Translate::get('profile_page'); ?></a>
        </p>
            <p><textarea name="cell_reg_page_head" class="hlogin-header-code"><?= html_entity_decode($regData['cell_reg_page_head'] ?? '') ?></textarea></p>
        <br><br>

    <p><input type="submit" value="<?php echo Translate::get('apply_changes'); ?>" class="hlogin-a7e-button" id="hloginSendButton"></p>
    </form>
</div>

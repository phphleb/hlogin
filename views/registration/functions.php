<?php

use Hleb\Constructor\Handlers\URL;
use Phphleb\Hlogin\App\Translate;

$csrfToken = str_shuffle(md5(rand()) . md5(rand()));
$_SESSION['HLOGIN_ADMIN_CSRF_PROTECTION'][] = $csrfToken;
if(count($_SESSION['HLOGIN_ADMIN_CSRF_PROTECTION']) > 10) {
    array_shift($_SESSION['HLOGIN_ADMIN_CSRF_PROTECTION']);
}

function check_csrf_protection($code) {
   if(!in_array($code, $_SESSION['HLOGIN_ADMIN_CSRF_PROTECTION'])) {
       die('Protection from CSRF');
   }
}

function hl_radio_checked($name, $value, $list) {
    return !empty($list[$name]) && $list[$name] == $value ? "checked" : "";
}

function hl_checkbox_on($name, $list) {
    return !empty($list[$name]) && $list[$name] == "on" ? "checked" : "";
}

function hl_checkbox_hidden($name, $list) {
    return !empty($list[$name]) && $list[$name] == "on" ? "" : "hlogin-a7e-blocked-str";
}

function save_data($type = true) {
    $_SESSION['HLOGIN_MESSAGE_LOG'] = $type ? 'success' : 'error';
    header('Location: ' . URL::getMainClearUrl());
    exit;
}

function show_message_log() {
    if(!empty($_SESSION['HLOGIN_MESSAGE_LOG'])) {
        $type = $_SESSION['HLOGIN_MESSAGE_LOG'];
        $message =  $type === "success" ? Translate::get('message_log_success') : Translate::get('message_log_error');
        $_SESSION['HLOGIN_MESSAGE_LOG'] = null;
        unset($_SESSION['HLOGIN_MESSAGE_LOG']);

        print "
          <div id='hloginMessageLog' class='hlogin_message_log_over hlogin_message_log_{$type}'>
            {$message}
            <script>
             setTimeout(function(){
                 document.getElementById('hloginMessageLog').classList.add('hlogin_message_close');
             }, 3000);
            </script>
          </div>          
        ";
    }
}




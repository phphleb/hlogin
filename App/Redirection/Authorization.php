<?php

namespace Phphleb\Hlogin\App\Redirection;

// Processing routes for registration pages

use \Phphleb\Hlogin\App\System\UserRegistration;
use \Phphleb\Hlogin\App\HloginUserModel;

class Authorization
{
    function get() {
        return null;
    }
    
    function variableExit($forced = false) {
        if(!isset($_SESSION)) session_start();

        if($forced) {
            $userEmail = UserRegistration::checkEmailAddress();
            if ($userEmail !== false) {
                HloginUserModel::setCells(HloginUserModel::CELL_EMAIL, $userEmail, [HloginUserModel::CELL_SESSIONKEY => md5($userEmail) . "-" . str_shuffle(md5(rand()) . md5(rand()))]);
            }
        }

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        $_SESSION = [];
        setcookie('HLOGIN_VERIFICATION_INDEX', '', 0, '/');
        session_destroy();

        if($_SERVER['REQUEST_METHOD'] === 'GET') {
           return '<script>location.replace("/");</script>';
        }

        return null;
    }

}


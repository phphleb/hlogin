<?php

use Phphleb\Hlogin\App\HloginUserLogModel;
use \Phphleb\Hlogin\App\HloginUserModel;
use Hleb\Constructor\Handlers\Request;
use \Phphleb\Hlogin\App\System\UserRegistration;
use \Phphleb\Hlogin\App\Translate;

$userId = Request::getGet('userid', null);
$host = Request::getMainClearUrl();

$postData = Request::getPost();
if($postData) {
    $_POST = [];
    unset($_POST['email']);
    unset($_POST);
}

echo '<div class=\'hlogin-adm-statistic-table\'>';

echo "<input type='text' placeholder='ID or E-mail' value='" . ($userId ?? '') . "' id='searchIdCell'><button onclick='document.location.href = \"$host?userid=\" + document.getElementById(\"searchIdCell\").value'>" . Translate::get('search') . "</button><br><br>";


if($postData && Request::getPost('email', false) && isset($postData['email']) && $userId) {
    $cells = HloginUserModel::getCells('id', $userId);
    if(UserRegistration::getNumericType() !== UserRegistration::REGISTERED_COMANDANTE || $cells['regtype'] == UserRegistration::REGISTERED_COMANDANTE || $postData['regtype'] == UserRegistration::REGISTERED_COMANDANTE) {
        $messageType = 'error';
        $messageText = 'Insufficient rights to change data!';
    } else if(empty($postData['email']) || strripos($postData['email'], '@') === false) {
        $messageType = 'error';
        $messageText = 'The saved data has not been validated!';
    } else {
        $savedData = [
            'email' => $postData['email'],
            'login' => standardization($postData['login']),
            'name' => standardization($postData['name']),
            'surname' => standardization($postData['surname']),
            'phone' => standardization($postData['phone']),
            'address' => standardization($postData['address']),
            'regtype' => $postData['regtype']
        ];
        $action = HloginUserModel::setCells('id', $userId, $savedData);
        if($action) {
            try {
                $result = HloginUserLogModel::createRowFromData(
                    $userId,
                    $savedData['regtype'],
                    'adminzone',
                    $savedData['email'],
                    Request::getRemoteAddress(),
                    $savedData['name'] ?? null,
                    $savedData['surname'] ?? null,
                    $savedData['phone'] ?? null,
                    $savedData['address'] ?? null,
                    'Changing data from the admin panel. Admin ID = ' . UserRegistration::getUserId(),
                    UserRegistration::getUserId()
                );
                if($result) {
                    $messageType = 'success';
                    $messageText = Translate::get('message_log_success');
                } else {
                    $messageType = 'error';
                    $messageText = 'Failed to save the request log.';
                }
            } catch (\Throwable $e) {
                $messageType = 'error';
                $messageText = 'Failed to save the request log. ' . $e->getMessage();
            }
        }
    }
    if(isset($messageType)) {
        print "
          <div id='hloginMessageLog' class='hlogin_message_log_over hlogin_message_log_{$messageType}'>
            {$messageText}
            <script>
             setTimeout(function(){
                 document.getElementById('hloginMessageLog').classList.add('hlogin_message_close');
             }, 3000);
            </script>
          </div>          
        ";
    }
}


if ($userId) {
    $cells = HloginUserModel::getCells(strripos($userId, '@') === false ? 'id' : 'email', $userId);
    if ($cells) {
        $regType = $cells['regtype'];
        $regTypes = [
            UserRegistration::BANNED_USER => 'banned',
            UserRegistration::DELETED_USER => 'deleted',
            UserRegistration::PRIMARY_USER => 'pre-registered',
            UserRegistration::REGISTERED_USER => 'registered',
            UserRegistration::REGISTERED_ADMIN => 'admin',
            UserRegistration::REGISTERED_COMANDANTE => 'superadmin',
        ];
        if (!isset($regTypes[$regType])) {
            $regTypes[$regType] = 'register level ' . ($regType - 2);
        }

        echo '<form action="?userid=' . $cells['id'] . '" name="saveForm" method="post">';
        echo '<table border="1" cellspadding="0" cellspacing="0">';
        echo '<tr><td>ID</td><td>' . $cells['id'] . '</tr>';
        echo '<tr><td>EMAIL</td><td><input type="text" maxlength="100" name="email" value="' . $cells['email'] . '"></tr>';
        echo '<tr><td>LOGIN</td><td><input type="text" name="login" maxlength="100" value="' . $cells['login'] . '"></tr>';
        echo '<tr><td>NAME</td><td><input type="text" name="name" maxlength="100" value="' . $cells['name'] . '"></tr>';
        echo '<tr><td>SURNAME</td><td><input type="text" name="surname" maxlength="100" value="' . $cells['surname'] . '"></tr>';
        echo '<tr><td>PHONE</td><td><input type="text" name="phone" maxlength="30" value="' . $cells['phone'] . '"></tr>';
        echo '<tr><td>ADDRESS</td><td><input type="text" maxlength="255" name="address" value="' . $cells['address'] . '"></tr>';
        echo '<tr><td>REGTYPE</td><td>';
        echo '<select name="regtype">';
              foreach($regTypes as $key => $value) {
                  echo "<option value='$key' " . ($key == $regType ? 'selected' : '') . ">$value [$key]</option>";
              }
        echo '</select></td></tr>';
        echo '<tr><td>REGDATE</td><td>' . $cells['regdate'] . '</tr>';
        echo '</table>';

        echo '<br><br><input type="submit" value="' . Translate::get('save_changes') . '" class="hlogin-a7e-button"></form>';

    } else {
        print('<b>A user with such data was not found.</b>');
    }
}

echo '</div>';

function standardization($value) {
    return is_null($value) || $value === '' ? null : $value;
}


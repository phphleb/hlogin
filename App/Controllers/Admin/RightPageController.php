<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers\Admin;

use Hleb\Base\Controller;
use Hleb\Constructor\Data\View;
use Hleb\Helpers\ArrayHelper;
use Hleb\Static\Request;
use Hleb\Static\Settings;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Hlogin\App\Helpers\EmailHelper;
use Phphleb\Hlogin\App\Models\UserModel;
use Phphleb\Hlogin\App\RegData;

/**
 * @internal
 */
final class RightPageController extends Controller
{
    use SettingDataTrait;

    public function index(): View
    {
        $config = ConfigStorage::getConfig();
        $lang = Settings::getAutodetectLang();
        $idUser = Request::get('id')->asString();
        if (!$idUser) {
            return view(
                'hlogin/adminzone/search',
                [
                    'config' => $config,
                    'lang' => $lang,
                    'trans' => $this->getTrans($lang),
                    'hloginData' => $this->getData(),
                    'message' => null,
                    'idUser' => null,
                ]
            );
        }
        if (\is_numeric($idUser)) {
            $data = UserModel::getCells('id', (int)$idUser);
        } else {
            $email = EmailHelper::convert($idUser);
            if (!preg_match(RegData::EMAIL_PATTERN, $email)) {
                return view(
                    'hlogin/adminzone/search',
                    [
                        'config' => $config,
                        'lang' => $lang,
                        'trans' => $this->getTrans($lang),
                        'hloginData' => $this->getData(),
                        'message' => AuthLang::trans($lang, 'data_validation_failed'),
                        'idUser' => null,
                    ]
                );
            }
            $data = UserModel::getCells('email', $email);
            if ($data) {
                $idUser = $data['id'];
            }
        }
        if (!$data) {
            return view(
                'hlogin/adminzone/search',
                [
                    'config' => $config,
                    'lang' => $lang,
                    'trans' => $this->getTrans($lang),
                    'hloginData' => $this->getData(),
                    'message' => AuthLang::trans($lang, 'user_not_found'),
                    'idUser' => $idUser,
                ]
            );
        }
        $email = $data['email'];
        $login = $data['login'];

        $regtype = (int)$data['regtype'];
        $level = AuthLang::trans($lang, 'regtype_level');
        $regtypeData = [
            -2 => AuthLang::trans($lang, 'regtype_banned'),
            -1 => AuthLang::trans($lang, 'regtype_deleted'),
            1 => AuthLang::trans($lang, 'regtype_prereg'),
            2 => AuthLang::trans($lang, 'regtype_reg'),
            3 => $level . ' 3',
            4 => $level . ' 4',
            5 => $level . ' 5',
            6 => $level . ' 6',
            7 => $level . ' 7',
            8 => $level . ' 8',
            9 => $level . ' 9',
            10 => AuthLang::trans($lang, 'regtype_admin'),
            11 => AuthLang::trans($lang, 'regtype_superadmin'),
        ];
        $regtypeList = ArrayHelper::moveFirstByValue([-2, -1, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11], $regtype);

        $confirm = (bool)$data['confirm'];
        $name = $data['name'];
        $surname = $data['surname'];
        $phone = $data['phone'];
        $address = $data['address'];
        $subscription = (bool)$data['subscription'];
        $regdate = $data['regdate'];

        return view(
            'hlogin/adminzone/rights',
            [
                'config' => $config,
                'lang' => $lang,
                'trans' => $this->getTrans($lang),
                'hloginData' => $this->getData(),
                'idUser' => $idUser,
                'email' => $email,
                'regtypeList' => $regtypeList,
                'regtypeData' => $regtypeData,
                'confirm' => $confirm,
                'name' => $name,
                'surname' => $surname,
                'phone' => $phone,
                'address' => $address,
                'subscription' => $subscription,
                'regdate' => $regdate,
                'login' => $login,
            ]
        );
    }
}
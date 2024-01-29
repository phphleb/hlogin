<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers\Admin;

use Hleb\Base\Controller;
use Hleb\Constructor\Data\View;
use Hleb\Helpers\ArrayHelper;
use Hleb\Static\Request;
use Hleb\Static\Router;
use Hleb\Static\Settings;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Hlogin\App\Helpers\UserSearchHelper;
use Phphleb\Hlogin\App\Models\UserModel;

/**
 * @internal
 */
final class UserPageController extends Controller
{
    use SettingDataTrait;

    protected const MIN_LIMIT = 20;
    protected const MAX_LIMIT = 100;

    public function index(): View
    {
        $config = ConfigStorage::getConfig();
        $lang = Settings::getAutodetectLang();

        $sortList = [];
        $page = Request::get('page')->asPositiveInt();
        $sortList['page'] = $page;
        $limit = Request::get('limit')->asPositiveInt();
        if ($limit < self::MIN_LIMIT) {
            $limit = self::MIN_LIMIT;
        }
        if ($limit > self::MAX_LIMIT) {
            $limit = self::MAX_LIMIT;
        }
        $sortList['limit'] = $limit;
        $active = Request::get('active')->asBool(true);
        $adminOnly = Request::get('admin_only')->asBool();
        $searchKey = Request::get('search')->asString();
        $searchValue = Request::get('search_value')->asString('');

        $sortId = Request::get('sort_id')->asInt();
        if ($sortId) {
            $sortList['id'] = (int)$sortId;
        }
        $sortConfirm = Request::get('sort_confirm')->asInt();
        if ($sortConfirm) {
            $sortList['confirm'] = (int)$sortConfirm;
        }
        $sortSubscription = Request::get('sort_subscription')->asInt();
        if ($sortSubscription) {
            $sortList['subscription'] = (int)$sortSubscription;
        }

        $filterOn = $this->isDefineFilter($active, $adminOnly, $searchValue);

        $linkFn = static function(int $id) use ($lang): string {
            return Router::address('hlogin.rights', ['lang' => $lang]) . '?id=' . $id;
        };
        $boolFn = static function (int|bool $value): string {
            return $value ? '&#9989;' : '-';
        };
        $strFn = static function (null|string $value): string {
            if ($value === '' || $value === null) {
                return '-';
            }
            if (mb_strlen($value) > 27) {
                return '<span class="hlogin-az-limit" title="' . $value . '">' . mb_substr($value, 0, 24) . '...</span>';
            }
            return $value;
        };
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
        $searchList = [
            'id' => AuthLang::trans($lang, 'user_id'),
            'email' => 'E-mail',
            'login' => AuthLang::trans($lang, 'login'),
            'name' => AuthLang::trans($lang, 'name'),
            'surname' => AuthLang::trans($lang, 'surname'),
            'phone' => AuthLang::trans($lang, 'phone'),
            'address' => AuthLang::trans($lang, 'address'),
            'promocode' => AuthLang::trans($lang, 'promo_code'),
            'ip' => 'Reg. IP',
        ];
        $filterList = [];
        if (empty($searchList[$searchKey]) || empty($searchValue)) {
            $searchKey = '';
        } else {
            $filterList = [$searchKey => $searchValue];
        }
        if ($searchKey) {
            $searchList = ArrayHelper::moveToFirst($searchList, $searchKey);
        }

        $count = UserModel::getCount([], $active);
        if ($count > 100_000) {
            $filterList = [];
            $countAll = $count;
        } else {
            $countAll = UserModel::getCount($filterList, $active);
        }

        $users = UserModel::getUsers($sortList, $filterList, $active, $adminOnly);

        $pages = $countAll ? (int)(ceil($countAll / $limit)) : 0;

        $page = $page < 1 ? 1 : $page;
        if ($pages < 2) {
            $page = 1;
        }
        $pgn = UserSearchHelper::getPagination($pages, $page, text: AuthLang::trans($lang, 'page'));
        $all = AuthLang::trans($lang, 'all');
        $res = count((array)$users);
        $view = AuthLang::trans($lang, 'view_text');
        $pgButtons = '';
        if ($pages > 1) {
            $pgButtons = "<div class=\"hlogin-az-btn-pagination\">$view: $res &ensp;$all: $countAll<br>$pgn</div>";
        }

        $sortBlockFn = function(string $type, int $sort = 0): string {
            return UserSearchHelper::getSortBlock($type, $sort);
        };

        return view(
            'hlogin/adminzone/users',
            [
                'config' => $config,
                'lang' => $lang,
                'trans' => $this->getTrans($lang),
                'hloginData' => $this->getData(),
                'countAll' => $countAll,
                'pages' => $pages,
                'users' => $users,
                'linkFn' => $linkFn,
                'boolFn' => $boolFn,
                'regtypeData' => $regtypeData,
                'activeUser' => $active,
                'adminOnly' => $adminOnly,
                'searchList' => $searchList,
                'searchValue' => $searchValue,
                'strFn' => $strFn,
                'pageButtons' => $pgButtons,
                'filterOn' => $filterOn,
                'sortBlockFn' => $sortBlockFn,
                'sortId' => $sortId,
                'sortConfirm' => $sortConfirm,
                'sortSubscription' => $sortSubscription,
                'page' => $page,
            ]
        );
    }

    private function isDefineFilter(bool $active, bool $adminOnly, string|null $searchValue): bool
    {
        return !$active|| $adminOnly || $searchValue;
    }
}
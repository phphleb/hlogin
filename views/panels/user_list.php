<?php

use Phphleb\Adminpan\Add\AdminPanData;
use Phphleb\Adminpan\MainAdminPanel;
use \Hleb\Constructor\Handlers\Request;
use Phphleb\Hlogin\App\Main as Main;
use Phphleb\Hlogin\App\Translate;

$page = Request::getGetInt('page', 1);
$limit = Request::getGetInt('limit', 50);
$filters = json_decode(Request::getGetString('filter', '{}'), true);
$request = Request::getGet() ?? [];

$page = isset($request['clear']) ? 1 : $page;

unset($request['clear']);

echo addFilter('1', $filters['1'] ?? []);

echo addFilter('2', $filters['2'] ?? []);

echo addFilter('3', $filters['3'] ?? []);

echo addFilterButton();

$countAllRows = Phphleb\Hlogin\App\HloginUserModel::getCount($filters);

$buttons = getButtons($page, $limit, $countAllRows, $request);

echo $buttons;

$data = Phphleb\Hlogin\App\HloginUserModel::getUsers($request, $filters);
$list = [];
foreach($data as $user) {
   $list[] = [
       createCell(addFilters('Id', 'id')) => '<b>' . $user['id'] . '</b>',
       createCell('E-mail') => linkToUserInfo($user['email'], $user['id']),
       createCell(addFilters('Type', 'regtype')) => regtypeCell($user['regtype']),
       createCell(addFilters('Confirm', 'confirm')) => checkBoolCell($user['confirm']),
       createCell('Login') => checkCell($user['login']),
       createCell('Name') => checkCell($user['name']),
       createCell('Surname') => checkCell($user['surname']),
       createCell('Phone') => checkCell($user['phone']),
       createCell('Address') => checkCell($user['address']),
       createCell('Promocode') => checkCell($user['promocode']),
       createCell('Register IP') => checkCell($user['ip']),
       createCell(addFilters('Subscription', 'subscription')) => checkBoolCell($user['subscription']),
       createCell('Register date') => checkCell($user['regdate'])
   ];
}

echo (new MainAdminPanel())->getDataTable($list);

echo $buttons;


echo "<hr><span class='hlogin-adm-statistic'><b>" . Translate::get('all') . ": {$countAllRows}</b></span><br>";

echo "<div class='hl-over-fon' align='center'><h3>" . Translate::get('waiting') . "</h3></div>";

function createCell($cell) {
    return "<span class='hlogin-adm-table-cell'>{$cell}</span>";
}


function addFilters($name, $cell) {
    return "<b>{$name}</b> <span>" . addFilterButtons($cell, 1)  . addFilterButtons($cell, 0) . "</span> ";
}

function linkToUserInfo($email, $id) {
    return "<a href='/" . AdminPanData::getLang() . "/adminzone/registration/rights/?userid={$id}' class='hlogin-adm-link'>{$email}</a>";
}

function addFilterButtons($cell, int $sortType = 1) {
    $list = Request::getGet();
    $valueName = $cell . '_sort';
    $select = isset($list[$valueName]) && $list[$valueName] == $sortType;
    $icon = $sortType ? '&#9650;' : '&#9660;';
    if($select) {
        unset($list[$valueName]);
        return "<a href=\"" . Request::getMainClearUrl() . "?clear=1&" . http_build_query($list) . "\" ><button class='hl-cell-btn-select'>{$icon}</button></a>";
    }
    $list[$valueName] = $sortType;
    return "<a href=\"" . Request::getMainClearUrl() . "?clear=1&" . http_build_query($list) . "\" ><button class='hl-cell-btn'>{$icon}</button></a>";
}


function checkCell($value) {
    return empty($value) ? '<div align="center">-</div>': $value;
}

function regtypeCell(int $value) {
    if($value == -1) {
        return '<span class="hlogin-adm-cell hlogin-adm-banned">banned</span>';
    }
    if($value == 0) {
        return '<div align="center">-</div>';
    }
    if($value == 1) {
        return '<span class="hlogin-adm-cell hlogin-adm-register1">pre-reg</span>';
    }
    if($value == 2) {
        return '<span class="hlogin-adm-cell hlogin-adm-register2">registered</span>';
    }
    if($value == 10) {
        return '<span class="hlogin-adm-cell hlogin-adm-admin">admin</span>';
    }
    if($value == 11) {
        return '<span class="hlogin-adm-cell hlogin-adm-superadmin">superadmin</span>';
    }
      return 'register level ' . ($value - 2);
}

function checkBoolCell($value) {
    return '<div align="center">' . (!empty($value) ? '<img src="/en/login/resource/' . Main::getVersion() . '/light/svg/svg/checkbox-dynamic-on/" width="12" height="12">' : '-' ) . '</div>';
}

function getButtons($page, $limit, $count, $filters = []) {
    return (new MainAdminPanel())->getNumericPageBtns($page, $limit, Request::getMainClearUrl(), $count, $filters, \Phphleb\Hlogin\App\Translate::get('page'));
}

function addFilter(int $id, array $data) {
    $inputKey = $data['name'] ?? null;
    $listName = ['Id' => 'id', 'Type' => 'regtype', 'E-mail' => 'email', 'Register date' => 'regdate', 'Phone' => 'phone', 'Confirm' => 'confirm'];
    $result = '<div class="hlogin-filter-over"><select class="hlogin-select-filter hl-filter-' . $id . '" name="name" value=""><option value=""> </option>';
    foreach ($listName as $key => $value) {
        $result .= "<option value='{$value}' " . ($inputKey === $value ? 'selected' : '' ) . ">{$key}</option>";
    }
    $result .= '</select>';

    $inputSelector = $data['selector'] ?? '';
    $listSelector = ['1' => '=', '2' => '>', '3' => '<', '4' => '>=', '5' => '<=', '6' => '!=', '7' => '%...%'];
    $result .= '<select class="hlogin-select-filter hl-filter-' . $id . '" name="selector" value="">';
    foreach ($listSelector as $key => $value) {
        $result .= "<option value='{$key}' " . ($inputSelector == $key ? 'selected' : '' ) . ">{$value}</option>";
    }
    $result .= '</select>';

    $inputValue = $data['value'] ?? '';
    $result .= '<input type="text" class="hlogin-select-filter hl-filter-' . $id . '" name="value" style="max-width: 80px;background-color:' . (trim($inputValue) == '' ? '' : 'lightblue')  . '" value="' . $inputValue . '">';

    $result .= '</div>';
    return $result;
}

function addFilterButton() {
    return '<input class="hlogin-select-filter" type="button" value="' . Translate::get('add_filter') . '" onclick="mHLogin.adminzone.hloginFilterSend()">';
}

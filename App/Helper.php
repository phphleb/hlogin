<?php


namespace Phphleb\Hlogin\App;


use Hleb\Constructor\Handlers\Key;

final class Helper
{
    public static function convertEmail($email) {
        if (!is_string($email)) {
            return null;
        }
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return null;
        }
        return $parts[0] . '@' . strtolower($parts[1]);
    }

    static public function getProtectedHashGenerator() {
        try {
            $keygen = str_shuffle(md5(strval(random_int(100, 1000))) . md5(bin2hex(random_bytes(30))));
        } catch (\Exception $ex) {
            $keygen = str_shuffle(md5(rand() . Key::get()) . md5(rand()));
        }
        return $keygen;
    }

    static public function createDynamicHash(int $id, string $email, string $date = '') {
        if(empty($date)) {
            $date = date('Y-m-d');
        }
        return md5($id . Key::get() . $date . $email);
    }

    static public function checkDynamicHash(int $id, string $email, string $hash) {
        $dateList = [
            date('Y-m-d'),
            date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"))),
            date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") - 1,   date("Y")))
        ];

        foreach ($dateList as $date) {
            if($hash === self::createDynamicHash($id, $email, $date)) {
                return true;
            }
        }
        return false;
    }

}


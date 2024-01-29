<?php

namespace Phphleb\Hlogin\App;

class RegData
{
    public const API_VERSION = '2';

    public const EMAIL_PATTERN = '/^[-_+\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,10}$/';

    public const DOMAIN_PATTERN = '/^([A-z0-9][-A-z0-9]+\.)+[A-z]{2,10}$/';

    public const PASSWORD_PATTERN = '/^[a-zA-Z0-9]{6,}$/';

    public const CAPTCHA_PATTERN = '/^[a-zA-Z0-9]{5,6}$/';

    public const NAME_PATTERN = '/^[\w\._\-\s\№\?\@\:\$\+\!\;]{0,150}$/iu';

    public const MESSAGE_PATTERN = '/^[^<>]{5,10000}$/';

    public const HIDDEN_PATTERNS = ['blank'];

    public const PREVIOUS_PAGE_SESSION_NAME = 'HLOGIN_PREVIOUS_PAGE';

    public const USER_TABLE_CELLS = [
        'id',
        'regtype',
        'confirm',
        'email',
        'newemail',
        'login',
        'password',
        'name',
        'surname',
        'phone',
        'address',
        'promocode',
        'ip',
        'subscription',
        'period',
        'regdate',
        'hash',
        'code',
        'sessionkey',
    ];

}
<?php
/**
 * The returned array contains a list of pages for making an interactive menu from them.
 * Each page must match the route by name.
 *
 * Возвращаемый массив содержит перечень страниц для составления из них интерактивного меню.
 * Каждая страница должна содержать соответствие с маршрутом по названию.
 *
 * @see @/vendor/phphleb/adminpan/match-directory/structure/adminpan.php
 */


return [
    'design' => 'base', // base|light... default `base`
    'logoUri' => null, // Relative link to the PNG, JPG or SVG (230x55px)
    'breadcrumbs' => 'on', // on|off default 'on'
    'section' => [
        [
            'link' => '/',
            'name' => [
                'en' => 'Main Page',
                'ru' => 'Главная страница',
                'de' => 'Startseite',
                'es' => 'Página inicial',
                'zh' => '首页'
            ],
        ],
        [
            'name' => [
                'en' => 'Users',
                'ru' => 'Пользователи',
                'de' => 'Benutzer/innen',
                'es' => 'Usuarios',
                'zh' => '用户'
            ],
            'section' => [
                [
                    'route' => 'hlogin.users',
                    'name' => [
                        'en' => 'List',
                        'ru' => 'Список',
                        'de' => 'Liste',
                        'es' => 'Lista',
                        'zh' => '列表'
                    ],
                ],
                [
                    'route' => 'hlogin.rights',
                    'name' => [
                        'en' => 'Management',
                        'ru' => 'Управление',
                        'de' => 'Steuerung',
                        'es' => 'Manejo',
                        'zh' => '管理'
                    ],
                ],
            ]
        ],
        [
            'name' => [
                'en' => 'Settings',
                'ru' => 'Настройки',
                'de' => 'Einstellungen',
                'es' => 'Configuraciones',
                'zh' => '设置'
            ],
            'section' => [
                [
                    'route' => 'hlogin.settings',
                    'name' => [
                        'en' => 'Registration',
                        'ru' => 'Регистрация',
                        'de' => 'Anmeldung',
                        'es' => 'Registración',
                        'zh' => '注册'
                    ],
                ],
                [
                    'route' => 'hlogin.captcha',
                    'name' => [
                        'en' => 'Captcha',
                        'ru' => 'Капча',
                        'de' => 'CAАPTCHA',
                        'es' => 'Captcha',
                        'zh' => '验证码'
                    ],
                ],
                [
                    'route' => 'hlogin.email',
                    'name' => [
                        'en' => 'E-mail',
                        'ru' => 'Электронная почта',
                        'de' => 'E-Mail',
                        'es' => 'Correo electrónico',
                        'zh' => '电子邮箱'
                    ],
                ],
                [
                    'route' => 'hlogin.contact',
                    'name' => [
                        'en' => 'Feedback',
                        'ru' => 'Обратная связь',
                        'de' => 'Feedback',
                        'es' => 'Comentarios',
                        'zh' => '反馈'
                    ],
                ],
                [
                    'route' => 'hlogin.additional',
                    'name' => [
                        'en' => 'Additionally',
                        'ru' => 'Дополнительно',
                        'de' => 'Zusätzlich',
                        'es' => 'Además',
                        'zh' => '此外'
                    ],
                ],
            ]
        ],
        [
            'route' => 'hlogin.profile',
            'name' => [
                'en' => 'Profile',
                'ru' => 'Профиль',
                'de' => 'Profil',
                'es' => 'Perfil',
                'zh' => '账户'
            ],
        ],
    ],
];

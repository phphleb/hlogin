console.log(
    '%c Phphleb/Hlogin %c v2 ',
    "color:#fff; background: #3f9adb; padding: 2px",
    "color:#000; background:#ccc; padding: 2px"
);
if (typeof hlogin === 'undefined') hlogin = {};
if (typeof hlogin.script === 'undefined') hlogin.script = {
    config: null,
    version: null,
    defaultDesign: null,
    design: null,
    I: '',
    defaultLang: null,
    lang: null,
    languages: [],
    loadedJs: [],
    panelLoaded: false,
    loadProcess: false,
    hasPreloader: false,
    csrfToken: null,
    // Enumeration of actions for panels only.
    // Перечисление действий только для панелей.
    popups: [
        // Panel for user login.
        // Панель для пользовательского входа.
        'UserEnter',
        // Password recovery panel.
        // Панель для восстановления пароля.
        'UserPassword',
        // Panel with user profile data.
        // Панель с данными профиля пользователя.
        'UserProfile',
        // User registration panel.
        // Панель регистрации пользователя.
        'UserRegister',
        // Panel for feedback.
        // Панель для обратной связи.
        'ContactMessage',
        // Output a text message.
        // Вывод текстового сообщения.
        'CustomMessage',
        // Display the password recovery panel (via link).
        // Вывод панели восстановления пароля (по ссылке).
        'NewPassword',
        // Panel for recovering E-mail.
        // Панель для восстановления E-mail.
        'ConfirmEmail',
    ],
    // Enumeration of actions for which no preloader is needed.
    // Перечисление действий для которых не нужен прелоадер.
    actions: [
        // Changing the current design, requires specifying the type of design.
        // Смена текущего дизайна, требует указание типа дизайна.
        'ChangeDesign',
        // Roll back the current design to the original one.
        // Откат текущего дизайна к исходному.
        'DefaultDesign',
        // Changing the current language, requires specifying the type of translation.
        // Смена текущего языка, требует указание типа перевода.
        'ChangeLang',
        // Roll back the current language to the original one.
        // Откат текущего языка к исходному.
        'DefaultLang',
        // Close all open registration windows.
        // Закрытие всех открытых окон регистрации.
        'CloseAllPopups',
        // Refresh the page.
        // Обновить страницу.
        'ReloadPage',
        // Updates the captcha in the panel.
        // Обновляет капчу в панели.
        'ReloadCaptcha',
        // Redirect to the specified additional page.
        // Редирект на указанную дополнительно страницу.
        'RedirectToPage',
        // Update user profile data.
        // Обновление данных профиля пользователя.
        'UserProfileData',
        // Redirect to log out and cancel authorization.
        // Редирект на выход и отмену авторизации.
        'UserExit',
        // Redirect to the admin panel.
        // Редирект в административную панель.
        'AdminzoneEnter',
        // Exit on all devices.
        // Выход на всех устройствах.
        'UserFullExit',
        // Redirect to the main page.
        // Редирект на главную страницу.
        'ToHomepage',
        // Resend the registration letter.
        // Повторная отправка регистрационного письма.
        'RegisterEmail',
        'CustomEmailMessage',
    ],
    register: function () {
        const th = this;
        var intervalId = setInterval(function () {
            if (document.body !== null) {
                clearInterval(intervalId);
                th.stateOnload();
            }
        }, 20);
    },
    stateOnload: function () {
        var p  = document.getElementById('JsWarning');
        if (p) {
            p.outerHTML = '';
        }
        var d = document.getElementById('hlogin_init_script');
        var config = JSON.parse(d.dataset.config.replace(/&apos;/, '\''));
        this.I = config.endingUrl ? '/' : '';
        this.lang = config.lang;
        this.csrfToken = config.csrfToken;
        this.defaultLang = config.lang;
        this.setLang();
        this.languages = config.languages;
        // Initialization of this variable is always at the end of configuration parsing.
        // Инициализация этой переменной всегда в конце разбора конфигурации.
        this.version = config.config.version;
        this.design = config.config.design;
        this.defaultDesign = config.config.design;
        this.config = config.config;
        d.outerHTML = '';

        this.initDemoSelectors();
        this.initPopupActions(document.body);
        var th = this;
        setInterval(function() {
            th.initPopupActions(document.body);
        }, 500);
        setInterval(function() {
            th.initPanelScrollAction();
        }, 20);
        // Intentional duplication of the base pattern may occur.
        // Может возникать преднамеренное дублирование шаблона base.
        this.loadCss('hloginstyle' + this.design);

        if (this.config.startCommand) {
            this.runAction(this.config.startCommand);
        }
    },
    loadJs: function (name) {
        if (this.loadedJs.indexOf(name) === -1) {
            var script = document.createElement('script');
            script.src = '/hlresource/hlogin/v' + hlogin.script.version + '/js/' + name + this.I;
            script.async = true;
            script.type = 'text/javascript';
            document.body.appendChild(script);
            this.loadedJs.push(name);
        }
    },
    loadCss: function (name, before) {
        var css = document.createElement('link');
        css.rel = "stylesheet";
        css.href = '/hlresource/hlogin/v' + hlogin.script.version + '/css/' + name + this.I;
        if(typeof before === 'undefined') {
            document.head.appendChild(css);
        } else {
            document.head.insertBefore(css, before);
        }
    },
    els: function (val) { // simple dynamic value
        var q = val.substring(1);
        if (val.charAt(0) === "." && q.match(/^[a-z0-9\-\_]+$/i)) {
            return document.getElementsByClassName(q);
        }
        return document.getElementsByTagName(val);
    },
    setLang: function () {
        var html = this.els('html')[0];
        if (html != null) {
            var langParts = html.getAttribute("lang");
            if (langParts) {
                var lang = this.trim(langParts.toLowerCase().split('-')[0]);
                this.lang = this.languages.indexOf(lang) !== -1 && lang.length == 2 ? lang : this.lang;
                this.defaultLang = this.lang;
                return;
            }
        }
        var urlParts = document.location.pathname.split("/");
        if (urlParts.length > 1 && this.languages.indexOf(urlParts[1].toLowerCase()) !== -1) {
            this.lang = urlParts[1].toLowerCase();
            this.defaultLang = this.lang;
            return;
        }
    },
    trim: function (str, chars) {
        return this.ltrim(this.rtrim(str, chars), chars);
    },
    ltrim: function (str, chars) {
        chars = chars || "\\s";
        return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
    },
    rtrim: function (str, chars) {
        chars = chars || "\\s";
        return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
    },
    /**
     * Performing actions with the initialization of panels.
     * For example:
     *
     * Выполнение действий с инициализацией панелей.
     * Например:
     *
     * hlogin.script.runAction('UserEnter');
     *
     * hlogin.script.runAction('ChangeDesign', 'dark');
     *
     * hlogin.script.runAction('ChangeLang', 'en');
     *
     * @param {string} type
     * @param {?string} value
     */
    runAction: function(type, value, title) {
        if (this.loadProcess) {
            return;
        }
        const th = this;
        this.loadProcess = true;
        if (this.panelLoaded) {
            if (th.popups.indexOf(type) !== -1) {
                th.openSubstrate(true);
            }
            setTimeout( function() {
                hlogin.panel.actionHandler(type, value, title);
                th.loadProcess = false;
            }, 100);
        } else {
            if (th.popups.indexOf(type) !== -1){
                th.openSubstrate(true);
            }
            this.loadJs('hloginpanel');
            this.loadJs('hloginlang' + th.lang);
            var intervalId = setInterval(function () {
                if (typeof hlogin.panel !== 'undefined') {
                    clearInterval(intervalId);
                    hlogin.panel.actionHandler(type, value, title);
                    th.loadProcess = false;
                    th.panelLoaded = true;
                }
            }, 20);
        }
    },
    openSubstrate: function(closed) {
        var l = document.getElementById('hlogin-substrate')
        if (l) {
            l.style.display = 'block';
            return;
        }
        var l = document.createElement('div');
        l.id = 'hlogin-substrate';
        l.style = 'background-color:#dcd9d9;opacity:0.001;z-index:2147483647;position:absolute;top:0;left:0;width:100%;height:100%'
        document.body.appendChild(l);
        var timeout = setTimeout(
            function () {
                var b = document.getElementById('hlogin-substrate');
                if (b) {
                    b.style.opacity = '0.4';
                }
            }, 500);
        this.hasPreloader = true;
        var th = this;
        if (closed) {
            l.addEventListener('click', function (e) {
                clearTimeout(timeout);
                th.clearSubstrate();
            });
        }
        setTimeout(function(){
            clearTimeout(timeout);
            th.clearSubstrate();
        }, 7000);
    },

    clearSubstrate: function() {
        var b = document.getElementById('hlogin-substrate');
        if (b) {
            b.outerHTML = '';
            this.hasPreloader = false;
        }
    },

    clearPassword: function() {
        document.querySelectorAll('.hlogin-over-panel input.hlogin-password-cell').forEach(
            function(el) {
                el.value = '';
            }
        );
    },

    /**
     * Initialization of opening an arbitrary window in nested blocks.
     * To activate, the block must have the class 'hlogin-init-action' and the data-type of the action.
     * The optional data-value parameter is required for some types of actions.
     * If the block is generated dynamically, then it must be passed to this function in order to be activated.
     * For example:
     *
     * Инициализация открытия произвольного окна во вложенных блоках.
     * Для активации у блока должен быть класс 'hlogin-init-action' и data-type действия.
     * Необязательный параметр data-value необходим для некоторых типов действий.
     * Если блок генерируется динамически, то его нужно передать в эту функцию, чтобы активировать.
     * Например:
     *
     * <button class="hlogin-init-action" data-type="UserRegister">Open registration popup.</button>
     *
     * <button class="hlogin-init-action" data-type="ChangeDesign" data-value="special">
     *     Version for the visually impaired.
     * </button>
     *
     * @param {object} obj - a new object to initialize internal actions.
     *                     - новый объект для инициализации внутренних действий.
     */
    initPopupActions: function(obj) {
        var th = this;
        obj.querySelectorAll('.hlogin-init-action:not([data-active="on"])').forEach(
            function (e) {
                var a = e.dataset.active === 'on';
                if (a) {
                    return;
                }
                e.dataset.active = 'on';
                e.addEventListener('click', function() {
                    if (e.dataset.type) {
                        th.runAction(e.dataset.type, e.dataset.value, e.dataset.title);
                    }
                });
            }
        );
    },

    initPanelScrollAction: function() {
        var el = document.querySelector('.hlogin-outer-x');
        var pan = document.querySelector('.hlogin-frame-over');
        var over = document.querySelector('.hlogin-over-panel');
        if (el && pan) {
            if (pan.scrollHeight > pan.clientHeight) {
                el.classList.add('hlogin-pan-scroll');
                over.classList.add('hlogin-pan-scroll-over');
            } else {
                el.classList.remove('hlogin-pan-scroll');
            }
        }
    },

    /**
     * Assigning handlers to links for the demo page.
     *
     * Назначение обработчиков ссылкам для демо-страницы.
     */
    initDemoSelectors: function() {
        if (!document.getElementById('hlogin-path-selector')) {
            return;
        }
        document.getElementById('hlogin-path-selector').addEventListener('change', function (e) {
            document.getElementById('hlogin-path-link').href = '/' + e.target.value + '/login/profile/';
            hlogin.script.runAction('ChangeLang', e.target.value);
        });
        document.getElementById('hlogin-select-action').addEventListener('change', function (e) {
            hlogin.script.runAction('ChangeDesign', e.target.value);
        });
    },
}
hlogin.script.register();

/**
 * Changing the design type for panels.
 *
 * Смена типа дизайна для панелей.
 *
 * @param {string} design
 */
function hloginSetDesignToPopups(design) {
    hlogin.script.runAction('ChangeDesign', design);
}

/**
 * Returns the design view to its original value.
 *
 * Возвращает вид дизайна к изначальному значению.
 */
function hloginRevertDesignToPopups() {
    hlogin.script.runAction('DefaultLang');
}

/**
 * Closes all open registration system windows.
 *
 * Закрытие всех открытых окон системы регистрации.
 */
function hloginCloseAllPopups() {
    hlogin.script.runAction('CloseAllPopups');
}

/**
 * Opening a popup by its name.
 *
 * Открытие окна по его названию.
 *
 * @param type - 'UserRegister' / 'UserEnter' / 'UserPassword' / 'ContactMessage'
 */
function hloginVariableOpenPopup(type) {
    hlogin.script.runAction(type);
}

/**
 * Opening a panel with custom message text.
 *
 * Открытие панели с кастомным текстом сообщения.
 */
function hloginOpenMessage(title, content) {
    hlogin.script.runAction('CustomMessage', content, title);
}
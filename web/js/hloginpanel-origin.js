if (typeof hlogin === 'undefined') hlogin = {};
if (typeof hlogin.panel === 'undefined') hlogin.panel = {
    loadMainStyle: false,
    loadDesignStyle: false,
    loadedMain: false,
    timer: null,
    timerId: null,
    profileData: [],
    sendingData: false,
    /**
     * Accepts parameters to perform an action.
     *
     * Принимает параметры для выполнения действия.
     *
     * @param {string} type
     * @param {?string} value
     * @param {?string} id - email
     */
    actionHandler: function (type, value, id) {
        if (!type || hlogin.script.popups.concat(hlogin.script.actions).indexOf(type) === -1) {
            console.error('Invalid type=' + type + ' parameter for action call.');
            return;
        }
        if (value && ['ChangeDesign', 'ChangeLang', 'RedirectToPage', 'CustomMessage', 'CustomEmailMessage', 'ConfirmEmail'].indexOf(type) === -1) {
            console.error('Invalid type=' + type + ' value=' + value + ' parameter for action call.');
            return;
        }
        var preloader = document.getElementById('hlogin-substrate');
        if (preloader) {
            preloader.outerHTML = '';
        }
        if (!this.loadMainStyle) {
            hlogin.script.loadCss('hloginstylemain');
            this.loadMainStyle = true;
            var сh = document.createElement('span');
            сh.id = 'hlogin-check-main-css';
            document.body.appendChild(сh);
        }
        if (!this.loadDesignStyle) {
            hlogin.script.loadCss('hloginstyle' + hlogin.script.design);
            this.loadDesignStyle = true;
            var сh = document.createElement('span');
            сh.id = 'hlogin-check-design-css';
            document.body.appendChild(сh);
        }
        var th = this;
        var intervalId = setInterval(function () {
            var check1 = document.getElementById('hlogin-check-main-css');
            var check2 = document.getElementById('hlogin-check-design-css');
            if (typeof hlogin.i18n !== 'undefined' &&
                th.searchLang(hlogin.script.lang) &&
                th.searchDesign(hlogin.script.design) &&
                (th.loadMainStyle || check1.offsetWidth > 20) &&
                (th.loadDesignStyle || check2.offsetWidth > 20)
            ) {
                clearInterval(intervalId);
                if (check1) check1.outerHTML = '';
                if (check2) check2.outerHTML = '';
                th.internalActionHandler(type, value, id);
            }
        }, 20);
    },
    clear: function(text) {
        return text.replace(/<\/?[^>]+>/g, '').replace(/\"/g, '').replace(/\'/g, '');
    },
    internalActionHandler: function (type, value, id) {
        switch (type) {
            case 'DefaultLang':
                this.changeLang(hlogin.script.defaultLang)
                break;
            case 'ChangeLang':
                this.changeLang(value);
                break;
            case 'DefaultDesign':
                this.changeDesign(hlogin.script.defaultDesign)
                break;
            case 'ChangeDesign':
                this.changeDesign(value);
                this.sendAjaxRequest({method: 'ChangeDesign', value: value, lang: hlogin.script.lang});
                break;
            case 'CloseAllPopups':
                this.closeAllPopups(value);
                break;
            case 'RedirectToPage':
                location.assign(value);
                break;
            case 'RegisterEmail':
                this.sendAjaxRequest({method: 'RegisterEmail', value: value, lang: hlogin.script.lang});
                break;
            case 'CustomEmailMessage':
                this.createPopup(this.getCustomEmailContent(id, value), false, type);
                break;
            case 'ToHomepage':
                location.assign(this.convertLink(hlogin.script.config.registration['homepage-redirect']));
                break;
            case 'ReloadPage':
                location.reload(true);
                break;
            case 'ReloadCaptcha':
                this.updCaptchaImage();
                break;
            case 'UserEnter':
                this.createPopup(this.getUserEnterContent(id), false, type);
                break;
            case 'UserPassword':
                this.createPopup(this.getUserPasswordContent(id), false, type);
                break;
            case 'NewPassword':
                this.createPopup(this.getNewPasswordContent(id), false, type);
                break;
            case 'ConfirmEmail':
                this.createPopup(this.getConfirmEmailContent(), false, type);
                if (value === 'test') {
                    this.createPopup(this.getCustomContent(this.getI18n('email_confirm_header'), this.getI18n('message_log_success')), false, type);
                    break;
                }
                this.sendAjaxRequest({method: 'ConfirmEmail', value: value, lang: hlogin.script.lang});
                break;
            case 'UserRegister':
                this.createPopup(this.getUserRegisterContent(id), true, type);
                break;
            case 'UserProfile':
                this.createPopup(this.getUserProfileContent(id), true, type);
                this.internalActionHandler('UserProfileData', id);
                break;
            case 'ContactMessage':
                this.createPopup(this.getContactMessageContent(id), true, type);
                break;
            case 'CustomMessage':
                this.createPopup(this.getCustomContent(id, value), false, type);
                break;
            case 'UserProfileData':
                this.sendAjaxRequest({method: 'UserProfileData', value: value, lang: hlogin.script.lang});
                break;
            case 'AdminzoneEnter':
                location.assign('/' + hlogin.script.lang + '/adminzone/registration/settings' + hlogin.script.I);
                break;
            case 'UserExit':
                location.assign('/' + hlogin.script.lang + '/login/action/exit' + hlogin.script.I + '?_token=' + hlogin.script.csrfToken)
                break;
            case 'UserFullExit':
                location.assign('/' + hlogin.script.lang + '/login/action/exit/forced' + hlogin.script.I + '?_token=' + hlogin.script.csrfToken)
                break;
            default:
                this.createPopup(type, false, type);
        }
        this.setFullScrinPanel();
    },
    searchLang: function (value) {
        return document.body.querySelector('script[src*="/js/hloginlang' + value + '"]') !== null;
    },
    changeLang: function (value) {
        hlogin.script.loadJs('hloginlang' + value);
        hlogin.script.lang = value;
        if (typeof hlogin.button !== 'undefined') {
            hlogin.button.reloadButtons();
            hlogin.button.stateOnload();
        }
    },
    searchDesign: function (value) {
        return document.head.querySelector('link[href*="/css/hloginstyle' + value + '"]') !== null;
    },
    changeDesign: function (value) {
        var previous = document.head.querySelectorAll('link[href*="/css/hloginstyle"]').forEach(
            function (e) {
                if (!e.href.includes('/css/hloginstylemain') &&
                    !e.href.includes('/css/hloginstylebutton')
                ) {
                    e.outerHTML = '';
                }
            }
        );
        if (!this.searchDesign(value)) {
            hlogin.script.loadCss('hloginstyle' + value);
            hlogin.script.design = value;
            if (typeof hlogin.button !== "undefined") {
                hlogin.button.reloadButtons();
                hlogin.button.loadButtons();
            }
        }
    },
    /**
     * Delete all open registration windows.
     *
     * Удаление всех открытых окон регистрации.
     */
    closeAllPopups: function () {
        var popups = document.querySelectorAll('.hlogin-over-panel');
        popups.forEach(
            function (e) {
                e.outerHTML = '';
            }
        )
        document.body.classList.remove('hlogin-pan-trim-target-for-open-popup');

        return popups.length;
    },
    /**
     * Forms a general frame for the panel output.
     *
     * Формирует общий каркас для вывода панели.
     *
     * @param {string} content - the contents of the panel.
     *                         - содержимое панели.
     *
     * @param {boolean} h     - full-height panel.
     *                         - полноразмерная по высоте панель.
     *
     * @param {string} type
     */
    createPopup(content, h, type) {
        if (hlogin.script.design === 'special' || hlogin.script.design === 'game') {
            h = true;
        }
        var closed = this.closeAllPopups();
        var div = document.createElement('div');
        div.classList.add('hlogin-over-panel');
        div.setAttribute('data-lang', hlogin.script.lang);
        div.setAttribute('data-design', hlogin.script.design);
        div.setAttribute('data-type', type);
        var hClass = h ? ' hlogin-pan-full-height' : '';
        div.innerHTML = (h ? '' : '<div class="hlogin-pan-top hlogin-init-action" data-type="CloseAllPopups"><div class="hlogin-blob"> </div></div>') +
            '<div class="hlogin-pan-center">' +
            '<div class="hlogin-pan-left hlogin-init-action" data-type="CloseAllPopups"><div class="hlogin-blob"> </div></div>' +
            '<div class="hlogin-panel-content">' +
            '<div class="hlogin-blob hlogin-init-action" data-type="CloseAllPopups"> </div>' +
            '<div class="hlogin-over-content' + hClass + '">' + content + '</div>' +
            '</div>' +
            '<div class="hlogin-pan-right hlogin-init-action" data-type="CloseAllPopups"><div class="hlogin-blob"> </div></div>' +
            '</div>' +
            (h ? '' : '<div class="hlogin-pan-bottom hlogin-init-action" data-type="CloseAllPopups"><div class="hlogin-blob"> </div></div>');
        div.style.opacity = '0.01';
        document.body.appendChild(div);
        if (closed == 0) {
            setTimeout(function () {
                div.style.opacity = '1';
            }, 250);
        } else {
            div.style.opacity = '1';
        }
        hlogin.script.initPopupActions(div);
        hlogin.panel.initPopupContentActions(div);
        document.body.classList.add('hlogin-pan-trim-target-for-open-popup');
        this.initFocusInFirstCell(div);
        if (typeof (hloginPopupVariableFunction) === "function") {
            hloginPopupVariableFunction(type);
        }
    },

    /**
     * Adds focus to the first field of the window.
     *
     * Добавляет фокус к первому полю окна.
     */
    initFocusInFirstCell: function(obj) {
        var cell = obj.querySelector('input');
        if (cell) {
            cell.focus();
        }
    },

    /**
     * Sending data to the server.
     *
     * Отправка данных на сервер.
     *
     * @param {object} data
     */
    sendAjaxRequest: function (data) {
        if (this.sendingData) {
            return;
        }
        this.sendingData = true;
        if (!this.loadedMain) {
            hlogin.script.loadJs('hloginmain');
            this.loadedMain = true;
        }
        var intervalId = setInterval(function () {
            if (typeof hlogin.main !== 'undefined') {
                clearInterval(intervalId);
                hlogin.script.openSubstrate();
                hlogin.main.sendAjaxRequest(data);
            }
        }, 20);
    },

    getI18n: function (tag) {
        return hlogin.i18n[hlogin.script.lang].get(tag);
    },

    /**
     * Duplicate title required for some designs.
     *
     * Дубликат заголовка, необходим для некоторых дизайнов.
     *
     * @param {string} title
     * @returns {string}
     */
    getPanTitle: function (title) {
        return '<div class="hlogin-content-title">' + title + '<span class="hlogin-init-action" data-type="CloseAllPopups">[X]</span></div>'
    },

    /**
     * Wrapper for the contents of all panels.
     *
     * Обёртка для содержимого всех панелей.
     *
     * @param {string} name   - system name of the current window (label).
     *                        - системное название текущего окна (метка).
     *
     * @param {string} title - window title..
     *                       - заголовок окна.
     *
     * @param {string} content - HTML content of the window.
     *                          - HTML-контент окна.
     *
     * @param {string|null} link - alternative name of a link to another popup.
     *                           - альтернативная название ссылки на другое окно.
     *
     * @param {string|null} type - name of the alternative window type.
     *                           - название типа альтернативного окна.
     * @returns {string}
     */
    wrapInRegFrame: function (name, title, content, link, type) {
        var alternativeLink = '';
        var x = hlogin.script.config.startCommand;
        if (link && type && !hlogin.script.config.registration['enter-only']) {
            var enter = type === 'UserExit' ? ' hlogin-pan-exit-title' : '';
            alternativeLink = ' <button tabindex="1" class="hlogin-alternative-link hlogin-init-action ' + enter + '" data-type="' + type + '" alt="' + this.clear(link) + '">' + link + '</button>';
        }
        return '<div class="hlogin-frame-over">' +
            '<div class="hlogin-frame-edge"> </div>' +
            '<div class="hlogin-frame-content">' +
            '<div class="hlogin-frame-internal">' +
            '<span class="hlogin-content-header">' +
            '<span class="hlogin-title-name">' + title + '</span> ' +
            alternativeLink +
            '</span>' + (x ? '' : '<button class="hlogin-init-action hlogin-inner-x" data-type="CloseAllPopups" tabindex="1" alt="' + this.clear(this.getI18n('close')) + '">X</button>') + content + this.getFooterLinks(name) + '</div></div>' +
            '<div class="hlogin-frame-edge">' + (x ? '' : '<button class="hlogin-init-action hlogin-outer-x" tabindex="1" data-type="CloseAllPopups" alt="' + this.clear(this.getI18n('close')) + '">X</button>') + '</div>' +
            '</div>';
    },

    /**
     * Returns the button code based on conditions.
     *
     * Возвращает код кнопки по условиям.
     *
     * @param {string} name - text on the button.
     *                      - текст на кнопке.
     *
     * @param action - system event label (open window).
     *               - системная метка события (открыого окна).
     *
     * @param color - color of the button from the available ones.
     *              - цвет кнопки из доступных.
     *
     * @returns {string}
     */
    getButton: function (name, action, color) {
        if (!color) {
            color = 'green';
        }
        return '<div align="center"><div class="hlogin-btn-notice"></div><div class="hlogin-pan-btn-adaptive"><button tabindex="1" class="hlogin-pan-btn hlogin-pan-btn-' + color + ' hlogin-send-action" data-action="' + action + '" alt="' + this.clear(name) + '">' + name + '</button></div></div>';
    },

    /**
     * Adding an interactive checkbox.
     *
     * Добавление интерактивного чекбокса.
     *
     * @param {string} text - description of the option.
     *                      - описание опции.
     *
     * @param {string} type - type of action.
     *                      - тип действия.
     *
     * @param {boolean} on - enabled by default.
     *                     - включено по умолчанию.
     */
    getCheckBox: function (text, type, on) {
        return '<div class="hlogin-checkbox-over">' +
            '<div class="hlogin-cell-notice"></div>' +
            '<input  type="checkbox" autocomplete="off" class="hlogin-origin-checkbox" data-type="' + type + '" value="' + (on ? 'on' : '') + '" ' + (on ? 'checked' : '') + '>' +
            '<button class="hlogin-btn-checkbox" tabindex="1" alt="' + this.clear(text) + '"><img src="/hlresource/hlogin/v' + hlogin.script.version + '/svg/checkbox' + (on ? 'on' : 'none') + hlogin.script.design + hlogin.script.I + '" class="hlogin-img-checkbox"></button><span class="hlogin-pan-btn-checkbox-text">' + text + '</span>' +
            '</div>';
    },

    /**
     * Adding an interactive checkbox in which the text is not active (may contain a link).
     *
     * Добавление интерактивного чекбокса в котором текст не активен (может содержать ссылку).
     *
     * @param {string} text - description of the option.
     *                      - описание опции.
     *
     * @param {string} type - type of action.
     *                      - тип действия.
     *
     * @param {boolean} on - enabled by default.
     *                     - включено по умолчанию.
     */
    getSimpleCheckBox: function (text, type, on) {
        return '<div class="hlogin-checkbox-over hlogin-simple-checkbox">' +
            '<div class="hlogin-cell-notice"></div>' +
            '<input  type="checkbox" autocomplete="off" class="hlogin-origin-checkbox" data-type="' + type + '" value="' + (on ? 'on' : '') + '" ' + (on ? 'checked' : '') + '>' +
            '<button class="hlogin-btn-checkbox" tabindex="1" alt="' + this.clear(text) + '"><img src="/hlresource/hlogin/v' + hlogin.script.version + '/svg/checkbox' + (on ? 'on' : 'none') + hlogin.script.design + hlogin.script.I + '" class="hlogin-img-checkbox"></button>' +
            '<div class="hlogin-checkbox-text">' + text + '</div>' +
            '</div>';
    },

    getFormData: function (method, ec) {
        if (!ec) {
            ec = 'hlogin-over-content';
        }
        var fm = document.querySelectorAll('.' + ec + ' input, ' + '.' + ec + ' textarea, ' + '.' + ec + ' select');
        var data = {method: method, value: {}};
        fm.forEach(function (e) {
            var t = e.dataset.type;
            if (typeof data.value[t] !== 'undefined') {
                console.error('Duplicate ' + t + ' field in the form.');
            }
            if (e.type === 'checkbox') {
                data.value[t] = e.value === 'on';
            } else {
                data.value[t] = e.value;
            }
        });
        data['lang'] = hlogin.script.lang;
        return data;
    },

    /**
     * Creating a list of links to other panels based on the name of the current panel type.
     *
     * Создание списка ссылок на другие панели по названию текущего типа панели.
     *
     * @param {string} name - system name of the current window.
     *                      - системное название текущего окна.
     * @returns {string}
     */
    getFooterLinks: function (name) {
        var ls = null;
        switch (name) {
            case 'UserEnter':
                ls = hlogin.script.config.registration['enter-only'] ? {'UserPassword': 'forgot_password'} : {'UserPassword': 'forgot_password', 'UserRegister': 'registration_title'};
                break;
            case 'UserRegister':
                ls = {'UserEnter': 'sign_in'};
                break;
            case 'UserProfile':
                ls = {'UserExit': 'exit', 'UserFullExit': 'exit_all'};
                break;
            case 'UserPassword':
                ls = {'UserEnter': 'sign_in'};
                break;
            case 'CustomEmailMessage':
                ls = {'RegisterEmail': 'email_confirm_post'};
                break;
            case 'CustomMessage':
            case 'NewPassword':
            case 'ConfirmEmail':
            case 'ContactMessage':
                ls = {};
                break;
            default:
        }
        if (ls !== null && typeof ls === 'object') {
            if (hlogin.script.config.startCommand) {
                ls.ToHomepage = 'to_main_page';
            }
            var l = '<div class="hlogin-over-link">';
            for (var key in ls) {
                l += '<div class="hlogin-footer-link"><button tabindex="1" class="hlogin-init-action" data-type="' + key + '" alt="' + this.clear(this.getI18n(ls[key])) + '">' + this.getI18n(ls[key]) + '</button></a></div>';
            }
            return l + '</div>';
        }
        return '';
    },

    /**
     * Authorization panel.
     *
     * Панель авторизации.
     *
     * @param {string} id - email
     * @returns {string}
     */
    getUserEnterContent: function (id) {
        var name = this.getI18n('sign_in');
        var ct = this.getPanTitle(name) +
            this.wrapInRegFrame('UserEnter', name,
                this.getEmailRow(id) +
                this.getAntispamCell() +
                this.getPasswordRow(this.getI18n('password')) +
                this.getCheckBox(this.getI18n('remember_login'), 'remember', true) +
                this.getCaptchaRow() +
                this.getButton(this.getI18n('submit'), 'UserEnter', 'green'),
                this.getI18n('registration_title'), 'UserRegister');

        return ct;
    },

    getNewPasswordContent: function (id) {
        var name = this.getI18n('change_password');
        var ct = this.getPanTitle(name) +
            this.wrapInRegFrame('NewPassword', name,
                this.getAntispamCell() +
                this.getPasswordRow(this.getI18n('password')) +
                this.getPasswordRow(this.getI18n('password_repeat'), 1, false, true) +
                this.getCaptchaRow() +
                this.getButton(this.getI18n('submit'), 'NewPassword', 'blue')
            );

        return ct;
    },

    /**
     * Password recovery panel.
     *
     * Панель восстановление пароля.
     *
     * @param {string|null} id
     * @returns {string}
     */
    getUserPasswordContent: function (id) {
        var name = this.getI18n('send_password');
        var ct = this.getPanTitle(name) +
            this.wrapInRegFrame('UserPassword', name,
                this.getEmailRow(id) +
                this.getAntispamCell() +
                this.getCaptchaRow() +
                this.getPasswordTimerRow() +
                this.getButton(this.getI18n('submit'), 'UserPassword', 'blue'),
            );
        return ct;
    },

    /**
     * User registration panel.
     *
     * Панель решистрации пользователя.
     *
     * @param {string|null} id
     * @returns {string}
     */
    getUserRegisterContent: function (id) {
        var name = this.getI18n('registration_title');
        var ct = this.getPanTitle(name) +
            this.wrapInRegFrame('UserRegister', name,
                this.getEmailRow(id) +
                this.getAntispamCell() +
                this.getRow('phone') +
                this.getRow('name') +
                this.getRow('surname') +
                this.getRow('login') +
                this.getRow('address') +
                this.getRow('promocode') +
                this.getRow('cell1') +
                this.getRow('cell2') +
                this.getRow('cell3') +
                this.getRow('cell4') +
                this.getRow('cell5') +
                this.getPasswordRow(this.getI18n('password')) +
                this.getPasswordRow(this.getI18n('password_repeat'), 1, false, true) +
                this.getTermsRow() +
                this.getCaptchaRow() +
                this.getButton(this.getI18n('submit'), 'UserRegister', 'blue'),

                this.getI18n('sign_in'), 'UserEnter');

        return ct;
    },

    /**
     * Panel for displaying the user profile.
     *
     * Панель для вывода профиля пользователя.
     *
     * @param {string|null} id
     * @returns {string}
     */
    getUserProfileContent: function (id) {
        var name = this.getI18n('profile');
        var isAdmin = hlogin.script.config.registration.type >= 10;
        var ct = this.getPanTitle(name) +
            this.wrapInRegFrame('UserProfile', name,
                (isAdmin ? this.getButton(this.getI18n('adminzone_enter'), 'AdminzoneEnter', 'blue') : '' ) +
                this.getEmailRow(id) +
                this.getProfileRow('phone') +
                this.getProfileRow('name') +
                this.getProfileRow('surname') +
                this.getProfileRow('login') +
                this.getProfileRow('address') +
                this.getProfileRow('promocode') +
                this.getProfileRow('cell1') +
                this.getProfileRow('cell2') +
                this.getProfileRow('cell3') +
                this.getProfileRow('cell4') +
                this.getProfileRow('cell5') +
                '<hr><div class="hlogin-pan-hr"></div>' +
                this.getPasswordRow(this.getI18n('new_password'), 1, false, false, false) +
                this.getPasswordRow(this.getI18n('password_repeat'), 2, false, true, false) +
                '<hr><div class="hlogin-pan-hr"></div>' +
                this.getPasswordRow(this.getI18n('old_password')) +
                this.getButton(this.getI18n('submit'), 'UserProfile', 'blue'),

                this.getI18n('exit'), 'UserExit');

        return ct;

    },

    /**
     * Output a template for displaying E-mail confirmation messages.
     *
     * Вывод шаблона для отображения сообщений подтверждения E-mail.
     *
     * @returns {string}
     */
    getConfirmEmailContent: function() {
        var ct = this.wrapInRegFrame('ConfirmEmail', '',
            '<div class="hlogin-btn-notice"></div>',
        );
        return ct;
    },

    /**
     * Feedback form.
     *
     * Форма обратной связи.
     *
     * @param {string} id - email
     * @returns {string}
     */
    getContactMessageContent: function (id) {
        var name = this.getI18n('contact_send_message');
        var ct = this.getPanTitle(name) +
            this.wrapInRegFrame('ContactMessage', name,
                this.getEmailRow(id, this.getI18n('sender_email')) +
                this.getAntispamCell() +
                this.getTextRow(this.getI18n('contact_text')) +
                this.getCaptchaRow() +
                this.getButton(this.getI18n('submit'), 'ContactMessage', 'green'),
            );

        return ct;
    },

    /**
     * Display a custom message.
     *
     * Вывод произвольного сообщения.
     *
     * @param {string} title
     * @param {string} text
     * @returns {string}
     */
    getCustomContent(title, text) {
        return this.getPanTitle(this.getI18n(title)) +
            this.wrapInRegFrame('CustomMessage', this.getI18n(title),
                '<div class="hlogin-custom-message">' + this.getI18n(text) + '</div>',
            );
    },

    /**
     * Display an arbitrary message related to confirmation of registration E-mail.
     *
     * Вывод произвольного сообщения связанного с подтверждением регистрационного E-mail.
     *
     * @param {string} title
     * @param {string} text
     * @returns {string}
     */
    getCustomEmailContent(title, text) {
        return this.getPanTitle(this.getI18n(title)) +
            this.wrapInRegFrame('CustomEmailMessage', this.getI18n(title),
                '<div class="hlogin-custom-message">' + this.getI18n(text) + '</div>',
            );
    },

    /**
     * @param {string} id - email
     * @param {string} name - field title.
     *             - заголовок поля.
     * @returns {string}
     */
    getEmailRow: function (id, name) {
        var name = name ?? 'E-mail';
        var value = id ?? '';
        return '<label>' +
            '<div class="hlogin-input-title">' + name + '<span class="hlogin-input-marker">*</span></div>' +
            '<div class="hlogin-cell-notice"></div>' +
            '<input type="email" autocomplete="off" tabindex="1" data-type="email" minlength="5" maxlength="255" placeholder="email@example.com" class="hlogin-email-input-cell" value="' + value + '" alt="' + this.clear(name) + '">' +
            '</label>';
    },

    /**
     * Option to enter an E-mail with a list of allowed domains.
     *
     * Вариант ввода E-mail со списком разрешённых доменов.
     *
     * @param {array} opt - list of domains.
     *                    - список доменов.
     *
     * @param {string} name - field title.
     *                      - заголовок поля.
     * @returns {string}
     */
    getReqEmailRow: function (opt, name) {
        var name = name ?? 'E-mail';
        var value = opt[0];
        var select = '<select type="domain" tabindex="1" data-type="domain" placeholder="example.com" class="hlogin-email-second-cell" value="' + value + '" alt="' + this.clear(name) + '">';
        opt.forEach(
            function (val) {
                select += '<option value="' + val + '">' + val + '</option>';
            }
        );
        return '<input type="text" autocomplete="off" tabindex="1" data-type="email" minlength="1" maxlength="255" placeholder="" class="hlogin-email-first-cell" value="" alt="' + this.clear(name) + '">' +
            '<span class="hlogin-email-tag">@</span>' + select + '</select>';
    },

    getTextRow: function (name) {
        var name = name ?? 'E-mail';
        return '<label>' +
            '<div class="hlogin-input-title">' + name + '<span class="hlogin-input-marker">*</span></div>' +
            '<div class="hlogin-cell-notice"></div>' +
            '<textarea type="text" autocomplete="off" tabindex="1" data-type="message" minlength="5" maxlength="5000" placeholder="" class="hlogin-text-input-cell" alt="' + this.clear(name) + '"></textarea>' +
            '</label>';
    },

    /**
     * @param {string} name - field title.
     *                      - заголовок поля.
     *
     * @param {int} num - number of the password field.
     *                  - номер поля для пароля.
     *
     * @param {bool} display - display of password characters.
     *                       - отображение символов пароля.
     *
     * @param {bool} second - второстепенный пароль для подтверждения.
     *                      - secondary password for confirmation.
     *
     * @param {bool} req - признак обязательности.
     *                   - a sign of obligation.
     *
     * @returns {string}
     */
    getPasswordRow: function (name, num, display, second, req) {
        if (typeof num === 'undefined') {
            num = '';
        }
        var cl = hlogin.script.config.registration.cells.password;
        if (!cl.on) {
            return '';
        }
        if (typeof req === 'undefined') {
            req = cl.req;
        }
        var d = typeof display === 'undefined' || display ? '<div class="hlogin-password-text-show" title="' + this.clear(this.getI18n('hide_password')) + '">•••</div><div class="hlogin-password-text-hide" title="' + this.clear(this.getI18n('show_password')) + '">Aa</div>' : '';
        var p9r = this.getI18n('input_password');
        return '<form><div class="hlogin-input-title">' + name + '<span class="hlogin-input-marker">' + (req ? '*' : '') + '</span></div>' +
            '<div class="hlogin-cell-notice"></div><div class="hlogin-password-over">' + d +
            '<input type="password" tabindex="1" minlength="6" maxlength="100" data-type="password' + num + '" placeholder="' + (second ? '' : p9r) + '" maxlength="100" class="hlogin-password-cell" value="" data-req="1" autocomplete="off" alt="' + this.clear(name) + '">' +
            '</div>' +
            '</form>';
    },

    getCaptchaRow: function () {
        var c = '<!-- Captcha is disabled in the library settings. -->';
        if (hlogin.script.config.captcha.active) {
            return '<div class="hlogin-captcha-over">' +
                '<div class="hlogin-cell-notice"></div>' +
                '<img src="/hlresource/hlogin/v' + hlogin.script.version + '/png/ucaptcha' + hlogin.script.I + '?design=' + hlogin.script.design + '&' + Math.random() + '" class="hlogin-captcha-img">' +
                '<button class="hlogin-remove-img-over  hlogin-init-action" data-type="ReloadCaptcha" tabindex="1"><img src="/hlresource/hlogin/v' + hlogin.script.version + '/svg/hloginremoveimg' + hlogin.script.I + '" class="hlogin-captcha-reload hlogin-init-action" data-type="ReloadCaptcha"></button>' +
                '<input type="text" autocomplete="off" data-type="captcha" tabindex="1" minlength="4" maxlength="6" class="hlogin-captcha-code" placeholder="' + this.getI18n('captcha_code') + '" alt="' + this.clear(this.getI18n('captcha_code')) + '">' +
                '</div>';
        }
        return c;
    },

    /**
     * Block for displaying the balance when retrieving a password.
     *
     * Блок для вывода остсчёта при восстановлении пароля.
     *
     * @returns {string}
     */
    getPasswordTimerRow: function() {
        return '<div class="hlogin-timer-text"></div>';
    },

    /**
     * @param name - title tag in translation.
     *             - тег названия в переводе.
     *
     * @param type - tag in the configuration.
     *             - тег в конфигурации.
     * @param placeholder
     * @returns {string}
     */
    getRow: function (name, type, placeholder) {
        if (typeof type === 'undefined') {
            type = name;
        }
        var cl = hlogin.script.config.registration.cells[type];
        if (!cl.on) {
            return '';
        }
        if (typeof placeholder === 'undefined') {
            placeholder = '';
        }
        return this.getDynamicRow(name, type, placeholder, cl.req);
    },

    /**
     * @param name - title tag in translation.
     *             - тег названия в переводе.
     *
     * @param type - tag in the configuration.
     *             - тег в конфигурации.
     * @param placeholder
     * @returns {string}
     */
    getProfileRow: function (name, type, placeholder) {
        if (typeof type === 'undefined') {
            type = name;
        }
        var cl = hlogin.script.config.registration.cells[type];
        if (!cl.prof) {
            return '';
        }
        if (typeof placeholder === 'undefined') {
            placeholder = '';
        }
        return this.getDynamicRow(name, type, placeholder, cl.req);
    },

    /**
     * @param name - title tag in translation.
     *             - тег названия в переводе.
     *
     * @param type - tag in the configuration.
     *             - тег в конфигурации.
     * @param placeholder
     * @param required
     * @returns {string}
     */
    getDynamicRow: function (name, type, placeholder, required) {
        return '<label>' +
            '<div class="hlogin-input-title">' + this.getI18n(name) + '<span class="hlogin-input-marker">' + (required ? '*' : '') + '</span></div>' +
            '<div class="hlogin-cell-notice"></div>' +
            '<input type="text" autocomplete="off" tabindex="1" data-type="' + type + '" placeholder="' + this.getI18n(placeholder) + '" class="hlogin-input-cell" value="" alt="' + this.clear(this.getI18n(name)) + '">' +
            '</label>';

    },

    getAntispamCell: function () {
        return '<div class="hlogin-hidden"><input type="text" autocomplete="off" data-type="detector" value=""></div>';
    },

    getTermsRow: function() {
        var cl = hlogin.script.config.registration.cells;
        var termLink = hlogin.script.config.registration.src['terms-of-use'];
        var policyLink = hlogin.script.config.registration.src['privacy-policy'];
        var res = '';
        if (cl['terms-of-use'].on && cl['privacy-policy'].on) {
            res += '<div class="hlogin-checkbox-terms">' + this.getSimpleCheckBox(this.getI18n('familar') + ' <a href="' + termLink  + '" target="_blank">' + this.getI18n('terms') + '</a> ' + this.getI18n('and') + ' <a href="' + policyLink + '" target="_blank">' + this.getI18n('privacy_policy') + '</a>, ' + this.getI18n('conditions') + '.' , 'terms') + '</div>';
        } else if (cl['terms-of-use'].on) {
            res += '<div class="hlogin-checkbox-terms">' + this.getSimpleCheckBox(this.getI18n('familar') + ' <a href="' + termLink  + '" target="_blank">' + this.getI18n('terms') + '</a>, '+ this.getI18n('conditions') + '.' , 'terms') + '</div>';
        } else if (cl['privacy-policy'].on) {
            res += '<div class="hlogin-checkbox-terms">' + this.getSimpleCheckBox(this.getI18n('familar') + ' <a href="' + policyLink  + '" target="_blank">' + this.getI18n('privacy_policy') + '</a>, '+ this.getI18n('conditions') + '.' , 'terms') + '</div>';
        }
        if (cl['subscription'].on) {
            res += '<div class="hlogin-checkbox-subscription">' + this.getSimpleCheckBox(this.getI18n('subscription') + '.', 'subscription') + '</div>';
        }
        return res;
    },

    /**
     * Update captcha picture.
     * It is implied that she is the only one in the form.
     *
     * Обновление картинки на captcha.
     * Подразумевается, что в форме она одна.
     */
    updCaptchaImage: function () {
        var img = document.querySelector('.hlogin-captcha-over>.hlogin-captcha-img');
        if (img) {
            img.src = '/hlresource/hlogin/v' + hlogin.script.version + '/png/ucaptcha' + hlogin.script.I + '?design=' + hlogin.script.design + '&' + Math.random();
        }
    },

    /**
     *
     * Replacing the standard email input field with a composite one.
     *
     * Замена стандартного поля ввода email на составной.
     *
     * @param {array} opt
     */
    replaceEmailRow: function(opt) {
        var old = document.querySelector('.hlogin-frame-over input[data-type="email"].hlogin-email-input-cell');
        if (old) {
            old.outerHTML = this.getReqEmailRow(opt);
        }
    },

    updateProfileData(data) {
        if (!data) {
            this.internalActionHandler('CustomMessage', 'email_not_confirmed', 'profile');
            return;
        }
        document.querySelectorAll('.hlogin-frame-internal input').forEach(
            function(e) {
                e.value = '';
                var t = e.getAttribute('data-type');
                if (t && typeof data[t] !== 'undefined') {
                    e.value = data[t];
                }
            }
        );
    },

    updateTimer: function() {
        var t = document.querySelector('.hlogin-timer-text');
        if (t) {
            if (this.timer === null) {
                this.timer = 60;
            }
            if (this.timerId === null) {
                var th = this;
                this.timerId = setInterval(function() {
                    th.timer--;
                    if (th.timer <= 0) {
                        clearInterval(th.timerId);
                        th.timerId = null;
                        th.clearTimer();
                        return;
                    }
                    th.setTimer(th.timer);
                }, 1000);
            }
        }
    },

    clearTimer: function() {
        this.timer = null;
        var t = document.querySelector('.hlogin-timer-text');
        if (t) {
            t.innerHTML = '';
            document.querySelectorAll('button[data-action="UserPassword"]').forEach(
                function(e) {
                    e.disabled = '';
                }
            );
        }
    },

    setTimer: function (time) {
        var t = document.querySelector('.hlogin-timer-text');
        if (t) {
            t.innerHTML = this.getI18n('will_be_available') + ' ' + time + ' ' + this.getI18n('seconds');
            var btn = document.querySelector('button[data-action="UserPassword"]');
            if (btn) {
                btn.disabled = 'disabled';
            }
        }
    },


    setFullScrinPanel: function() {
        if (!hlogin.script.config.startCommand) return;

        document.querySelectorAll('.hlogin-pan-top, .hlogin-pan-right, .hlogin-pan-left, .hlogin-pan-bottom').forEach(
            function (el) {
                el.outerHTML = '';
            }
        );
        document.querySelectorAll('.hlogin-over-panel').forEach(
            function (p) {
                p.classList.add('hlogin-pan-maximize-block');
            }
        );
    },

    /**
     * Assigning handlers for forms.
     *
     * Назначение обработчиков для форм.
     *
     * @param obj - an element within which event handlers are assigned.
     *            - элемент, внутри которого происходит назначение обработчиков событий.
     */
    initPopupContentActions: function (obj) {
        var th = this;
        obj.querySelectorAll('.hlogin-send-action').forEach(
            function (e) {
                e.addEventListener('click', function () {
                    if (e.dataset.action) {
                        hlogin.panel.sendAjaxRequest(hlogin.panel.getFormData(e.dataset.action));
                    }
                });
            });
        obj.querySelectorAll('.hlogin-btn-checkbox').forEach(
            function (e) {
                e.addEventListener('click', function () {
                    var c = e.parentNode.querySelector('.hlogin-origin-checkbox');
                    var on = c.value === 'on';
                    c.value = on ? '' : 'on';
                    c.checked = !on;
                    var img = e.querySelector('.hlogin-img-checkbox');
                    img.src = '/hlresource/hlogin/v' + th.version + '/svg/checkbox' + (on ? 'none' : 'on') + hlogin.script.design + hlogin.script.I;
                });
            });
        // Show the original checkbox if the checkbox image has not loaded.
        // Показ исходного чекбокса если картинка чекбокса не загрузилась.
        obj.querySelectorAll('.hlogin-img-checkbox').forEach(
            function (e) {
                e.addEventListener('error', function () {
                    e.style.display = 'none';
                    var c = e.parentNode.parentNode.querySelector('.hlogin-origin-checkbox');
                    if (c) {
                        c.style.display = 'inline-block';
                    }
                });
            });
        obj.querySelectorAll('.hlogin-password-text-show').forEach(
            function (e) {
                e.addEventListener('click', function () {
                    e.style.display = 'none';
                    var t = e.parentNode.querySelector('.hlogin-password-text-hide');
                    t.style.display = 'inline-block';
                    var i = e.parentNode.querySelector('.hlogin-password-cell');
                    i.type = 'password';
                });
            });
        obj.querySelectorAll('.hlogin-password-text-hide').forEach(
            function (e) {
                e.addEventListener('click', function () {
                    e.style.display = 'none';
                    var t = e.parentNode.querySelector('.hlogin-password-text-show');
                    t.style.display = 'inline-block';
                    var i = e.parentNode.querySelector('.hlogin-password-cell');
                    i.type = 'text';
                });
            });
        obj.querySelectorAll('.hlogin-origin-checkbox').forEach(
            function (e) {
                e.addEventListener('click', function () {
                    e.parentNode.querySelector('.hlogin-btn-checkbox').click();
                });
            });
    },

    convertLink: function (uri) {
        return uri.replace('$LANG', hlogin.script.lang);
    }
}
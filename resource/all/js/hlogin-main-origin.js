if (typeof mHLogin === 'undefined') mHLogin = {};
if (typeof mHLogin.base === 'undefined') mHLogin.base = {};

// Основные функции для регистрации
mHLogin.base = {
    inFramePerem: null,
    csrfToken: null,
    userRegister: false,
    LoadedCss: [],
    version: null,
    design: null,
    arhiveFromBtnText: {},
    checkLoadedCss: function () {
        var el = hlUnvrsl.Def.$1('.hlogin-load-css-files-test--control');
        if (el && el.offsetWidth > 90 && el.offsetHeight > 90 && el.offsetWidth < 110 && el.offsetHeight < 110) {
            hlUnvrsl.Def.deleteBlock(el);
            return true;
        }
        return false
    },
// Инициализатор по загрузке страницы
    registerAMain: function () {
        const th = this;
        var intervalId = setInterval(function () {
            if (document.body !== null && typeof uHLogin === 'object' && typeof uHLogin.functions === 'object' && typeof hlUnvrsl === 'object' && typeof hlUnvrsl.Def === 'object') {
                clearInterval(intervalId);
                th.stateOnloadAMain();
            }
        }, 20);
    },
    loadJsLangs: function () {
        const th = this;
        hlUnvrsl.Def.loadJs('/en/login/resource/' + uHLogin.functions.version + '/all/js/js/hlogin-lang-' + uHLogin.functions.lang + this.confEndingUrl());
        var intervalId = setInterval(function () {
            if (typeof mHLogin.i18n === 'object') {
                clearInterval(intervalId);
                mHLogin.actions.i18n = mHLogin.i18n;
            }
        }, 20);
    },
    loadCssResources: function (href) {
        var el = document.createElement('div');
        if (!hlUnvrsl.Def.$1('.hlogin-load-css-files-test--control')) {
            el.style.opacity = '0.01';
            el.style.position = 'fixed';
            el.style.width = "0";
            el.style.height = "0";
            el.classList.add('hlogin-test-load-block-media');
            el.classList.add('hlogin-test-load-block-design');
            el.classList.add('hlogin-load-css-files-test--control');
            document.body.appendChild(el);
        }

        var css = document.createElement('link');
        css.rel = "stylesheet";
        css.href = '/en/login/resource/' + this.version + href;
        this.LoadedCss.push(href);
        document.head.appendChild(css);
    },
// Загрузчик значений из кода страницы
    stateOnloadAMain: function () {
        var data = uHLogin.functions.registrationData;
        this.lang = data.lang;
        this.csrfToken = data.csrfToken;
        this.userRegister = data.userRegister;
        this.version = data.version;
        this.design = uHLogin.functions.design;
        this.endingUrl = data.endingUrl;
        this.loadCssResources('/' + this.design + '/css/css/hlogin-design' + this.confEndingUrl());
        this.loadCssResources('/all/css/css/hlogin-media' + this.confEndingUrl());
        this.loadJsLangs();
        mHLogin.actions.initActions();
    },
    confEndingUrl: function () {
        return this.endingUrl ? '/' : '';
    }
};

// Действия для регистрации
if (typeof mHLogin.actions === 'undefined') {
    mHLogin.actions = {
        i18n: null,
        M: hlUnvrsl.Def,
        F: uHLogin.functions,
        $: function (val) {
            return this.M.$(val)
        },
        $1: function (val) {
            return this.M.$1(val)
        },
        $$: function (val) {
            return this.M.$$(val)
        },
        userRegisterData: null,
        actualEmail: null,
        activeType: null,
        firstLoad: true,
        timeLeft: 0,
        timeLeftInterval: 0,
        formToPopup: [],
        lastBlockedBtnText: null,
        lastOpenPopupType: [],
        lastAddedEmail: '',
        // Была попытка отправить данные
        firstDataSended: false,
        lastKey: null,
        intervalsFromFocus: null,
        initActions: function () {
            var th = this;
            document.body.addEventListener('keydown', function (event) {
                th.lastKey = event;
                setTimeout(
                    function () {
                        th.lastKey = null;
                    }, 1000);
            });
        },
        tabCount: 0,
        captchaCode: null,
        captchaClose: false,
        loadedCss: false,
        forcedExit: false,
        lastAddedEmail: "",
        originalFontFamily: "'PT Sans', sans-serif",
        closeFonFromPopup: function () {
            var fon = this.$1(".hlogin-p-overlay-popup--control");
            if (typeof fon !== "undefined" && fon !== null) {
                fon.style.display = "none";
            }
        },
        openFonFromPopup: function () {
            var fon = this.$1(".hlogin-p-overlay-popup--control");
            if (typeof fon !== "undefined" && fon !== null) {
                fon.style.display = "block";
                if (this.M.sizeW() < 480) {
                    this.M.scrollLeft(100);
                    this.M.scrollTop(100);
                }
            } else {
                var el = document.createElement('div');
                el.classList.add('hlogin-p-overlay-popup--control');
                el.classList.add('hlogin-p-overlay-fon-from-popup');
                el.style.display = "block";
                el.onclick = function () {
                    mHLogin.actions.variableCloseLastPopup()
                };
                document.body.appendChild(el);
            }
        },
        viewBlockOverPopup: function (val) {
            var over = this.$1(".hlogin-p-block-over-popup--control");
            if (typeof over !== "undefined" && over !== null) {
                over.style.display = val ? "block" : "none";
            } else if (val) {
                var el = document.createElement('div');
                el.classList.add('hlogin-p-block-over-popup--control');
                el.classList.add('hlogin-p-block-over-popup');
                el.style.display = "block";
                document.body.appendChild(el);
            }
        },
        // очистка данных в окне
        clearOnePopupData: function (type) {
            var popups = this.$("#HLoginVariablePopup_" + type + " input");
            for (var p = 0; p < popups.length; p++) {
                this.clearDataOnPopup(popups[p], type);
            }
            var info = this.$("#HLoginVariablePopup_" + type + " .hlogin-p-popup-check-info");
            for (var i = 0; i < info.length; i++) {
                info[i].innerHTML = "";
            }
            return false;
        },
        clearDataOnPopup: function (elem, type) {
            var popup_type = (typeof type !== "undefined") ? type : "";
            if (elem.getAttribute("type") === "checkbox") {
                elem.checked = false;
                this.setImgCheckbox(elem.parentNode, false)
            } else if ((!(elem.getAttribute("data-type") === "email" && elem.value === this.lastAddedEmail) ||
                popup_type === "UserRegister") && elem.getAttribute("data-type") !== "captcha") {
                elem.value = "";
            }
            elem.classList.remove("hlogin-p-popup-cell-error");

            var pass_cells = this.$(".hlogin-p-autogen-pass-cell--control");
            for (var p = 0; p < pass_cells.length; p++) {
                pass_cells[p].innerHTML = "";
            }
        },
        clearPopupsData: function () {
            var popups = this.$(".hlogin-p-register-popup-user--control input");
            for (var i = 0; i < popups.length; i++) {
                this.clearDataOnPopup(popups[i]);
            }
            var info = this.$(".hlogin-p-popup-check-info");
            for (var f = 0; f < popups.length; f++) {
                if (typeof info[f] !== "undefined") {
                    info[f].innerHTML = "";
                }
            }
            this.clearCaptcha();
            this.clearAllPasswords();
            this.firstDataSended = false;
            return false;
        },
        clearCaptcha: function () {
            if (this.captchaClose) {
                var captcha = this.$(".hlogin-captcha-over");
                for (var c = 0; c < captcha.length; c++) {
                    captcha[c].innerHTML = "";
                }
            }
        },
        variableCloseLastPopup: function () {
            if (this.checkLoadedLang()) {
                this.closeFonFromPopup();
                this.clearOnePopupData(this.lastOpenPopupType);
                this.$("#" + this.formToPopup[this.lastOpenPopupType]).style.display = "none";
                this.M.removeBodyOverflow();
            }
            return false;
        },
        // Открытие блока
        variableOpenPopup: function (type) {
            if (type === 'EmptyBlock') return;
            if (type == "UserExit") {
                this.variableCloseAll();
                // Редирект
                location.assign("/en/login/action/" + (this.forcedExit ? "exitforce" : "exit") + mHLogin.base.confEndingUrl());
                return;
            }
            if(this.captchaCode == null) {
                this.captchaCode = this.generateCaptchaLink();
            }
            this.clearErrors();
            this.unlockBtn(0);
            this.openFonFromPopup();

            // Создание пустого блока, если отсутствует
            if (typeof mHLogin.i18n === 'object') {
                this.createStandardBlock(type);
                this.hloginMainVariableFunction();
            } else {
                var th = this;
                var intervalId = setInterval(function () {
                    if (th.checkLoadedLang()) {
                        clearInterval(intervalId);
                        th.createStandardBlock(type);
                        th.hloginMainVariableFunction();
                    }
                }, 20);
            }
            return false;
        },
        hloginMainVariableFunction: function(type) {
            // Выполнение пользовательской функции
            if(typeof _hloginMainVariableFunction === 'function' && (['UserRegister', 'UserEnter', 'UserPassword', 'ContactMessage', 'UserProfile', 'UserMessage', 'MessageFromConfirmEmail', 'MessageAfterContact', 'ErrorChangePassword', 'EmailConfirmSuccess', 'NewPassword'].indexOf(type) != -1)) {
                _hloginMainVariableFunction(type);
            }
        },
        checkLoadedLang: function () {
            return typeof mHLogin.i18n === 'object' && typeof mHLogin.i18n.get !== 'undefined' && this.i18n != null;
        },
        createStandardBlock: function (type) {
            console.log(type);
            this.lastOpenPopupType = type;
            this.variableCreateBlock(type);

            var element = this.$("#" + this.formToPopup[type]);
            element.style.display = "block";

            this.M.addBodyOverflow();

            this.M.scrollElementTop(element);
            if (this.M.sizeW() > 680 && this.M.sizeH() > 420) this.setFocusOnCell(this.formToPopup[type]);
            this.firstLoad = false;
        },
        variableCloseAll: function () {
            for (var key in this.formToPopup) {
                var item = this.$("#" + this.formToPopup[key]);
                if (item) {
                    item.style.display = "none";
                }
            }
            this.M.removeBodyOverflow();
            this.clearPopupsData();
            this.closeFonFromPopup();
            if (this.M.sizeW() < 480) this.M.scrollTop(100);
            this.activeType = null;
        },
        variableCreateBlock: function (type) {
            if (this.activeType == type) {
                if (type === "UserProfile") {
                    this.loadUserRegisterData();
                }
                return;
            }
            this.F.setLang();
            this.activeType = type;
            if (typeof this.formToPopup[type] === "undefined" || this.formToPopup[type] == null) {
                var block = document.createElement('div');
                block.id = "HLoginVariablePopup_" + type;
                block.style.display = "none";
                block.classList.add('hlogin-shadow-on-block-st');
                block.classList.add('hlogin-p-register-popup-global');
                block.classList.add('hlogin-wn');
                block.classList.add('hlogin-p-register-popup-user--control');
                block.setAttribute('data-type', this.F.design);
                block.setAttribute('align', "center");
                block.style.visibility = "hidden";
                if (this.getConfigValue("font_base")) {
                    block.style.fontFamily = this.getConfigValue("font_base");
                } else {
                    block.style.fontFamily = this.originalFontFamily;
                }
                var timer = 0;
                if (!this.loadedCss) {
                    var th = this;
                    var interval = setInterval(function () {
                        if (timer > 50 || mHLogin.base.checkLoadedCss()) {
                            clearInterval(interval);
                            block.style.visibility = "visible";
                            th.loadedCss = true;
                        }
                        timer++;
                    }, 100);
                } else {
                    block.style.visibility = "visible";
                }

                var block_in = document.createElement('div');
                block_in.classList.add('hlogin-p-register-popup-global-in');
                block_in.align = "left";
                block_in.innerHTML = "<div class='hlogin-p-close-popup-x' title='" + this.i18n.get('close') + "'><a href='javascript:mHLogin.actions.variableCloseAll();' tabindex='1'>X</a></div>";

                if (type === "UserRegister") {
                    block_in.innerHTML += this.createBlockUserRegister(type);
                    if (this.tabCount > 4 || this.design == 'special') {
                        block.style.top = '0';
                        block.style.height = '100%';
                        block.style.maxHeight = '100%';
                    }
                    this.tabCount = 0;
                } else if (type === "UserEnter") {
                    block_in.innerHTML += this.createBlockUserEnter(type);
                } else if (type === "UserPassword") {
                    block_in.innerHTML += this.createBlockUserPassword(type);
                } else if (type === "UserProfile") {
                    block_in.innerHTML += this.createBlockUserProfile(type);
                    block.style.top = '0';
                    block.style.height = '100%';
                    block.style.maxHeight = '100%';
                    this.tabCount = 0;
                } else if (type === "NewPassword") {
                    block_in.innerHTML += this.createBlockNewPassword(type);
                } else if (type === "MessageFromConfirmEmail") {
                    block_in.innerHTML += this.createBlockMessageFromConfirmEmail(type);
                } else if (type === 'GetToMainPage') {
                    block_in.innerHTML += this.createBlockGetToMainPage(type);
                    block_in.style.border = "0";
                } else if (type === 'ErrorChangePassword' || type === 'ErrorConfirmEmail') {
                    block_in.innerHTML += this.createBlockErrorChangePassword();
                } else if (type === 'EmailConfirmSuccess') {
                    block_in.innerHTML += this.createBlockEmailConfirmSuccess();
                } else if(type === 'UserMessage') {
                    block_in.innerHTML += this.createBlockUserMessage(type);
                } else if(type === 'ContactMessage') {
                    block.style.top = '0';
                    block.style.height = '100%';
                    block.style.maxHeight = '100%';
                    this.tabCount = 0;
                    block_in.innerHTML += this.createBlockContactMessage(type);
                } else if(type === 'MessageAfterContact') {
                    block_in.innerHTML += this.createBlockMessageAfterContact(type);
                }

                block.appendChild(block_in);
                document.body.appendChild(block);
                this.formToPopup[type] = "HLoginVariablePopup_" + type;
            }
            if (type === "UserProfile") {
                this.loadUserRegisterData();
            }
        },
        setFocusOnCell: function (id) {
            var input = this.$1("#" + id + " input");
            if (input) {
                setTimeout(function () {
                    input.focus();
                }, this.firstLoad ? 1000 : 0);
            }
        },
        getConfigValue: function (name) {
            if (typeof this.F.configData[name] !== 'undefined') {
                return this.F.configData[name];
            }
            return null;
        },
        // Создание всплывающего сообщения
        createMessageWindow: function (title, text, error, button) {
            if (typeof error === 'undefined') error = false;
            if (typeof button === 'undefined') button = false;
            var prefix = "HloginMessageInPopupInfo";
            var type = "UserMessage";
            this.variableOpenPopup(type);
            var th = this;
            var messageInterval = setInterval( function() {
                var titleStr = th.$("#" + prefix + "Title_" + type);
                var textStr = th.$("#" + prefix + "Text_" + type);
                var errorStr = th.$("#" + prefix + "Error_" + type);
                if (titleStr && textStr && errorStr) {
                    clearInterval(messageInterval);
                    var btn = '';
                    if (button !== null) {
                        btn = button ? "<div align='center'>" + th.createCloseButton(th.i18n.get(button.replace(/[\<\>]/g, ''))) + "</div>" : th.createLinkToEnter(th.i18n.get('to_main_page'), "GetToMainPage", 2) +
                            "<br><br>";
                    }
                    titleStr.innerHTML = title;
                    textStr.innerHTML = (!error ? text : '') + "<br><br>" + btn;
                    errorStr.innerHTML = error ? '<span class="hlogin-a7e-danger">&#9888;</span> ' + text : '';
                }
            }, 20);
        },
        // Регистрация
        createBlockUserRegister: function (type) {
            var tab = 0;
            this.tabCount = 0;
            return "<div class='hlogin-p-title-h3'>" + this.i18n.get('registration_title') + " " + this.createLinkToEnter(this.i18n.get('sign_in'), "UserEnter", tab + 1, true) + "</div>" +
                "<div id='HLoginMessageInPopupInfo_" + type + "' class='hlogin-p-popup-check-info'></div>" +
                this.createBlockInputEmailCell("E-mail", "email@example.com", tab + 1, "", "email") +
                this.createBlockVariableCell("phone", "", tab + 1, "", "tel", 30) +
                this.createBlockVariableCell("name", "", tab + 1, "", "text", 100) +
                this.createBlockVariableCell("surname", "", tab + 1, "", "text", 100) +
                this.createBlockVariableCell("address", "", tab + 1, "", "text", 255) +
                (this.getConfigValue('cell_password') == 'on' ?
                    (this.createBlockInputPasswordCell(this.i18n.get('password') + this.createVisiblePassword(), this.i18n.get('input_password'), tab + 1, "password1", true) +
                        this.createBlockInputPasswordCell(this.i18n.get('password_repeat'), "", tab + 1, "password2", true) +
                        this.createPasswordGenerator()) : this.createSimpleText('mess_password')) +
                this.createBlockVariableCell("promocode", "", tab + 1, "", "text", 100) +
                this.createUserBlockCaptchaSDK(type) +
                (this.F.captchaActive == 1 ? this.createBlockCaptcha() : "") +
                (this.getConfigValue('cell_subscription') == 'on' ? this.createBlockSubscriptionCell("subscription", tab + 1) : "") +
                this.createConfirmText(tab + 1) +
                "<div align='center'>" + this.createBlueButtonSend(this.i18n.get('submit'), type, tab + 1) + "</div>" +
                this.createLinkToEnter(this.i18n.get('sign_in'), "UserEnter", tab + 1) +
                "<div class='hlogin-only-single-page'><br><br>" + this.createLinkToEnter(this.i18n.get('to_main_page'), "GetToMainPage", tab + 1) + "<br><br></div>" +
                this.createBlockMainSDK(type) +
                "<br><br>";
        },
        // Вход
        createBlockUserEnter: function (type) {
            var tab = 0;
            return "<div class='hlogin-p-title-h3'>" + this.i18n.get('sign_in') + "</div>" +
                "<div id='HLoginMessageInPopupInfo_" + type + "' class='hlogin-p-popup-check-info'></div>" +
                this.createBlockInputEmailCell("E-mail", "email@example.com", tab + 1, this.lastAddedEmail, "email") +
                this.createBlockInputPasswordCell(this.i18n.get('password') + this.createVisiblePassword(), "", tab + 1, "password1", true) +
                this.createUserBlockCaptchaSDK(type) +
                (this.F.captchaActive == 1 ? this.createBlockCaptcha() : "") +
                this.createRememberText(tab + 1) +
                "<div align='center'>" + this.createGreenButtonSend(this.i18n.get('submit'), type, tab + 1) + "</div>" +
                this.createLinkToEnter(this.i18n.get('registration_title'), "UserRegister", tab + 1) +
                this.createLinkToEnter(this.i18n.get('forgot_password'), "UserPassword", tab + 1) +
                "<div class='hlogin-only-single-page'><br><br>" + this.createLinkToEnter(this.i18n.get('to_main_page'), "GetToMainPage", tab + 1) + "<br><br></div>" +
                this.createBlockMainSDK(type) +
                "<br><br>";
        },
        // Восстановление пароля
        createBlockUserPassword: function (type) {
            var tab = 0;
            return "<div class='hlogin-p-title-h3'>" + this.i18n.get('send_password') + "</div>" +
                "<div id='HloginMessageInPopupInfo_" + type + "' class='hlogin-p-popup-check-info'></div>" +
                this.createBlockInputEmailCell(this.i18n.get('on_email'), "email@example.com", tab + 1, this.lastAddedEmail, "email") +
                this.createUserBlockCaptchaSDK(type) +
                (this.F.captchaActive == 1 ? this.createBlockCaptcha() : "") +
                "<div class='hlogin-v-time-left hlogin-v-time-left--control'></div>" +
                "<div align='center' id='HloginUserPasswordButton'>" + this.createBlueButtonSend(this.i18n.get('submit'), type, tab + 1) + "</div>" +
                this.createLinkToEnter(this.i18n.get('sign_in'), "UserEnter", tab + 1) +
                this.createLinkToEnter(this.i18n.get('registration_title'), "UserRegister", tab + 1) +
                this.createBlockMainSDK(type) +
                "<br><br>";
        },
        // Обратная связь
        createBlockContactMessage: function (type) {
            var tab = 0;
            this.tabCount = 12;
            return "<div class='hlogin-p-title-h3'>" + this.i18n.get('contact_send_message') + "</div>" +
                "<div id='HloginMessageInPopupInfo_" + type + "' class='hlogin-p-popup-check-info'></div>" +
                this.createBlockInputEmailCell(this.i18n.get('sender_email'), "email@example.com", tab + 1, this.lastAddedEmail, "email") +
                this.simpleBlockCell("sender_name", false, "text", "", tab + 1, 100, "") + "<br>" +
                "<div id='HloginContactMessage'><div class='hlogin-g-input-cell-text'>" + this.i18n.get('contact_text') + "<span class='hlogin-s-required-marker'>*</span></div>" +
                "<textarea type='text' data-type='contact_text' tabindex='1' data-req='1' maxlength='2500' class='hlogin-g-input-cell hlogin-g-textarea-cell'></textarea></div>" +
                this.createUserBlockCaptchaSDK(type) +
                (this.F.captchaActive == 1 ? this.createBlockCaptcha() : "") +
                "<div align='center'>" + this.createGreenButtonSend(this.i18n.get('submit'), type, tab + 1) + "</div>" +
                (mHLogin.base.userRegister <= 0 ? this.createLinkToEnter(this.i18n.get('sign_in'), "UserEnter", tab + 1)  :  '') + this.createLinkToEnter(this.i18n.get('to_main_page'), "GetToMainPage", tab + 1) +
                this.createBlockMainSDK(type) +
                "<br><br>";
        },
        // Новый пароль (по ссылке из письма)
        createBlockNewPassword: function (type) {
            var tab = 0;
            var code = this.M.convertUriToObject('code');
            if (code) {
                return this.createBlockInputPasswordCell(this.i18n.get('new_password') + this.createVisiblePassword(), this.i18n.get('input_password'), tab + 1, "password1", true) +
                    this.createBlockInputPasswordCell(this.i18n.get('password_repeat'), "", tab + 1, "password2", true) +
                    this.createUserBlockCaptchaSDK(type) +
                    (this.F.captchaActive == 1 ? this.createBlockCaptcha() : "") +
                    "<div align='center'>" + this.createBlueButtonSend(this.i18n.get('change_password'), type, tab + 1) + "</div>" +
                    "<br><br>";
            }
            return this.errorChangePassword(tab);
        },
        createUserBlockCaptchaSDK: function (type) {
            return "\n" + "<!-- Hlogin: Possibility to add your own captcha in this place for '" + type + "'. -->" + "\n" + "<div id='hloginAddMainSdkCaptcha" + type + "' class='hlogin-add-variable-block-captcha'></div>" + "\n";
        },
        createBlockErrorChangePassword: function () {
            var tab = 0;
            return this.errorChangePassword(tab);
        },
        // Профиль пользователя
        createBlockUserProfile: function (type) {
            var tab = 0;
            return "<div class='hlogin-p-title-h3'>" + this.i18n.get('profile') + " " + this.createLinkToEnter(this.i18n.get('exit'), "UserExit", tab + 1, true, 'hlogin-light-alert-color') + "</div>" +
                (mHLogin.base.userRegister >= 11 ? '<div align="center">' + this.createGreenButtonLink(this.i18n.get('adminzone_enter'), type, tab + 1, '/' + uHLogin.functions.lang + '/adminzone/registration/settings/') + "</div>" : '') +
                this.createBlockInputEmailCell("E-mail", "email@example.com", tab + 1, "", "email") +
                this.createConfirmEmail(tab + 1) +
                this.createBlockProfileCell("phone", "", tab + 1, "", "tel", 30) +
                this.createBlockProfileCell("name", "", tab + 1, "", "text", 100) +
                this.createBlockProfileCell("surname", "", tab + 1, "", "text", 100) +
                this.createBlockProfileCell("address", "", tab + 1, "", "text", 255) +
                this.createBlockInputPasswordCell(this.i18n.get('new_password') + this.createVisiblePassword(), this.i18n.get('input_password'), tab + 1, "password", false) +
                "<br><hr>" +
                this.createBlockInputPasswordCell(this.i18n.get('old_password') + this.createVisiblePassword(), "", tab + 1, "password1", true) +
                "<div align='center'>" + this.createBlueButtonSend(this.i18n.get('save_changes'), type, tab + 1) + "</div>" +
                this.createLinkToEnter(this.i18n.get('to_main_page'), "GetToMainPage", tab + 1) +
                "<br><br><br>" + this.linkToSelectExitParams(tab + 1);
        },
        linkToSelectExitParams: function (tab) {
            return "<button class='hlogin-f-btn-empty' style=\"font-family:" + this.originalFontFamily + "; float:bottom\" tabindex='" + tab + "' onclick='mHLogin.actions.openUserExitPopup();'><div class='hlogin-p-active-link' >" + this.i18n.get('exit_all') + "</div></button>";
        },
        openUserExitPopup: function () {
            this.forcedExit = true;
            this.variableCloseAll();
            this.variableOpenPopup('UserExit');
        },
        errorChangePassword: function (tab) {
            return this.i18n.get('error_change_password') + "<br><br>" +
                this.createLinkToEnter(this.i18n.get('sign_in'), "UserEnter", tab + 1) +
                this.createLinkToEnter(this.i18n.get('forgot_password'), "UserPassword", tab + 1) +
                "<br><br>";
        },
        createBlockUserMessage: function (type) {
            return "<div class='hlogin-p-title-h3' id='HloginMessageInPopupInfoTitle_" + type + "'>MESSAGE_TITLE</div>" +
                "<div id='HloginMessageInPopupInfoError_" + type + "' class='hlogin-p-popup-check-info'>Error_text</div>" +
                "<div class='hlogin-p-text-info-text' id='HloginMessageInPopupInfoText_" + type + "'>Message_text</div><br><br>";
        },
        createBlockMessageFromConfirmEmail: function (type) {
            var tab = 0;
            return "<div class='hlogin-p-title-h3'>" + this.i18n.get('profile') + " " + this.createLinkToEnter(this.i18n.get('exit'), "UserExit", tab + 1, true, 'hlogin-light-alert-color') + "</div>" +
                this.i18n.get('email_not_confirmed') + '.<br><br><br><br>' +
                // Без капчи
                "<div align='center'>" + this.createBlueButtonSend(this.i18n.get('email_confirm_post'), type, tab + 1) + "</div>" +
                this.createLinkToEnter(this.i18n.get('to_main_page'), "GetToMainPage", tab + 1) +
                "<br><br>";
        },
        createBlockMessageAfterContact: function (type) {
            var tab = 0;
            return this.i18n.get('sender_mail') + '.<br><br><br>' +
                this.createLinkToEnter(this.i18n.get('to_main_page'), "GetToMainPage", tab + 1) +
                "<br><br>";
        },
        createBlockEmailConfirmSuccess: function (type) {
            var tab = 0;
            return this.i18n.get('email_confirmed') + '<br><br>' +
                this.createLinkToEnter(this.i18n.get('to_main_page'), "GetToMainPage", tab + 1) +
                this.createLinkToEnter(this.i18n.get('profile'), "UserProfile", tab + 1) +
                this.createLinkToEnter(this.i18n.get('exit'), "UserExit", tab + 1) +
                "<br><br>";
        },
        createBlockGetToMainPage: function (type) {
            location.assign('/');
            return this.i18n.get('redirection_');
        },
        createVisiblePassword: function () {
            return "<div class='hlogin-p-action-page-link hlogin-p-action-password-visible' data-type='password' title='" + this.i18n.get('show_password') + "' onclick='mHLogin.actions.revertPassword(this)'>Aa</div>";
        },
        createBlockInputEmailCell: function (name, placeholder, tabindex, value, dtype) {
            return "<div class='hlogin-g-input-cell-over'><label><div class='hlogin-g-input-cell-text'>" + name + "<span class='hlogin-s-required-marker'>*</span></div><input type='email' tabindex='" + tabindex + "' data-type='" + dtype + "' placeholder='" + placeholder + "' maxlength='255' class='hlogin-g-input-cell' value='" + value + "' data-req='1' pattern='^[-\+\_\\w.]+@([A-z0-9][-A-z0-9]+\\.)+[A-z]{2,10}$' onkeyup='mHLogin.actions.sortFormsOnchange()'></div>";
        },
        createBlockVariableCell: function (name, placeholder, tabindex, value, type, maxlength) {
            if (this.getConfigValue("cell_" + name) !== 'on') return "";
            this.tabCount++;
            var required = this.getConfigValue("required_" + name) == 'on';
            return this.simpleBlockCell(name, required, type, this.i18n.get(placeholder), tabindex, maxlength, value);
        },
        createBlockProfileCell: function (name, placeholder, tabindex, value, type, maxlength) {
            if (this.getConfigValue("profile_" + name) !== 'on') return '';
            this.tabCount++;
            var required = this.getConfigValue("required_" + name) == 'on';
            return this.simpleBlockCell(name, required, type, this.i18n.get(placeholder), tabindex, maxlength, value);
        },
        simpleBlockCell: function(name, required, type, placeholder, tabindex, maxlength, value) {
            return "<div class='hlogin-g-input-cell-over'><label><div class='hlogin-g-input-cell-text'>" + this.i18n.get(name) + (required ? "<span class='hlogin-s-required-marker'>*</span>" : "") + "</div><input type='" + type + "' data-type='" + name + "' placeholder='" + placeholder + "' maxlength='" + maxlength + "'  class='hlogin-g-input-cell' value='" + value + "' tabindex='" + tabindex + "' data-req='" + (required ? 1 : 0) + "' onkeyup='mHLogin.actions.sortFormsOnchange()'></label></div>";
        },
        createBlockInputPasswordCell: function (cell, placeholder, tabindex, dtype, req) {
            this.tabCount++;
            return "<form><div class='hlogin-g-input-cell-over'><label><div class='hlogin-g-input-cell-text'>" + cell + (req ? "<span class='hlogin-s-required-marker'>*</span>" : "") + "</div><input type='password' data-type='" + dtype + "' placeholder='" + placeholder + "' maxlength='100' tabindex='" + tabindex + "' class='hlogin-g-input-password-cell' value='' data-req='" + (req ? 1 : 0) + "' pattern='^[a-zA-Z0-9]{6,}$' onkeyup='mHLogin.actions.sortFormsOnchange()' autocomplete='off'></label></div></form>";
        },
        createSimpleText: function (str) {
            return "<div class='hlogin-p-popup-message-text'>" + this.i18n.get(str) + "</div>";
        },
        createPasswordGenerator: function () {
            return "<div class='hlogin-p-generator'><span class='hlogin-p-action-page-link' onclick='mHLogin.actions.updateAutoPassword(this)'>" + this.i18n.get('generate_password') + "</span><span class='hlogin-p-autogen-pass hlogin-p-autogen-pass-cell--control' style='user-select: text;'></span></div>"
        },
        createRememberText: function (tabindex) {
            return "<label class='hlogin-p-popup-remember-over'>" + this.createVCheckboxInLabel("remember", "hlogin-p-popup-remember-checkbox", false, tabindex, false) + "<div class='hlogin-p-popup-remember-text'> " + this.i18n.get('remember_login') + "</div></label>";
        },
        createConfirmEmail: function (tabindex) {
            return "<label class='hlogin-p-popup-check-email-over'><span class='hlogin-p-check-email'>" + this.createVCheckboxInLabel("checkmail", "hlogin-p-popup-remember-checkbox", true, tabindex, true) + "</span><div class='hlogin-p-popup-remember-text'> " + this.i18n.get('check_email') + "</div></label>";
        },
        createConfirmText: function (tabindex) {
            if (this.getConfigValue("cell_terms") !== 'on') {
                return this.createOnceConfirmText('privacy_policy', tabindex);
            }
            if (this.getConfigValue("cell_privacy_policy") !== 'on') {
                return this.createOnceConfirmText('terms', tabindex);
            }
            this.tabCount++;
            var link_terms = this.getConfigValue("terms_url").replace(/\$LANG/g, this.F.lang);
            var link_privacy_policy = this.getConfigValue("privacy_policy_url").replace(/\$LANG/g, this.F.lang);
            return "<table><tr><td style='vertical-align: top'><label class='hlogin-p-popup-confirm-text-over'> " + this.createVCheckboxInLabel("confirm", "hlogin-p-popup-confirm-checkbox", false, tabindex, true) + "</label></td><td><div class='hlogin-p-popup-confirm-text'>" + this.i18n.get('familar') + " <a href='" + link_terms + "' class='hlogin-g-link-grey hlogin-g-link-line' tabindex='" + tabindex + "' target='_blank'>" + this.i18n.get('terms') + "</a>" + " " + this.i18n.get('and') + " <a href='" + link_privacy_policy + "' class='hlogin-g-link-grey hlogin-g-link-line' tabindex='" + tabindex + "' target='_blank'>" + this.i18n.get('privacy_policy') + "</a>" +
                ", " + this.i18n.get('conditions') + "</div></td></tr></table>";
        },
        createOnceConfirmText: function (name, tabindex) {
            if (this.getConfigValue("cell_" + name) !== 'on') return "";
            this.tabCount++;
            var link = this.getConfigValue(name + "_url").replace(/\$LANG/g, this.F.lang);
            return "<table><tr><td style='vertical-align: top'><label> " + this.createVCheckboxInLabel(name, "hlogin-p-popup-confirm-checkbox", false, tabindex, true) + "</label></td><td><div class='hlogin-p-popup-confirm-text'>" + this.i18n.get('familar') + " <a href='" + link + "' class='hlogin-g-link-grey hlogin-g-link-line' tabindex='" + tabindex + "' target='_blank'>" + this.i18n.get(name) + "</a>" +
                ", " + this.i18n.get('conditions') + "</div></td></tr></table>";
        },
        createBlockSubscriptionCell: function (type, tabindex) {
            return "<table><tr><td style='vertical-align: top'><label> " + this.createVCheckboxInLabel(type, "hlogin-p-popup-confirm-checkbox", false, tabindex, false) + "</label></td><td><div class='hlogin-p-popup-subscription-text'>" + this.i18n.get('subscription') + "</div></td></tr></table>";
        },
        createBlueButtonSend: function (name, type, tabindex) {
            return "<div class='hlogin-p-over-btn-adaptive'><button class='hlogin-f-btn hlogin-f-btn-blue' tabindex='" + tabindex + "' style=\"font-family:" + this.originalFontFamily + "\" data-type='" + type + "' onmousedown='mHLogin.actions.sendDataFromPopup(this)'>" + name + "</button></div>";
        },
        createGreenButtonSend: function (name, type, tabindex) {
            return "<div class='hlogin-p-over-btn-adaptive'><button class='hlogin-f-btn hlogin-f-btn-green' tabindex='" + tabindex + "' style=\"font-family:" + this.originalFontFamily + "\" data-type='" + type + "' onmousedown='mHLogin.actions.sendDataFromPopup(this)'>" + name + "</button></div>";
        },
        createGreenButtonLink: function (name, type, tabindex, path) {
            return "<div class='hlogin-p-over-btn-adaptive'><button class='hlogin-f-btn hlogin-f-btn-green' tabindex='" + tabindex + "' style=\"font-family:" + this.originalFontFamily + "\" data-type='" + type + "' onmousedown='location.assign(\"" + path + "\");'>" + name + "</button></div>";
        },
        // Закрывающая кнопка
        createCloseButton: function (name) {
            return "<div class='hlogin-p-over-btn-adaptive'><button class='hlogin-f-btn hlogin-f-btn-blue hlogin-f-btn-close' style=\"font-family:" + this.originalFontFamily + "\" onmousedown='mHLogin.actions.variableCloseAll()'>" + name + "</button></div>";
        },
        createBlockCaptcha: function () {
            if(this.captchaClose) return '';
            this.tabCount++;
            var cell = "<input type='text' data-type='captcha' placeholder='" + this.i18n.get('captcha_code') + "' maxlength='10'  class='hlogin-g-input-cell hlogin-captcha-cell' value='' tabindex='1' pattern='^[a-zA-Z0-9]{5,6}$' data-req='1' onkeyup='mHLogin.actions.sortFormsOnchange()'>";
            var remove = '<img src="/en/login/resource/version/all/svg/svg/hlogin-remove-img/" class="hlogin-captcha-remove" onmousedown="mHLogin.actions.updateCaptchaCell()">';
            return '<div class="hlogin-captcha-over" align="justify"><img src="' + this.captchaCode + '" height="30" width="120" class="hlogin-captcha">' + remove + cell + '<br></div>';
        },
        updateCaptchaCell: function() {
            if(this.F.captchaActive == 1) {
                var els = this.$$(".hlogin-captcha");
                this.captchaCode = this.generateCaptchaLink();
                for (let e in els) {
                    if (typeof els[e] !== 'undefined' && els[e]) {
                        els[e].src = this.captchaCode;
                    }
                }
            }
        },
        generateCaptchaLink: function() {
            return '/en/login/captcha/' + Math.floor(Math.random() * 1000000) + 1000;
        },
        createBlockMainSDK: function (name) {
            this.tabCount++;
            return "<div id='hloginAddMainSdkCode" + name + "' style='display:none'><!-- Hlogin: Allows you to add your own registration code via the SDK.--></div>";
        },
        createLinkToEnter: function (name, type, tabindex, title, add_class) {
            var large = typeof title !== 'undefined';
            var addClass = typeof add_class != 'undefined' ? add_class : '';
            var largeClass = large ? "hlogin-p-title-h3-copy" : "";
            var style = large ? "float:right" : "";
            return "<button class='hlogin-f-btn-empty' style=\"font-family:" + this.originalFontFamily + "; " + style + "\" tabindex='" + tabindex + "' onmousedown='mHLogin.actions.variableCloseAll(); mHLogin.actions.variableOpenPopup(\"" + type + "\");'><div class='hlogin-p-active-link  " + largeClass + " " + addClass + "' >" + this.i18n.get(name) + "</div></button>";
        },
        createVCheckboxInLabel: function (data_type, class_name, checked, tabindex, req) {
            return "<div></div><input tabindex='" + tabindex + "' type='checkbox'  " + (checked ? "checked='checked'" : "") + " class='hlogin-f-hidden' data-type='" + data_type + "' onchange='mHLogin.actions.changeCheckbox(this); mHLogin.actions.sortFormsOnchange()' data-req='" + (req ? 1 : 0) + "'>" +
                "<img src='/en/login/resource/" + this.F.version + "/" + this.F.design + "/svg/svg/checkbox-dynamic-" + (checked ? "on" : "none") + mHLogin.base.confEndingUrl() + "' class='" + class_name + "' tabindex='" + tabindex + "' onerror='mHLogin.actions.errorCheckboxLoad(this)' onfocus='mHLogin.actions.activateChekboksFromFocus(this)' onblur='mHLogin.actions.deactivateChekboksBlur()' width='20' height='20' alt='checkbox'>";
        },
        activateChekboksFromFocus: function (elem) {
            var th = this;
            this.intervalsFromFocus = setInterval(function () {
                if (th.lastKey != null && th.lastKey.code && th.lastKey.code == "Enter") {
                    th.lastKey = null;
                    elem.parentNode.click();
                }
            }, 100);
        },
        deactivateChekboksBlur: function () {
            this.lastKey = null;
            clearInterval(this.intervalsFromFocus);
        },
        changeCheckbox: function (el) {
            if (el.disabled) return;
            var img = el.parentNode.querySelector("img");
            var checked = (el.checked);
            img.setAttribute("src", "/en/login/resource/" + this.F.version + "/" + this.F.design + "/svg/svg/checkbox-dynamic-" + (checked ? "on" : "none") + mHLogin.base.confEndingUrl());
            return false;
        },
        setImgCheckbox: function (el, checked) {
            if (el) {
                var img = el.querySelector("img");
                if (img) {
                    img.src = "/en/login/resource/" + this.F.version + "/" + this.F.design + "/svg/svg/checkbox-dynamic-" + (checked ? "on" : "none") + mHLogin.base.confEndingUrl();
                }
            }
            return false;
        },
        revertAllImgCheckbox: function (check, ess) {
            for (var el = 0; el < ess.length; el++) {
                if (ess[el]) {
                    this.setImgCheckbox(ess[el].parentNode, check);
                }
            }
            return false;
        },
        errorCheckboxLoad: function (el) {
            el.style.display = "none";
            el.parentNode.querySelector("input").style.display = "inline-block";
            return false;
        },
        updatePasswordCell: function (type, value) {
            var bl = this.$("#" + this.formToPopup[this.activeType] + " input[data-type=password1], #" + this.formToPopup[this.activeType] + " input[data-type=password2]");
            if (bl.length > 0) {
                for (var b in bl) {
                    if (bl[b] && typeof bl[b].parentNode != 'undefined') {
                        var cell = bl[b];
                        if (typeof value !== 'undefined') {
                            cell.value = value;
                        }
                        var selector = cell.parentNode.getElementsByClassName("hlogin-p-action-password-visible");
                        if (selector.length == 0 || (selector.length > 0 && selector[0] && selector[0].getAttribute("data-type") !== 'text')) {
                            cell.type = type;
                        }
                    }
                }
            }
        },
        revertPassword: function (el) {
            var cell = el.parentNode.parentNode.querySelector("input");
            var val = cell.getAttribute("type") == "password";
            el.setAttribute("data-type", val ? "text" : "password");
            cell.setAttribute("type", val ? "text" : "password");
            el.innerHTML = val ? "&bull;&bull;&bull;" : "Aa";
            el.title = val ? this.i18n.get('hide_password') : this.i18n.get('show_password');
        },
        clearAllPasswords: function () {
            var blocks = this.$$(".hlogin-p-action-password-visible");
            for (var i = 0; i < blocks.length; i++) {
                if (blocks[i].getAttribute("data-type") !== "password") {
                    this.revertPassword(blocks[i]);
                }
            }
        },
        updateAutoPassword: function (elem) {
            var cell = elem.parentNode.querySelector(".hlogin-p-autogen-pass-cell--control");
            var password = cell.innerHTML = this.makePassword();
            this.updatePasswordCell('text', password);
            var th = this;
            setTimeout(function () {
                th.updatePasswordCell('password');
            }, 3000);
        },
        makePassword: function () {
            var password = '';
            var chars = 'abcdefghkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ123456789';
            var length = Math.floor(Math.random() * 2) + 8;
            for (var i = 1; i < length; i++) {
                var c = Math.floor(Math.random() * chars.length - 1);
                password += chars.charAt(c);
            }
            return password;
        },
        updateUserProfileData: function (data) {
            this.actualEmail = data['email'];
            for (d in data) {
                var cell = this.$1("#" + this.formToPopup[this.activeType] + " input[data-type=" + d + "]");
                if (typeof cell !== 'undefined' && cell != null) {
                    cell.value = data[d];
                }
            }
        },
        clearErrors: function () {
            this.M.deleteBlocks(this.$(".hlogin-input-error-overlay-block"));
            var els = this.$('.hlogin-input-error');
            for (var i = 0; i < els.length; i++) {
                els[i].classList.remove("hlogin-input-error");
                els[i].classList.remove("hlogin-input-error-checkbox");
            }
        },
        sendDataFromPopup: function (btn) {
            this.viewBlockOverPopup(true);
            btn.setAttribute('disabled', true);
            btn.classList.add('hlogin-btn-disable');
            this.lastBlockedBtnText = btn.innerHTML;
            btn.innerHTML = this.i18n.get("waiting");
            this.clearErrors();
            var type = btn.getAttribute('data-type');
            var popup = this.$1('#HLoginVariablePopup_' + type);
            if (popup != null && typeof popup !== 'undefined') {
                var checked = this.sortFormsData(popup);
                if (checked == 0) {
                    this.actionAjaxRequest(type);
                    this.unlockBtn(7000);
                    return;
                }
            }
            this.unlockBtn(1200);
        },
        loadUserRegisterData: function () {
            this.actionAjaxRequestGetData('GetUserRegisterData');
        },
        unlockBtn: function (time) {
            var btn = this.$1('.hlogin-btn-disable');
            var th = this;
            setTimeout(function () {
                if (btn) {
                    btn.removeAttribute('disabled');
                    btn.classList.remove('hlogin-btn-disable');
                    btn.innerHTML = th.lastBlockedBtnText;
                }
                th.viewBlockOverPopup(false);
            }, time);
        },
        sortFormsOnchange: function (type) {
            if (this.firstDataSended) {
                this.clearErrors();
                this.sortFormsData(this.$1('#HLoginVariablePopup_' + this.lastOpenPopupType));
            }
            if (this.actualEmail != null) {
                var email = this.$1('#HLoginVariablePopup_' + this.lastOpenPopupType + " input[data-type=email]");
                var confirmEmail = this.$1('#HLoginVariablePopup_' + this.lastOpenPopupType + " .hlogin-p-popup-check-email-over");
                if (email && confirmEmail && confirmEmail.style.display != 'block') {
                    if (this.actualEmail !== email.value) {
                        this.changeCheckboxComplect(confirmEmail, false);
                        setTimeout(function () {
                            confirmEmail.style.display = "block";
                        }, 1000);
                    }
                }
            }
            return false;
        },
        changeCheckboxComplect: function(el, checked) {
            el.querySelector("input").checked = checked;
            this.setImgCheckbox(el, checked);
        },
        sortFormsData: function (popup) {
            if (typeof popup)
                var errors = 0;

            var errorsMessage = [];
            var inputs = popup.querySelectorAll("input,textarea");
            var checkbox = popup.querySelectorAll("checkbox");
            var password = null;
            var password1 = null;
            var password2 = null;
            this.firstDataSended = true;
            for (var i = 0; i < inputs.length; i++) {
                var input = inputs[i];
                var value = this.M.trim(input.value);
                var errorBlock = document.createElement('div');
                errorBlock.classList.add('hlogin-input-error-overlay-block');
                if (input && !(input.getAttribute('data-type') === 'checkmail' && popup.querySelector('.hlogin-p-popup-check-email-over').style.display !== 'block')) {
                    if (value.length > 0 && input.getAttribute("data-type") === "password1" || input.getAttribute("data-type") === "password2" || input.getAttribute("data-type") === "password") {
                        if (input.getAttribute("data-type") === "password1") {
                            password1 = input.value;
                        } else if (input.getAttribute("data-type") === "password2") {
                            password2 = input.value;
                        } else if (input.getAttribute("data-type") === "password") {
                            password = input.value;
                        }
                        if (password != null && password1 != null && password === password1) {
                            errorBlock.innerHTML = this.i18n.get("error_password_same");
                            input.parentNode.insertBefore(errorBlock, input.parentNode.childNodes[0]);
                            input.classList.add('hlogin-input-error');
                            errors++;
                        }
                        if (password1 != null && password2 != null && password1 !== password2) {
                            errorBlock.innerHTML = this.i18n.get("error_password_mismatch");
                            input.parentNode.insertBefore(errorBlock, input.parentNode.childNodes[0]);
                            input.classList.add('hlogin-input-error');
                            errors++;
                        }
                    }
                    if (input.getAttribute('type') == 'checkbox') {
                        var img = input.parentNode.querySelector("img");
                        if (input.hasAttribute('data-req') && input.getAttribute('data-req') == "1" && input.checked == false) {
                            img.classList.add('hlogin-input-error');
                            img.classList.add('hlogin-input-error-checkbox');
                            errors++;
                        }
                    } else {
                        if (value.length == 0 && input.hasAttribute('data-req') && input.getAttribute('data-req') == "1") {
                            errorBlock.innerHTML = this.i18n.get("error_empty_data");
                            input.parentNode.insertBefore(errorBlock, input.parentNode.childNodes[0]);
                            input.classList.add('hlogin-input-error');
                            errors++;
                        } else if (value.length > 0 && input.hasAttribute('pattern') && !(new RegExp(input.getAttribute('pattern')).test(value))) {
                            errorBlock.innerHTML = this.i18n.get("error_pattern_data");
                            input.parentNode.insertBefore(errorBlock, input.parentNode.childNodes[0]);
                            input.classList.add('hlogin-input-error');
                            errors++;
                        }
                    }
                }
            }
            return errors;
        },
        setFormToObject: function (type) {
            var inputs = this.$("#HLoginVariablePopup_" + type + " input, " + "#HLoginVariablePopup_" + type + " textarea");
            var obj = {};
            obj.name = type;
            var result = [];
            for (var i = 0; i < inputs.length; i++) {
                result[i] = {};
                if (typeof inputs[i] !== "undefined") {
                    result[i]['type'] = inputs[i].getAttribute("type");
                    result[i]['checked'] = inputs[i].checked ? "checked" : null;
                    result[i]['value'] = inputs[i].value;
                    result[i]['data-type'] = inputs[i].getAttribute("data-type");
                }
            }
            var code = this.M.convertUriToObject('code');
            if (code) {
                obj.code = code;
            }
            obj.data = result;
            return obj;
        },
        // Отправка запроса
        actionAjaxRequest: function (type) {
            var url = "/" + this.F.lang + "/login/data/ajax/" + type.toLowerCase() + "/?" + Math.random();
            var params = this.setFormToObject(type);
            var data = "json_data=" + encodeURIComponent(JSON.stringify(params)) + "&_token=" + mHLogin.base.csrfToken;
            var methodType = "POST";
            var functionName = "fHloginVariableReturn_";
            var contentType = "application/x-www-form-urlencoded";
            this.M.sendAjaxRequest(url, data, methodType, functionName, contentType);
        },
        actionAjaxRequestGetData: function (type) {
            var url = "/" + this.F.lang + "/login/data/ajax/" + type.toLowerCase() + "/?_debug=off&_token=" + mHLogin.base.csrfToken + "&" + Math.random();
            var methodType = "GET";
            var functionName = "fHloginVariableReturn_";
            var contentType = "application/json";
            var data = "json_data={}";
            this.M.sendAjaxRequest(url, data, methodType, functionName, contentType);
        },
        decodeAjaxData: function (data) {
            if (typeof data !== 'undefined') {
                var obj = JSON.parse(this.M.trim(data));
                if (typeof obj.result !== 'undefined' && typeof obj.type !== 'undefined' && typeof obj.error !== 'undefined') {
                    return obj;
                }
            }
            return null;
        },
        timeLeftSet: function () {
            if (this.timeLeftInterval !== 0) clearInterval(this.timeLeftInterval);
            this.timeLeft = 60;
            var th = this;
            this.timeLeftInterval = setInterval(function () {
                var button = th.$1('#HloginUserPasswordButton button');
                if (button) {
                    button.setAttribute("disabled", "disabled");
                }
                th.timeLeft--;
                if (th.timeLeft == 0) {
                    clearInterval(th.timeLeftInterval);
                    if (button) {
                        button.removeAttribute('disabled');
                    }
                }
                var time = th.timeLeft == 0 ? '' : th.i18n.get("will_be_available") + ' ' + th.timeLeft + ' ' + th.i18n.get("seconds");
                var blocks = th.$('.hlogin-v-time-left--control');
                for (var t in blocks) {
                    if (blocks[t]) {
                        blocks[t].innerHTML = time;
                    }
                }
            }, 1000);
        },
        getdAjaxAfter: function (data) {
            this.unlockBtn();
            var d = this.decodeAjaxData(data);
            if (d.error) {
                if (typeof d.error_cells !== 'undefined' && d.error_cells != null) {
                    if (d.type === this.activeType) {
                        for (var i in d.error_cells) {
                            var errorCells = this.$1("#" + this.formToPopup[this.activeType] + " input[data-type=" + d.error_cells[i] + "]");
                            if (errorCells != null) {
                                errorCells.classList.add('hlogin-input-error');
                            }
                        }
                        var sendButton = this.$1("#" + this.formToPopup[this.activeType] + " .hlogin-p-over-btn-adaptive");
                        if (sendButton) {
                            var errorBlock = document.createElement("div");
                            errorBlock.classList.add('hlogin-input-error-overlay-block');
                            errorBlock.innerHTML = d.error;
                            sendButton.parentNode.insertBefore(errorBlock, sendButton);
                        }

                    }
                }
                console.log("error: " + d.error);
            } else {
                this.lastAddedEmail = d.email ? d.email : '';
                var emailCells = this.$('.hlogin-g-input-cell[data-type="email"]');
                for (var e in emailCells) {
                    emailCells[e].value = this.lastAddedEmail;
                }
                var lastType = this.activeType;
                if (lastType == 'UserPassword') {
                    this.timeLeftSet();
                }
                if (!this.captchaClose && d.type && d.result) {
                    this.captchaClose = (['UserRegister', 'UserEnter', 'UserPassword', 'ContactMessage'].indexOf(lastType) != -1);
                    this.clearCaptcha();
                }
                if (d.type !== this.activeType && d.type !== 'UserRegisterData') {
                    this.variableCloseAll();
                    this.viewBlockOverPopup(false);
                    if (d.type) {
                        if (d.type == 'UserMessage') {
                            var message = d.result['message'];
                            switch (lastType) {
                                case 'UserPassword':
                                    message = d.result['message'] + '<br><br>' + this.createLinkToEnter(this.i18n.get('sign_in'), "UserEnter", 1) + this.createLinkToEnter(this.i18n.get('resend_password'), "UserPassword", 1);
                                    break;
                                case 'UserRegister':
                                    message = d.result['message'] + '<br><br>' + this.createLinkToEnter(this.i18n.get('sign_in'), "UserEnter", 1);
                                    break;
                                default:
                            }
                            this.createMessageWindow(d.result['title'], message, false, false);
                        } else if (d.type == 'ReloadPage') {
                            location.reload(true);
                        } else if (d.type == 'RedirectToPage') {
                            location.assign(d.result);
                        } else {
                            this.variableOpenPopup(d.type);
                        }
                    }
                }
                if (d.type == 'UserRegisterData') {
                    this.updateUserProfileData(JSON.parse(d.result));
                }
                if (d.type == 'UserProfile') {
                    this.variableCloseAll();
                    this.viewBlockOverPopup(false);
                    this.createMessageWindow('',  this.i18n.get('data_send') + '.<br><br>' + this.createLinkToEnter(this.i18n.get('profile'), "UserProfile", 1), false, false);
                }
                // Очистка паролей
                var p = this.$1("#" + this.formToPopup[this.activeType] + " input[data-type=password]");
                var p1 = this.$1("#" + this.formToPopup[this.activeType] + " input[data-type=password1]");
                if (p) {
                    p.value = '';
                }
                if (p1) {
                    p1.value = '';
                }
            }
        }
    }
}


if (typeof fHloginVariableReturn_ !== 'function') {
    function fHloginVariableReturn_(data) {
        if (typeof mHLogin == "object") mHLogin.actions.getdAjaxAfter(data);
    }
}
mHLogin.base.registerAMain();
if (typeof hlogin === 'undefined') hlogin = {};
if (typeof hlogin.button === 'undefined') hlogin.button = {
    loadCss: false,
    defaultLangs: ['en', 'ru', 'de', 'es', 'zh'],
    register: function () {
        const th = this;
        var intervalId = setInterval(function () {
            if (document.body !== null && typeof hlogin.script !== 'undefined' && hlogin.script.config) {
                clearInterval(intervalId);
                th.stateOnload();
            }
        }, 20);
    },
    stateOnload: function () {
        const th = this;
        if (!this.loadCss) {
            hlogin.script.loadCss('hloginstylebutton');
            this.loadCss = true;
        }
        var сh = document.createElement('span');
        сh.id = 'hlogin-check-btn-css';
        document.body.appendChild(сh);
        var innerLang = this.defaultLangs.indexOf(hlogin.script.lang) === -1;
        if (innerLang) {
            hlogin.script.loadJs('hloginlang' + hlogin.script.lang);
        }

        var intervalId = setInterval(function () {
            var el = document.getElementById('hlogin-check-btn-css');
            if (el.offsetWidth > 20 &&
                (!innerLang || (
                        typeof hlogin.i18n !== 'undefined' &&
                        typeof hlogin.i18n[hlogin.script.lang] !== 'undefined'
                    )
                )
            ) {
                clearInterval(intervalId);
                el.outerHTML = '';
                th.loadButtons();
            }
        }, 20);
    },
    loadButtons: function() {
        switch(hlogin.script.lang) {
            case 'ru':
                this.i18n = ['Профиль', 'Вход', 'Отправить сообщение', 'Регистрация'];
                break;
            case 'de':
                this.i18n = ['Profil', 'Eintritt', 'Feedback', 'Anmeldung'];
                break;
            case 'es':
                this.i18n = ['Perfil', 'Entrada', 'Comentarios', 'Registración'];
                break;
            case 'zh':
                this.i18n = ['账户', '登录', '反馈', '注册'];
                break;
            case 'en':
                this.i18n = ['Profile', 'Sign In', 'Send Message', 'Registration'];
                break;
            default:
                this.i18n = this.getExternalLangData(hlogin.script.lang);
        }
        var b = document.createElement('div');
        b.id = 'hlogin-buttons';
        b.setAttribute('data-design', hlogin.script.design)

        if (hlogin.script.config.registration.type > 0) {
            b.innerHTML += this.getImage('UserProfile', this.i18n[0], 'user');
            b.innerHTML += this.getLinks([{type: 'UserProfile', name: this.i18n[0]}]);
        } else {
            b.innerHTML += this.getImage('UserEnter', this.i18n[1], 'user');
            b.innerHTML += this.getLinks([{type: 'UserEnter', name: this.i18n[1]}, {type: 'UserRegister', name: this.i18n[3]}]);
        }
        if (hlogin.script.config.contact.active) {
            b.innerHTML += this.getImage('ContactMessage', this.i18n[2], 'contact');
            b.innerHTML += this.getLinks([{type: 'ContactMessage', name: this.i18n[2]}]);
        }
        document.body.appendChild(b);
        hlogin.script.initPopupActions(b);
    },
    reloadButtons: function() {
        var btns = document.getElementById('hlogin-buttons');
        if (btns !== null) {
            btns.outerHTML = '';
        }
    },
    getExternalLangData: function(lang) {
        return [hlogin.i18n[lang].get('profile'), hlogin.i18n[lang].get('sign_in'), hlogin.i18n[lang].get('contact'), hlogin.i18n[lang].get('enter_reg')];
    },

    getImage: function (action, alt, type) {
        var v = 'v' + hlogin.script.version;
        var ds = hlogin.script.design;
        var i = hlogin.script.I;
        return '<button class="hlogin-content-btn hlogin-init-action" data-type="' + action + '" alt="' + alt + '"><img src="/hlresource/hlogin/' + v + '/svg/' + type + ds + i + '" width="30" height="30" id="hlogin-img-' + action + '"></button>';
    },

    getLinks: function(list) {
        var btns = '<span class="hlogin-over-button">';
        for (var l in list) {
            if (l == 1) {
                btns += '<div class="hlogin-btn-delimiter">' + (hlogin.script.lang === 'zh' ? '|' : '/') + '</div>';
            }
            btns += '<button class="hlogin-content-btn hlogin-init-action" data-type="' + list[l].type + '" alt="' + list[l].name + '"><div>' + list[l].name + '</div></button>';
        }
        return btns + '</span>';
    },
}
hlogin.button.register();
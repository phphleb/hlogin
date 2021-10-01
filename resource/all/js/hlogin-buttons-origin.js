if (typeof uHLogin === 'undefined') uHLogin = {};
if (typeof uHLogin.buttons === 'undefined') uHLogin.buttons = {
    lang: 'en',
    design: null,
    orient: null,
    version: null,
    i18n: [],
    class: 'hlogin-buttons-over-block--control',
    classTop: 'hlogin-buttons-over-block-top--control',
    reg: 0,
    isContact: 0,
    init: function() {
        var th = this;
        var interval = setInterval(function() {
            if (typeof uHLogin.functions.registrationData !== null) {
                clearInterval(interval);
                th.design = uHLogin.functions.design;
                th.orient = uHLogin.functions.configData.block_orient;
                th.version = uHLogin.functions.version;
                uHLogin.functions.deleteBlocks(uHLogin.functions.$$('.' + th.class));
                uHLogin.functions.deleteBlocks(uHLogin.functions.$$('.' + th.classTop));
                if (th.orient === 'none') return;
                th.reg = uHLogin.functions.registrationData.userRegister;
                th.lang = uHLogin.functions.lang;
                th.isContact = uHLogin.functions.registrationData.isContact;
                switch(th.lang) {
                    case 'ru':
                        th.i18n = ['Профиль', 'Вход', 'Отправить сообщение', 'Регистрация'];
                        break;
                    case 'de':
                        th.i18n = ['Profil', 'Eintritt', 'Feedback', 'Anmeldung'];
                        break;
                    case 'es':
                        th.i18n = ['Perfil', 'Entrada', 'Comentarios', 'Registración'];
                        break;
                    case 'zh':
                        th.i18n = ['账户', '登录', '反馈', '注册'];
                        break;
                    default:
                        th.i18n = ['Profile', 'Sign In', 'Send Message', 'Registration'];
                }
                if (th.orient == 'top') {
                    th.createTopBlock();
                    // Fix size
                    window.addEventListener("orientationchange", function() {
                        th.stepsForFixSizeButtons();
                    }, false);
                    window.addEventListener("onresize", function() {
                        th.stepsForFixSizeButtons();
                    }, false);
                } else {
                    th.createBlock();
                }
            }
        }, 20);
    },
    stepsForFixSizeButtons: function() {
        var th = this;
        th.fixSizeButtonsBlock();
        var num = 0;
        var steps = setTimeout(function(){
            if(num > 10) {
                clearInterval(steps);
                return;
            }
            th.fixSizeButtonsBlock();
        }, 200);
    },
    fixSizeButtonsBlock: function() {
        var buttons = document.querySelector('.hlogin-buttons-over-block-top--control')
        if(buttons != null && window.outerWidth <= 769) {
            buttons.style.maxWidth = this.isContact == 1 ? '75px' : '30px';
        } else {
            buttons.style.maxWidth = '';
        }
    },
    createTopBlock: function() {
        var el = document.createElement('div');
        el.className += this.classTop + ' hlogin-buttons-design-' + this.design;
        el.style.fontFamily = uHLogin.functions.registrationData.config.font_base;
        var actionIcon = '/en/login/resource/' + this.version + '/' + this.design + '/svg/svg/user-free-icon' + this.confEndingUrl();
        var html = '<table cellpadding="0" cellspacing="0" border="0"><tr><td ' + this.getActionLink('UserEnter') + ' ><img src="' + actionIcon + '" width="30" height="30" alt="' + this.getActionName('UserEnter') + '/' + this.getActionName('UserRegister') + '" ></td><td><span ' + this.getActionLink('UserEnter') + '>' + this.getActionName('UserEnter') + '</span>' + (this.reg > 0 ? '' : '<span>/</span><span ' + this.getActionLink('UserRegister') + '>' + this.getActionName('UserRegister') + '</span>') + '</td>';
        if(this.isContact == 1) {
            var messageIcon = '/en/login/resource/' + this.version + '/' + this.design + '/svg/svg/user-contact-message' + this.confEndingUrl();
            html += '<td width="10"> </td><td ' + this.getContactLink() + '><img src="' + messageIcon + '"  width="30" height="30" alt="' + this.getContactName() + '"></td><td ' + this.getContactLink() + '><span>' + this.getContactName() + '<span></td>';
        }
        el.innerHTML = html + '</tr></table>';
        document.body.appendChild(el);
    },
    createBlock: function() {
        var el = document.createElement('div');
        el.className = this.class + ' hlogin-buttons-' + this.orient + ' hlogin-buttons-design-' + this.design;
        el.style.fontFamily = uHLogin.functions.registrationData.config.font_base;
        var actionIcon = '/en/login/resource/' + this.version + '/' + this.design + '/svg/svg/user-free-icon' + this.confEndingUrl();
        el.innerHTML = '<div id="HloginButtonsMain" ' + this.getActionLink('UserEnter') + ' class="hlogin-buttons-design-' + this.design + '" align="center"><img src="' + actionIcon + '" width="30" height="30"  alt="' + this.getActionName() + '" ></div>';
        if(this.isContact == 1) {
            var messageIcon = '/en/login/resource/' + this.version + '/' + this.design + '/svg/svg/user-contact-message' + this.confEndingUrl();
            el.innerHTML += '<div id="HloginButtonsContact" ' + this.getContactLink() + ' class="hlogin-buttons-design-' + this.design + '" align="center"><img src="' + messageIcon + '" width="30" height="30" alt="' + this.getContactName() + '"></div>';
        }
        document.body.appendChild(el);
    },
    confEndingUrl: function() {
        return uHLogin.functions.confEndingUrl();
    },
    getActionLink: function(type) {
        if(this.reg > 0) {
            return 'onclick="hloginVariableOpenPopup(\'UserProfile\')" title="' + this.getActionName() + '" tabindex="0" ';
        }
        return 'onclick="hloginVariableOpenPopup(\'' + type + '\')" title="' + this.getActionName(type) + '" tabindex="0" ';
    },
    getContactLink: function() {
       return 'onclick="hloginVariableOpenPopup(\'ContactMessage\')" title="' + this.getContactName() + '" tabindex="0" ';
    },
    getActionName: function(type) {
        if(this.reg > 0) {
            return this.i18n[0];
        }

        return type === 'UserEnter' ? this.i18n[1] : this.i18n[3];
    },
    getContactName: function() {
        return this.i18n[2];
    }

    };

uHLogin.buttons.init();

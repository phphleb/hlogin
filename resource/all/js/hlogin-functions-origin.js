console.log(
    '==================' + "\n" +
    'Phphleb/Hlogin 1.4' + "\n" +
    '==================' + "\n"
);
if (typeof uHLogin === 'undefined') uHLogin = {};
if (typeof uHLogin.functions === 'undefined') uHLogin.functions = {
  lang: 'en',
  version: 0,
  pageDesign: null,
  oldDesign: null,
  design: 'base',
  languages: [],
  loadedJs: [],
  cssFilePrefix: 'hlogin-main-css-file-',
  registrationData: null,
  captchaActive: 1,
  configData: {},
  isContact: 0,
  $$: function (val) { // simple dynamic value
    var q = val.substring(1);
    if (val.charAt(0) === "." && q.match(/^[a-z0-9\-\_]+$/i)) {
      return document.getElementsByClassName(q);
    }
    return document.getElementsByTagName(val);
  },
  getActionMethod: function (functionName, value) {
    var th = this;
    var interval = setInterval(function () {
      if (th.registrationData != null) {
        if (typeof hlUnvrsl !== 'object') {
          th.loadJs('/en/login/resource/' + th.version + '/all/js/js/hlogin-default' + th.confEndingUrl());
        }
        if (typeof hlUnvrsl === 'object' && typeof hlUnvrsl.Def !== 'undefined') {
          clearInterval(interval);
          th.loadJs('/en/login/resource/' + th.version + '/all/js/js/hlogin-main' + th.confEndingUrl());
          var getFunctionInterval = setInterval(function () {
            if (typeof mHLogin !== 'undefined' && typeof mHLogin.base !== 'undefined' && typeof hlUnvrsl !== 'undefined' && typeof hlUnvrsl.Def !== 'undefined') {
              clearInterval(getFunctionInterval);
              functionName = 'mHLogin.' + functionName;
              typeof value !== 'undefined' ? eval(functionName + '("' + value + '")') : eval(functionName + '()');
            }
          }, 10);
        }
      }
    }, 10);
  },
  loadRegistrationData: function() {
    if (typeof hlogin_init_script === 'function') {
      this.registrationData = hlogin_init_script();
      this.version = this.registrationData.version;
      this.languages = this.registrationData.languages.split(',');
      this.registrationData.config = this.configData = JSON.parse(this.registrationData.config);
      this.deleteBlocks(uHLogin.functions.$$('.hlogin_init_script'));
      if(this.registrationData.lang) {
        this.lang = this.registrationData.lang;
      } else if(typeof this.registrationData.config.lang !== "undefined" && this.languages.indexOf(this.registrationData.config.lang.toLowerCase()) !== -1) {
        this.lang = this.registrationData.config.lang;
      }
      this.design = this.pageDesign !== null ? this.pageDesign : (this.registrationData.design !== '' ? this.registrationData.design : this.registrationData.config.design);
      this.isContact = this.registrationData.isContact;
      if(this.configData.block_orient !== 'none') {
        this.loadCss('/en/login/resource/' + this.version + '/all/css/css/hlogin-buttons' + this.confEndingUrl());
        this.loadJs('/en/login/resource/' + this.version + '/all/js/js/hlogin-buttons' + this.confEndingUrl());
      }
    }
    this.captchaActive = this.registrationData.captchaActive;
    var th = this;
    var Interval = setInterval(function(){
      if(document.querySelector('html') != null) {
        clearInterval(Interval);
        th.setLang();
      }
    }, 20);
  },
  removeDesign: function (design) {
    var th = uHLogin.functions;
    th.oldDesign = th.oldDesign == null ? th.design : th.oldDesign;
    th.design = design;
    th.pageDesign = design;
    if (typeof mHLogin !== 'undefined') mHLogin.base.design = design;
    var url = '/en/login/resource/' + th.version + '/' + design + '/css/css/hlogin-design' + th.confEndingUrl();
    var blocks = document.querySelectorAll('link[href*="/css/css/hlogin-design"]');
    if (typeof uHLogin.buttons !== 'undefined') uHLogin.buttons.init();
    blocks.length ? th.loadCss(url, blocks[0]) : th.loadCss(url);
    var elements = document.querySelectorAll('.hlogin-p-register-popup-global');
    for (var el of elements) {
      if (typeof el !== 'undefined' && el) {
        el.setAttribute('data-type', design);
      }
    }
    setTimeout( function() {th.deleteBlocks(blocks);}, 1500);
  },
  revertDesign: function() {
    if (uHLogin.functions.oldDesign && uHLogin.functions.design !== uHLogin.functions.oldDesign) {
      this.removeDesign(this.oldDesign);
    }
  },
  // Обрезка указанных символов с двух сторон строки
  trim: function (str, chars) {
    return this.ltrim(this.rtrim(str, chars), chars);
  },
  // Обрезка указанных символов слева
  ltrim: function (str, chars) {
    chars = chars || "\\s";
    return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
  },
  // Обрезка указанных символов справа
  rtrim: function (str, chars) {
    chars = chars || "\\s";
    return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
  },

  // Определение языка
  setLang: function () {
    if(this.registrationData.lang !== '') {
      return this.registrationData.lang;
    }
    var html = document.querySelector('html');
    if (html != null) {
      var langParts = html.getAttribute("lang");
      if (langParts) {
        var lang = this.trim(langParts.toLowerCase().split('-')[0]);
        lang = this.languages.indexOf(lang) !== -1 ? lang : this.lang;
        this.lang = lang;
        return lang;
      }
    }
    var urlParts = document.location.pathname.split("/");
    if(urlParts.length > 1 && this.languages.indexOf(urlParts[1].toLowerCase()) !== -1){
      this.lang = urlParts[1].toLowerCase();
      lang = this.languages.indexOf(lang) !== -1 ? lang : this.lang;
      this.lang = lang;
      return lang;
    }
    return this.lang;
  },
  loadJs: function (file) {
    if (this.loadedJs.indexOf(file) === -1) {
      var script = document.createElement('script');
      script.src = file;
      script.type = 'text/javascript';
      document.body.appendChild(script);
      this.loadedJs.push(file);
    }
  },
  loadCss: function (file, before) {
    var css = document.createElement('link');
    css.rel = "stylesheet";
    css.href = file;
    if(typeof before === 'undefined') {
      document.head.appendChild(css);
    } else {
      document.head.insertBefore(css, before);
    }
  },
  confEndingUrl: function() {
    return this.registrationData.endingUrl ? '/' : '';
  },
  deleteBlocks: function (els) {
    for (var e in els) {
      this.deleteBlock(els[e]);
    }
  },
  deleteBlock: function (element) {
    if (element) {
      element.outerHTML = '';
    }
  },
  openUserMessage: function (title, text, button) {
    this.getActionMethod('actions.variableOpenPopup', 'EmptyBlock');
    var messageInterval = setInterval(function() {
      if (typeof mHLogin !== 'undefined' && typeof mHLogin.actions !== 'undefined') {
        button = typeof button === 'undefined' ? null : button;
        mHLogin.actions.variableCloseAll();
        mHLogin.actions.createMessageWindow(title, text, false, button);
        clearInterval(messageInterval);
      }
    }, 20);
  },
  createButtonInPopup: function (text, onclick, type) {
    return "<div align='center'><div class='hlogin-p-over-btn-adaptive'><button class='hlogin-f-btn hlogin-f-btn-blue hlogin-f-btn-close' tabindex='1' onclick='" + onclick + "' type='" + (typeof type === 'undefined' ? 'button' : type) + "'>" + text.replace(/[\<\>]/g, '') + "</button></div></div>";
  },
  createInputInPopup: function(name, dataType, req, type, placeholder, value, maxlength) {
    return "<div class='hlogin-g-input-cell-over'><label><div class='hlogin-g-input-cell-text'>" + name + "<span class='hlogin-s-required-marker'>" + (req ? "*" : "") + "</span></div><input data-type='" + dataType + "' type='" + (type ? type : "text") + "' tabindex='1' placeholder='" + (placeholder ? placeholder : "") + "' maxlength='" + (maxlength ? maxlength : 255) + "' class='hlogin-g-input-cell' value='" + (value ? value : "") + "' data-req='" + (req ? 1 : 0) + "' onkeyup='mHLogin.actions.sortFormsOnchange()'></div>";
  }
};
uHLogin.functions.loadRegistrationData();

/**
 * Opens the selected Hlogin panel.
 * @param {string} type - 'UserRegister', 'UserEnter', 'UserProfile', 'ContactMessage'...
 */
function hloginVariableOpenPopup(type) {
  uHLogin.functions.getActionMethod('actions.variableOpenPopup', type);
}

/**
 * Sets the local Hlogin panel design.
 *  @param {string} design - 'light', 'dark', 'base'...
 */
function hloginSetDesignToPopups(design) {
  uHLogin.functions.removeDesign(design);
}

/**
 * Returns the design type to its original state.
 */
function hloginRevertDesignToPopups() {
  uHLogin.functions.revertDesign();
}

/**
 * Closes all Hlogin panels.
 */
function hloginCloseAllPopups() {
  mHLogin.actions.variableCloseAll();
}

/**
 * Display a custom message.
 * @param {string} title - The headline of the message.
 * @param {string} text - Message text.
 * @param {(string|false|null)} [button] - Optional text for the consent (close) button.
 */
function hloginOpenMessage(title, text, button){
  uHLogin.functions.openUserMessage(title, text, button);
}

/**
 * Adds its own button (only to the popup).
 * @param text - Button text
 * @param {string} onclick - JS code
 * @param [type] - button|submit|reset
 * @returns {string}
 */
function hloginPopupButton(text, onclick, type) {
  return uHLogin.functions.createButtonInPopup(text, onclick, type);
}

/**
 * Adding your own field to the popup.
 * @param {string} name - Field name
 * @param {string} dataType - Identifier to define on the backend
 * @param {boolean} [isRequired = false] - Mandatory field
 * @param {string} [type = 'text']
 * @param {string} [placeholder = '']
 * @param {string|number} [defaultValue = '']
 * @param {number} [maxlength = 255]
 * @returns {string}
 */
function hloginPopupInput(name, dataType, isRequired, type, placeholder, defaultValue, maxlength) {
  return uHLogin.functions.createInputInPopup(name, dataType, isRequired, type, placeholder, defaultValue, maxlength);
}
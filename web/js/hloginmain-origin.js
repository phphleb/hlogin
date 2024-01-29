if (typeof hlogin === 'undefined') hlogin = {};
if (typeof hlogin.main === 'undefined') hlogin.main = {
    sendAjax: false,
    /**
     * @internal - data can only be sent via hlogin.panel.sendAjaxRequest().
     *           - отправка данных возможна только через hlogin.panel.sendAjaxRequest().
     * @param {object} data - must contain {string}'_token' and {string}'method'.
     *                      - должен содержать {string}'_token' и {string}'method'.
     */
    sendAjaxRequest: function (data) {
        if (!this.sendAjax) {
            var url = "/" + hlogin.script.lang + "/login/data/v"+ hlogin.script.version + '/ajax' + hlogin.script.I + "?" + Math.random();
            this.sendAjax = true;
            var th = this;
            if (data) {
                if (!data.value) {
                    data.value = {};
                }
                data.value.code = hlogin.script.config.code;
            }
            var xhr = th.createAjaxRequest();
            if (xhr) {
                var port = window.location.port !== '' ? ':' + window.location.port : '';
                xhr.open("POST", window.location.protocol + "//" + window.location.hostname + port + url, true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                var params = "json_data=" + encodeURIComponent(JSON.stringify(data)) + '&_token=' + hlogin.script.csrfToken;
                xhr.onreadystatechange = function () {
                    if (this.readyState != 4) return;
                    th.sendAjax = false;
                    hlogin.panel.sendingData = false;
                    th.clearFormNotice();
                    if (this.status == 200) {
                        console.log('Data send.');
                        /* console.log(this.responseText); */
                        th.ajaxResponseHandler(this.responseText);
                    } else {
                        console.log('Data not send.');
                    }
                    hlogin.script.clearSubstrate();
                    hlogin.script.clearPassword();
                    delete xhr;
                }
                xhr.upload.onerror = function() {
                    alert('[' + xhr.status + '] No Internet/API connection :( ');
                    hlogin.panel.sendingData = false;
                };
                /* console.log(params); */
                xhr.send(params);
            }
        }
    },

    // Получение данных для ajax
    createAjaxRequest: function () {
        if (typeof XMLHttpRequest === "undefined") {
            xhr = function () {
                try {
                    return new window.ActiveXObject("Microsoft.XMLHTTP");
                } catch (e) {
                }
            };
        } else {
            var xhr = new XMLHttpRequest();
        }
        return xhr;
    },

    ajaxResponseHandler: function(data) {
        var d = JSON.parse(data);
        var method = d.method;
        var popup = document.body.querySelector('.hlogin-frame-internal');
        if (typeof d.content !== "undefined" && d.content && typeof d.content.captcha !== "undefined" && d.content.captcha) {
            // If the captcha is passed, it will be hidden in any case.
            // Если капча пройдена, то в любом случае будет скрыта.
            hlogin.script.config.captcha.active = false;
            var captcha = popup.querySelector('.hlogin-captcha-over');
            if (captcha) {
                captcha.outerHTML = '';
            }
        }
        if (d.status === 'ok') {
            var ct = d.content;
            if (d.method === 'UserRegister') {
                var uri = hlogin.script.config.registration.src['url-after-reg'] ?? null;
                if (uri) {
                    window.location.href = uri.replace(/\$LANG/g, hlogin.script.lang);
                    return;
                }
            }
            if (ct.action && ct.action.type) {
                if (typeof ct.data !== 'undefined' && ct.data && ct.data.value) {
                    hlogin.panel.actionHandler(ct.action.type, ct.data.value, ct.data.id);
                } else {
                    hlogin.panel.actionHandler(ct.action.type);
                }
            }
            if (d.method === 'UserProfileData') {
                hlogin.panel.updateProfileData(d.content.data);
            }
            if (d.method === 'UserPassword') {
                hlogin.panel.updateTimer();
            }
            if (d.content && typeof d.content.system_message !== 'undefined') {
                var b = popup.querySelectorAll('.hlogin-btn-notice');
                if (b && b.length) {
                    b[b.length - 1].innerHTML = '<div class="hlogin-info-message">' + hlogin.panel.getI18n(d.content.system_message) + '</div>';
                }
            }

        } else {
            if (typeof d.content !== 'undefined') {
                /* console.log(d.method); */
                if (d.method === 'UserRegister' || d.method === 'UserProfile') {
                    if (d.content.data && d.content.data.length && typeof d.content.form['email'] !== 'undefined') {
                        hlogin.panel.replaceEmailRow(d.content.data);
                    }
                }
                popup = document.body.querySelector('.hlogin-frame-internal');
                if (d.content && typeof d.content.form !== 'undefined') {
                    var obj = d.content.form;
                    for (var name in obj) {
                        var e = popup.querySelector('input[data-type=' + name + ']');
                        if (e) {
                            e.classList.add('hlogin-error-border');
                            var n = e.parentNode.querySelector('.hlogin-cell-notice');
                            if (e.classList.contains('hlogin-password-cell')) {
                                var n = e.parentNode.parentNode.querySelector('.hlogin-cell-notice');
                            }
                            if (!n) return;
                            n.innerHTML = hlogin.panel.getI18n(obj[name]);
                        }
                    }
                }
                if (d.content && typeof d.content.system_message !== 'undefined') {
                    var b = popup.querySelectorAll('.hlogin-btn-notice');
                    if (b && b.length) {
                        b[b.length - 1].innerHTML = '<div class="hlogin-error-message">' + hlogin.panel.getI18n(d.content.system_message) + '</div>';
                    }
                }
            }

        }
    },

    /**
     * Clears previous warning messages in the window.
     *
     * Очистка предыдущих предупреждающих сообщений в окне.
     */
    clearFormNotice: function () {
        var popup = document.body.querySelector('.hlogin-frame-internal');
        if (popup) {
            popup.querySelectorAll('.hlogin-btn-notice, .hlogin-cell-notice').forEach(
                function (e) {
                    e.innerHTML = '';
                });
            popup.querySelectorAll('input').forEach(
                function (e) {
                    e.classList.remove('hlogin-error-border');
                });
        }
    }
}
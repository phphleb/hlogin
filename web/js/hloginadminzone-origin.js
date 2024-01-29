if (typeof hlogin === 'undefined') hlogin = {};
if (typeof hlogin.adminzone === 'undefined') hlogin.adminzone = {
    param: null,
    originData: {},
    newData: {},
    sendAjax: false,
    type: null,
    qS: function (p) {
        return document.querySelector(p);
    },
    qSA: function (p) {
        return document.querySelectorAll(p);
    },
    register: function () {
        const th = this;
        var intervalId = setInterval(function () {
            if (document.body !== null && th.qS('.hlogin-az-form')) {
                clearInterval(intervalId);
                th.type = th.qS('.hlogin-az-form').getAttribute('data-type');
                th.stateOnload();
            }
        }, 20);
    },
    /**
     * Initialization of actions.
     *
     * Инициализация действий.
     */
    stateOnload: function () {
        var th = this;
        this.originData = this.getFormData();
        this.newData = this.originData;
        this.param = JSON.parse(this.qS('textarea.hlogin-az-data').innerText);
        if (this.type === 'setting') {
            this.settingActions();
            this.allSettingActions();
        }
        if (this.type === 'additional' || this.type === 'captcha' || this.type === 'email'  || this.type === 'contact' || this.type === 'rights' || this.type === 'users') {
            this.allSettingActions();
        }
        var btn = th.qS('.hlogin-az-btn-submit');
        if (btn) {
            btn.addEventListener('click', function () {
                th.sendFormData();
            });
        }
        var search = th.qS('button.hlogin-az-btn-search');
        if (search) {
            search.addEventListener('click', function () {
                th.searchInputData();
            });
        }
        if (this.type === 'rights' || this.type === 'search') {
            var input = th.qS('.hlogin-az-search-input');
            var button = th.qS('.hlogin-az-btn-search');
            if (input && button) {
                input.addEventListener('keyup', function (event) {
                    if (event.keyCode === 13) {
                        button.click();
                    }
                });
            }
        }
        if (this.type === 'users') {
            var btnShow = th.qS('.hlogin-az-filter-btn-show');
            var btnHide = th.qS('.hlogin-az-filter-btn-hide');
            var btnSend = th.qS('.hlogin-az-filter-btn-send');
            var filters = th.qS('.hlogin-az-filter-block');
            var pages = th.qSA('.hlogin-az-page-btn-link');
            var sortBtns = th.qSA('.hlogin-az-sort-btn');
            if (btnShow) {
                btnShow.addEventListener('click', function () {
                    btnShow.style.display = 'none';
                    filters.style.display = 'block';
                    btnHide.style.display = 'inline-block';
                    btnSend.style.display = 'inline-block';
                });
            }
            if (btnHide) {
                btnHide.addEventListener('click', function () {
                    btnHide.style.display = 'none';
                    filters.style.display = 'none';
                    btnSend.style.display = 'none';
                    btnShow.style.display = 'inline-block';
                });
            }
            if (btnSend) {
                btnSend.addEventListener('click', function () {
                    if (JSON.stringify(th.newData) !== JSON.stringify(th.originData)) {
                        var params = '?filter=1';
                        for (var name in th.newData) {
                            params += '&' + name + '=' + th.newData[name];
                        }
                        btnSend.disabled = true;
                        window.location.href = window.location.pathname + params;
                    }
                });
            }
            if (pages) {
                pages.forEach(function(elm) {
                    elm.addEventListener('click', function (event) {
                        var value = event.target.getAttribute('data-value');
                        th.newData['page'] = value;
                        var params = '?sort=1';
                        for (var name in th.newData) {
                            params += '&' + name + '=' + th.newData[name];
                        }
                        event.target.disabled = true;
                        window.location.href = window.location.pathname + params;
                    });
                });
            }
            if (sortBtns) {
                sortBtns.forEach(function(elm) {
                    elm.addEventListener('click', function (event) {
                        var el = event.target;
                        var parent = el.parentElement;
                        var type = parent.getAttribute('data-type');
                        var value = el.getAttribute('data-value');
                        if (el.classList.contains("hlogin-az-sort-select")) {
                            el.classList.remove("hlogin-az-sort-select");
                            th.newData['sort_' + type] = 0;
                        } else {
                            th.newData['sort_' + type] = value;
                        }
                        var params = '?sort=1';
                        for (var name in th.newData) {
                            params += '&' + name + '=' + th.newData[name];
                        }
                        el.disabled = true;
                        window.location.href = window.location.pathname + params;
                    });
                });
            }
        }
    },

    /**
     * Actions for the 'setting' type panel.
     *
     * Действия для панели типа 'setting'.
     */
    settingActions: function () {
        var n = this.qS('input[name=on_password]');
        var th = this;
        if (n) {
            n.addEventListener('change', function (el) {
                var p = th.qS('input[name=req_password]');
                if (p) {
                    p.checked = n.checked;
                }
            })
        }

        this.synhronize('req_phone', 'on_phone', true);
        this.synhronize('req_name', 'on_name', true);
        this.synhronize('req_surname', 'on_surname', true);
        this.synhronize('req_address', 'on_address', true);

        this.synhronize('on_phone', 'req_phone', false);
        this.synhronize('on_name', 'req_name', false);
        this.synhronize('on_surname', 'req_surname', false);
        this.synhronize('on_address', 'req_address', false);
    },

    /**
     * Allows you to switch the second checkbox depending on the state of the first.
     *
     * Позволяет переключать второй чекбокс в зависимости от состояния первого.
     *
     * @param {string} first
     * @param {string} second
     * @param {boolean} on
     */
    synhronize: function(first, second, on) {
        var adr = this.qS('input[name=' + first + ']');
        var th = this;
        if (adr) {
            adr.addEventListener('change', function (el) {
                var p = th.qS('input[name=' + second + ']');
                if (p && adr.checked == on) {
                    p.checked = adr.checked;
                }
            })
        }
    },

    /**
     * Common actions for all settings panels.
     *
     * Общие действия для всех панелей настроек.
     */
    allSettingActions: function () {
        var th = this;
        var cells = th.qSA('.hlogin-az-form .hlogin-az-options, .hlogin-az-form .hlogin-az-input, .hlogin-az-form .hlogin-az-checkbox, .hlogin-az-form .hlogin-az-textarea');
        cells.forEach(
            function (el) {
                el.addEventListener('change',
                    function () {
                        th.newData = th.getFormData();
                        th.senderActivation(th.ifDataChecked());
                    }
                );
            }
        );
        cells.forEach(
            function (el) {
                el.addEventListener('keyup',
                    function () {
                        th.newData = th.getFormData();
                        th.senderActivation(th.ifDataChecked());
                    }
                );
            }
        );
    },

    /**
     * Submitting form data.
     *
     * Отправка данных формы.
     */
    sendFormData: function () {
        this.sendAjaxRequest(this.newData);
    },

    searchInputData: function () {
        var input = this.qS('.hlogin-az-search-input');
        if (input) {
            window.location.href = window.location.pathname + '?id=' + input.value;
        }
    },

    /**
     * Receiving data from the form.
     *
     * Получение данных из формы.
     *
     * @returns {object}
     */
    getFormData: function () {
        var th = this;
        var data = {};
        var inputs = th.qSA('.hlogin-az-form .hlogin-az-options, .hlogin-az-form .hlogin-az-input, .hlogin-az-form .hlogin-az-textarea');
        inputs.forEach(
            function (i) {
                data[i.getAttribute('name')] = i.value;
            }
        );

        var cbx = th.qSA('.hlogin-az-form .hlogin-az-checkbox');
        cbx.forEach(
            function (c) {
                data[c.getAttribute('name')] = Boolean(c.checked);
            }
        );

        return data;
    },

    /**
     * Checking for changes in data in the form relative to the original data.
     *
     * Проверка на изменение данных в форме относительно оригинальных данных.
     *
     * @returns {boolean}
     */
    ifDataChecked: function () {
        for (var i in this.originData) {
            if (this.newData[i] !== this.originData[i]) {
                return true;
            }
        }
        return false;
    },

    /**
     * Changing elements in response to changes in form data.
     *
     * Изменение элементов как реакция на изменение данных формы.
     *
     * @param status
     */
    senderActivation: function (status) {
        var btn = this.qS('.hlogin-az-btn-submit');
        if (btn) {
            btn.disabled = !status;
            if (status) {
                btn.classList.remove('hlogin-az-btn-disabled');
            } else {
                btn.classList.add('hlogin-az-btn-disabled');
            }
        }
        for (var name in this.originData) {
            var val = this.originData[name];
            var el = this.qS('.hlogin-az-form [name=' + name + ']');
            if (this.newData[name] !== val) {
                el.classList.add('hlogin-az-checked');
            } else {
                el.classList.remove('hlogin-az-checked');
            }
        }
    },


    sendAjaxRequest: function (data) {
        if (!this.sendAjax) {
            var url = "/" + this.param.lang + "/adminzone/registration/data/" + this.type + (this.param.ending ? '/' : '') + "?" + Math.random();
            this.sendAjax = true;
            var th = this;
            var xhr = th.createAjaxRequest();
            if (xhr) {
                var port = window.location.port !== '' ? ':' + window.location.port : '';
                xhr.open("POST", window.location.protocol + "//" + window.location.hostname + port + url, true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                var params = "json_data=" + encodeURIComponent(JSON.stringify(data)) + '&_token=' + this.param.token;
                xhr.onreadystatechange = function () {
                    if (this.readyState != 4) return;
                    th.sendAjax = false;
                    if (this.status == 200) {
                        console.log('Data send.');
                        /* console.log(this.responseText); */
                        th.ajaxResponseHandler(JSON.parse(this.responseText));
                    } else {
                        console.log('Data not send.');
                    }
                    delete xhr;
                }
                xhr.upload.onerror = function () {
                    th.createInfoMessage('error', '[' + xhr.status + '] No Internet/API connection :( ');
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

    ajaxResponseHandler: function (response) {
        if (response.status === 'ok') {
            window.location.reload();
            return;
        }
        if (response.status === 'error') {
            this.createInfoMessage('error', response.message);
        }

    },

    createInfoMessage(type, text) {
        var bl = document.createElement('div');
        bl.classList.add('hlogin-az-message-' + type);
        bl.innerHTML = text;
        bl.style.opacity = '1';
        document.body.appendChild(bl);
        var th = this;
        setTimeout(function () {
            var opacity = 1;
            var interval = setInterval(
                function () {
                    opacity = opacity - 0.01;
                    var el = th.qS('.hlogin-az-message-' + type);
                    if (opacity < 0.1) {
                        if (el) {
                            clearInterval(interval);
                            el.outerHTML = '';
                        }
                    }
                    if (el) {
                        el.style.opacity = String(opacity);
                    }
                }, 40
            );
        }, 4000);
    }
}
hlogin.adminzone.register();
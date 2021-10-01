if (typeof mHLogin === 'undefined') mHLogin = {};
if (typeof mHLogin.adminzone === 'undefined') {
    mHLogin.adminzone = {
        formData: {},
        // Инициализатор по загрузке страницы
        registerAMain: function () {
            const th = this;
            var intervalId = setInterval(function () {
                if (document.body !== null) {
                    clearInterval(intervalId);
                    th.stateOnloadAMain();
                }
            }, 20);
        },
        stateOnloadAMain: function () {

        },
        hloginFilterSend: function() {
            var list = {};
            var errors = 0;
            var result = 0;
            for(var i=1; i<4; i++) {
                var block = {};
                var filters = document.querySelectorAll('.hl-filter-' + i);
                var count = 0;
                for (var f in filters) {
                    if(filters[f] && filters[f] != undefined && filters[f].style && filters[f].style !== undefined) {
                        filters[f].style.backgroundColor = '';
                        if (filters[f].value && this.trim(String(filters[f].value), ' ') !== '') {
                            var value = String(filters[f].value);
                            if (filters[f].name === 'value') {
                                if (!(new RegExp(/^[0-9a-zA-Z\_\ \.\@\-\:\;\&]{1,50}$/).test(value))) {
                                    filters[f].style.backgroundColor = '#BD6F5F';
                                    errors++;
                                } else {
                                    filters[f].style.backgroundColor = '#1EA355';
                                    if(filters[0].value == "") {
                                        filters[0].style.backgroundColor = '#BD6F5F';
                                        errors++;
                                    }
                                }
                            }
                            if (filters[f].name === 'name' || filters[f].name === 'selector' || filters[f].name === 'value') {
                                block[filters[f].name] = value;
                                count++;
                            }
                        } else {
                            if(filters[0].value == "") {
                                filters[f].style.backgroundColor = '';
                            } else {
                                filters[f].style.backgroundColor = '#BD6F5F';
                                errors++;
                            }
                        }
                    }
                }
                if(count == 3) {
                    list[i] = block;
                    result++;
                }
            }
            if(errors == 0) {
                this.createFilters(list);
            }
        },
        createFilters: function (list) {
            var get = this.paramsToList();
            get.filter = JSON.stringify(list);
            get.page = 1;
            var params = '?';
            for(var g in get) {
                params += g + '=' + get[g] + '&';
            }
            document.querySelector('.hl-over-fon').style.display = 'block';

            var url = location.protocol + '//' + location.host + location.pathname + params;
            location.replace(url);
        },
        paramsToList: function() {
            var get = {};
            var re = /[?&]([^=&]+)(=?)([^&]*)/g;
            while (m = re.exec(location.search))
                get[decodeURIComponent(m[1])] = (m[2] == '=' ? decodeURIComponent(m[3]) : true);
            return get;
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
        reloadSendButton: function() {
            setTimeout(function() {
                var el = document.getElementById('hloginSendButton');
                if(el) {
                el.setAttribute('disabled', true);
                el.style.opacity = '0.7';
                }
            }, 3000);
        }

    }
}
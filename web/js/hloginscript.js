console.log('%c Phphleb/Hlogin %c v2 ',"color:#fff; background: #3f9adb; padding: 2px","color:#000; background:#ccc; padding: 2px");if(typeof hlogin==='undefined')hlogin={};if(typeof hlogin.script==='undefined')hlogin.script={config:null,version:null,defaultDesign:null,design:null,I:'',defaultLang:null,lang:null,languages:[],loadedJs:[],panelLoaded:!1,loadProcess:!1,hasPreloader:!1,csrfToken:null,popups:['UserEnter','UserPassword','UserProfile','UserRegister','ContactMessage','CustomMessage','NewPassword','ConfirmEmail',],actions:['ChangeDesign','DefaultDesign','ChangeLang','DefaultLang','CloseAllPopups','ReloadPage','ReloadCaptcha','RedirectToPage','UserProfileData','UserExit','AdminzoneEnter','UserFullExit','ToHomepage','RegisterEmail','CustomEmailMessage',],register:function(){const th=this;var intervalId=setInterval(function(){if(document.body!==null){clearInterval(intervalId);th.stateOnload()}},20)},stateOnload:function(){var p=document.getElementById('JsWarning');if(p){p.outerHTML=''}
    var d=document.getElementById('hlogin_init_script');var config=JSON.parse(d.dataset.config.replace(/&apos;/,'\''));this.I=config.endingUrl?'/':'';this.lang=config.lang;this.csrfToken=config.csrfToken;this.defaultLang=config.lang;this.setLang();this.languages=config.languages;this.version=config.config.version;this.design=config.config.design;this.defaultDesign=config.config.design;this.config=config.config;d.outerHTML='';this.initDemoSelectors();this.initPopupActions(document.body);var th=this;setInterval(function(){th.initPopupActions(document.body)},500);setInterval(function(){th.initPanelScrollAction()},20);this.loadCss('hloginstyle'+this.design);if(this.config.startCommand){this.runAction(this.config.startCommand)}},loadJs:function(name){if(this.loadedJs.indexOf(name)===-1){var script=document.createElement('script');script.src='/hlresource/hlogin/v'+hlogin.script.version+'/js/'+name+this.I;script.async=!0;script.type='text/javascript';document.body.appendChild(script);this.loadedJs.push(name)}},loadCss:function(name,before){var css=document.createElement('link');css.rel="stylesheet";css.href='/hlresource/hlogin/v'+hlogin.script.version+'/css/'+name+this.I;if(typeof before==='undefined'){document.head.appendChild(css)}else{document.head.insertBefore(css,before)}},els:function(val){var q=val.substring(1);if(val.charAt(0)==="."&&q.match(/^[a-z0-9\-\_]+$/i)){return document.getElementsByClassName(q)}
    return document.getElementsByTagName(val)},setLang:function(){var html=this.els('html')[0];if(html!=null){var langParts=html.getAttribute("lang");if(langParts){var lang=this.trim(langParts.toLowerCase().split('-')[0]);this.lang=this.languages.indexOf(lang)!==-1&&lang.length==2?lang:this.lang;this.defaultLang=this.lang;return}}
    var urlParts=document.location.pathname.split("/");if(urlParts.length>1&&this.languages.indexOf(urlParts[1].toLowerCase())!==-1){this.lang=urlParts[1].toLowerCase();this.defaultLang=this.lang;return}},trim:function(str,chars){return this.ltrim(this.rtrim(str,chars),chars)},ltrim:function(str,chars){chars=chars||"\\s";return str.replace(new RegExp("^["+chars+"]+","g"),"")},rtrim:function(str,chars){chars=chars||"\\s";return str.replace(new RegExp("["+chars+"]+$","g"),"")},runAction:function(type,value,title){if(this.loadProcess){return}
    const th=this;this.loadProcess=!0;if(this.panelLoaded){if(th.popups.indexOf(type)!==-1){th.openSubstrate(!0)}
        setTimeout(function(){hlogin.panel.actionHandler(type,value,title);th.loadProcess=!1},100)}else{if(th.popups.indexOf(type)!==-1){th.openSubstrate(!0)}
        this.loadJs('hloginpanel');this.loadJs('hloginlang'+th.lang);var intervalId=setInterval(function(){if(typeof hlogin.panel!=='undefined'){clearInterval(intervalId);hlogin.panel.actionHandler(type,value,title);th.loadProcess=!1;th.panelLoaded=!0}},20)}},openSubstrate:function(closed){var l=document.getElementById('hlogin-substrate')
    if(l){l.style.display='block';return}
    var l=document.createElement('div');l.id='hlogin-substrate';l.style='background-color:#dcd9d9;opacity:0.001;z-index:2147483647;position:absolute;top:0;left:0;width:100%;height:100%'
    document.body.appendChild(l);var timeout=setTimeout(function(){var b=document.getElementById('hlogin-substrate');if(b){b.style.opacity='0.4'}},500);this.hasPreloader=!0;var th=this;if(closed){l.addEventListener('click',function(e){clearTimeout(timeout);th.clearSubstrate()})}
    setTimeout(function(){clearTimeout(timeout);th.clearSubstrate()},7000)},clearSubstrate:function(){var b=document.getElementById('hlogin-substrate');if(b){b.outerHTML='';this.hasPreloader=!1}},clearPassword:function(){document.querySelectorAll('.hlogin-over-panel input.hlogin-password-cell').forEach(function(el){el.value=''})},initPopupActions:function(obj){var th=this;obj.querySelectorAll('.hlogin-init-action:not([data-active="on"])').forEach(function(e){var a=e.dataset.active==='on';if(a){return}
    e.dataset.active='on';e.addEventListener('click',function(){if(e.dataset.type){th.runAction(e.dataset.type,e.dataset.value,e.dataset.title)}})})},initPanelScrollAction:function(){var el=document.querySelector('.hlogin-outer-x');var pan=document.querySelector('.hlogin-frame-over');var over=document.querySelector('.hlogin-over-panel');if(el&&pan){if(pan.scrollHeight>pan.clientHeight){el.classList.add('hlogin-pan-scroll');over.classList.add('hlogin-pan-scroll-over')}else{el.classList.remove('hlogin-pan-scroll')}}},initDemoSelectors:function(){if(!document.getElementById('hlogin-path-selector')){return}
    document.getElementById('hlogin-path-selector').addEventListener('change',function(e){document.getElementById('hlogin-path-link').href='/'+e.target.value+'/login/profile/';hlogin.script.runAction('ChangeLang',e.target.value)});document.getElementById('hlogin-select-action').addEventListener('change',function(e){hlogin.script.runAction('ChangeDesign',e.target.value)})},}
hlogin.script.register();function hloginSetDesignToPopups(design){hlogin.script.runAction('ChangeDesign',design)}
function hloginRevertDesignToPopups(){hlogin.script.runAction('DefaultLang')}
function hloginCloseAllPopups(){hlogin.script.runAction('CloseAllPopups')}
function hloginVariableOpenPopup(type){hlogin.script.runAction(type)}
function hloginOpenMessage(title,content){hlogin.script.runAction('CustomMessage',content,title)}
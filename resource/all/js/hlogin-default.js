if(typeof hlUnvrsl==='undefined')hlUnvrsl={};if(typeof hlUnvrsl.Def==='undefined')hlUnvrsl.Def={sendAjax:!1,loadedJs:[],registrationData:{},$:function(val){var q=val.substring(1);if(val.charAt(0)==="#"&&q.match(/^[a-z0-9\-\_]+$/i)){return document.getElementById(q)}
return document.querySelectorAll(val)},$$:function(val){var q=val.substring(1);if(val.charAt(0)==="."&&q.match(/^[a-z0-9\-\_]+$/i)){return document.getElementsByClassName(q)}
return document.getElementsByTagName(val)},$1:function(val){var q=val.substring(1);if(val.charAt(0)==="#"&&q.match(/^[a-z0-9\-\_]+$/i)){return document.getElementById(q)}
return document.querySelector(val)},convertUriToObject:function(name){var obj=this.convertStringParamsToObject(window.location.search);if(typeof name==='undefined'){return obj}
if(typeof obj[name]!=='undefined'){return obj[name]}
return null},convertStringParamsToObject:function(str){if(str.length===0||(str.charAt(0)==='?'&&str.length===1)){return{}}
str=str.charAt(0)==='?'?str.substring(1):str;return JSON.parse('{"'+decodeURI(str.replace(/&/g,"\",\"").replace(/=/g,"\":\""))+'"}')},setLang:function(target){if(typeof target!=='object')return;var html=this.$1("html");if(html!=null){var langParts=html.getAttribute("lang");if(langParts){var lang=this.trim(langParts.toLowerCase().split('-')[0]);target.lang=target.languages.indexOf(lang)!==-1?lang:target.lang;return}}
var urlParts=document.location.pathname.split("/");if(urlParts.length>1&&target.languages.indexOf(urlParts[1].toLowerCase())!==-1){target.lang=urlParts[1].toLowerCase();return}},inFrame:function(){if(typeof this.inFramePerem==='undefined'){try{this.inFramePerem=window.self!==window.top}catch(e){this.inFramePerem=!0}}
return this.inFramePerem},trim:function(str,chars){return this.ltrim(this.rtrim(str,chars),chars)},ltrim:function(str,chars){chars=chars||"\\s";return str.replace(new RegExp("^["+chars+"]+","g"),"")},rtrim:function(str,chars){chars=chars||"\\s";return str.replace(new RegExp("["+chars+"]+$","g"),"")},revertShowBlock:function(id){return this.revertShowElement(id,"block")},revertShowInlineBlock:function(id){return this.revertShowElement(id,"inline-block")},showBlock:function(id){this.$("#"+id).style.display="block";return!1},showInlineBlock:function(id){this.$("#"+id).style.display="inline-block";return!1},closeBlock:function(id){this.$("#"+id).style.display="none";return!1},deleteBlocks:function(els){for(var e in els){this.deleteBlock(els[e])}},deleteBlock:function(element){if(element){element.outerHTML=''}},revertShowElement:function(id,type){var block=this.$("#"+id);if(block.style.display==="none"){block.style.display=type}else{block.style.display="none"}
return!1},revertShowObject:function(object,type){if(object.style.display==="none"){object.style.display=type}else{object.style.display="none"}
return!1},scrollElementTop:function(el){el.scrollTop=-(el.scrollTop);el.scrollTop=0},scrollElementsTop:function(els){for(var el in els){this.scrollElementTop(els[el])}},scrTop:function(ms){scrollTop(ms)},scrLeft:function(ms){this.scrollLeft(ms)},scrollTop:function(ms){setTimeout(function(){document.body.scrollTop=-(document.body.scrollTop);document.documentElement.scrollTop=-(document.documentElement.scrollTop);document.body.scrollTop=document.documentElement.scrollTop=0},ms?ms:0)},scrollLeft:function(ms){setTimeout(function(){document.body.scrollLeft=-(document.body.scrollLeft);document.documentElement.scrollLeft=-(document.documentElement.scrollLeft);document.body.scrollLeft=document.documentElement.scrollLeft=0},ms?ms:0)},sizeW:function(){return window.innerWidth||document.documentElement.clientWidth||document.body.clientWidth},sizeH:function(){return window.innerHeight||document.documentElement.clientHeight||document.body.clientHeight},createAjaxRequest:function(){if(typeof XMLHttpRequest==="undefined"){xhr=function(){try{return new window.ActiveXObject("Microsoft.XMLHTTP")}catch(e){}}}else{var xhr=new XMLHttpRequest()}
return xhr},addBodyOverflow:function(){this.$1("body").classList.add("hlogin-f-src-open-window");this.$1("html").classList.add("hlogin-f-src-open-window")},removeBodyOverflow:function(){this.$1("body").classList.remove("hlogin-f-src-open-window");this.$1("html").classList.remove("hlogin-f-src-open-window")},getRandomInt:function(min,max){return Math.floor(Math.random()*(max-min))+min},loadJs:function(file){if(this.loadedJs.indexOf(file)===-1){var script=document.createElement('script');script.src=file;script.type='text/javascript';this.$1('body').appendChild(script);this.loadedJs.push(file)}},loadCss:function(file){var css=document.createElement('link');css.rel="stylesheet";css.href=file;document.head.appendChild(css)},confEndingUrl:function(){return this.registrationData.endingUrl?'/':''},sendAjaxRequest:function(url,data,methodType,functionName,contentType){if(!this.sendAjax){this.sendAjax=!0;var th=this;var xhr=th.createAjaxRequest();if(xhr){var port=window.location.port!==''?':'+window.location.port:'';xhr.open(methodType,window.location.protocol+"//"+window.location.hostname+port+url,!0);xhr.setRequestHeader("Content-Type",contentType);xhr.onreadystatechange=function(){if(this.readyState!=4)return;th.sendAjax=!1;if(this.status==200){console.log('Data send.');if(typeof functionName!=='undefined'&&typeof window[functionName]!=='undefined'&&functionName){window[functionName](this.responseText)}}else{console.log('Data not send.')}
delete xhr}
xhr.upload.onerror=function(){alert('['+xhr.status+'] No Internet/API connection :( ')};xhr.send(data)}}},}
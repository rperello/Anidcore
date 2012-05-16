/**
 * AnidCore JS tools
 */

ac = {
    orientation : 'landscape',
    init : function(){
        $(function(){
            ac.detectOrientation();
            $(document).bind("orientationchange", ac.detectOrientation);
            if(ac.isMobile()) $("html").addClass("is-mobile");
            else $("html").addClass("no-mobile");
        });
    },
    isMobile: function(){
        return (/iphone|ipad|ipod|android|blackberry|symbian|mini|windows\sce|iemobile|psp|nokia|palm/i.test(navigator.userAgent.toLowerCase()));
    },
    detectOrientation:function(e){
        e = e || null;
        
        var deg=0, orientation = "landscape";
        
        if(window.orientation===undefined){
            var $w=$(window);
            if($w.width() >= $w.height())
                deg=90;
            else deg=0;
        }else{
            deg=window.orientation;
        }

        switch(deg){
            case 0:
            case 180:
            default:
                orientation= "portrait";
                break;  
            
            case -90:
            case 90:
                orientation= "landscape";
                break;
        }
        
        if(orientation=="portrait"){
            $("html").removeClass("orientation-landscape").addClass("orientation-portrait");
        }else if(orientation=="landscape"){
            $("html").removeClass("orientation-portrait").addClass("orientation-landscape");
        }
        
        //alert(orientation);
        
        ac.orientation=orientation;
        
        return orientation;
    },
    preventTouchMove: function(){
        $(document).bind("touchmove", function(e){
            e.preventDefault(); 
        });
    },
    preventScroll: function(){
        $("html, body").css({
            "overflow":"hidden"
        });
    },
    
    getFieldsAsQueryString: function(containers){
        var params = "";
        
        $("input[name]:not([type=reset]),textarea[name],select[name],button[name]:not([type=reset])", $(containers)).each(function(){
            
            var $this = $(this);
            
            if($this.hasClass("ckeditor") || $this.hasClass("rte")){
                params+="&"+this.name+"="+encodeURIComponent(ac.base64.encode(ac.cssClean($this.val())));
            }else if(($this.attr("type")!==undefined) && ($this.attr("type").toLowerCase()=="checkbox")){
                if (this.checked){
                    params+="&"+this.name+"="+encodeURIComponent($this.val());
                }else{
                    if(($this.attr("value")==1)&&($this.hasClass('boolean') || $this.hasClass('bool'))){
                        params+="&"+this.name+"=0";
                    }
                }
            }else if(($this.attr("type")!==undefined) && ($this.attr("type").toLowerCase()=="radio")){
                if(this.checked){
                    params+="&"+this.name+"="+encodeURIComponent($this.val());
                }
            }else if($this.hasClass('currency')){
                if($this.hasClass('integer')){
                    params+="&"+this.name+"="+encodeURIComponent($this.val().split(".",2).shift().replace(/\.|\,|\€|\$|\¥|\s/gi,""));
                }else if($this.hasClass('cents')){
                    params+="&"+this.name+"="+encodeURIComponent($this.val().replace(/\.|\,|\€|\$|\¥|\s/gi,""));
                }else{
                    params+="&"+this.name+"="+encodeURIComponent($this.val().replace(/\,|\€|\$|\¥|\s/gi,""));
                }
            }else{
                if($this.hasClass('number')){
                    params+="&"+this.name+"="+encodeURIComponent($this.val().replace(/\,/gi,"."));
                }else{
                    params+="&"+this.name+"="+encodeURIComponent($this.val());
                }
            }
        });
        return params;
    },
    
    formPost : function(containers, url, callback, mode){
        callback = callback || function(){};
        mode = mode || null;
        $.post(url,$.getFieldsAsQueryString(containers),callback,mode);
    },
    
    getUrlParams: function(){
        var vars = [], hash;
        var href = window.location.href.split("#")[0];
        var hashes = href.slice(href.indexOf('?') + 1).split('&');
        for(var i = 0; i < hashes.length; i++)
        {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            if((hash[1]==undefined) || (hash[1]==null) || (hash[1].length==0)) hash[1]=true;
            vars[hash[0]] = hash[1];
        }
        return vars;
    },
    getUrlParam: function(name){
        return ac.getUrlParams()[name];
    },
    
    cssClean : function(str){
        str=str.replace(/<\/?font[^>]*>/gi, "");
        str=str.replace(/font:[^>]*;/gi, "");
        str=str.replace(/font-family:[^>]*;/gi, "");
        str=str.replace(/background-color:[^>]*;/gi, "");
        str=str.replace(/background:[^>]*;/gi, "");
        str=str.replace(/line-height:[^>]*;/gi, "");
        str=str.replace(/letter-spacing:[^>]*;/gi, "");
        str=str.replace(/word-spacing:[^>]*;/gi, "");
        return str;
    },
    
    clearSelection:function(){
        var sel = null;
        if(document.selection && document.selection.empty){
            document.selection.empty();
        }else{
            if(window.getSelection) {
                sel=window.getSelection();
                if(sel && sel.removeAllRanges) sel.removeAllRanges();
            }
        }
    },
    
    randomInt:function(min, max){
        return Math.floor(Math.random() * (max - min + 1)) + min;
    },
    
    scrollTo: function(selector, animate, duration, easing, callback){
        selector = selector || this;
        animate = animate || true;
        duration = duration || 1000;
        easing = easing || "swing";  //swing, linear
        callback = callback || function(){};

        if(animate){
            $('html,body').animate({scrollTop: ($(selector).offset().top)}, duration, easing, callback);
        }else{
            $('html,body').scrollTop($(selector).offset().top);
            callback();
        }
    },
    blinkClass: function(selector, togglerClass, interval){
        var _el = $(selector).first();
        interval = interval || 1000;
        
        return setInterval(function(){
            _el.toggleClass(togglerClass);
        },interval);
    }
}

function ac_log($Object, $Label, $Type){
    $Label = $Label || null;
    $Type = $Type || "log";
    
    switch($Type){
        case "error":{
            console.error($Object, $Label);
        }break;
        case "warn":{
            console.warn($Object, $Label);
        }break;
        case "info":{
            console.info($Object, $Label);
        }break;
        default:{
            console.log($Object, $Label);
        }break;
    }
}

$.jsonrpc = {
    requestMessage: function(id, method, params, version){
        id = id || ac.randomInt(1, 10000);
        method = method || null;
        params = params || [];
        version = version || "2.0";
        return {"jsonrpc":version, "id":id, "method":method, "params":params};
    },
    notificationMessage: function(method, params, version){
        method = method || null;
        params = params || [];
        version = version || "2.0";
        return {"jsonrpc":version, "method":method, "params":params};
    }
    
}

function ac_redirect($url){
    window.location.href=$url;
}

/**
*
*  Base64 encode / decode
*  http://www.webtoolkit.info/
*
**/
 
ac.base64 = {
	// private property
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
 
	// public method for encoding
	encode : function (txt, url_safe) {
            url_safe = url_safe || false;
		var _output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;
 
		txt = ac.base64._utf8_encode(txt);
 
		while (i < txt.length) {
 
			chr1 = txt.charCodeAt(i++);
			chr2 = txt.charCodeAt(i++);
			chr3 = txt.charCodeAt(i++);
 
			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;
 
			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}
 
			_output = _output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
 
		}
                if(url_safe) return _output.replace(/\+/g,"-").replace(/\//g,"_").replace(/\=/g,"");
		return _output;
	},
 
	// public method for decoding
	decode : function (txt, url_safe) {
            url_safe = url_safe || false;
            if(url_safe){
                txt = txt.replace(/\-/g, '+').replace(/\_/g, '/');
                var mod4 = (txt.length % 4);
                if (mod4) {
                    txt = txt + ('===='.substr(mod4));
                }
            }
		var _output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;
 
		txt = txt.replace(/[^A-Za-z0-9\+\/\=]/g, "");
 
		while (i < txt.length) {
 
			enc1 = this._keyStr.indexOf(txt.charAt(i++));
			enc2 = this._keyStr.indexOf(txt.charAt(i++));
			enc3 = this._keyStr.indexOf(txt.charAt(i++));
			enc4 = this._keyStr.indexOf(txt.charAt(i++));
 
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
 
			_output = _output + String.fromCharCode(chr1);
 
			if (enc3 != 64) {
				_output = _output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				_output = _output + String.fromCharCode(chr3);
			}
 
		}
 
		_output = ac.base64._utf8_decode(_output);
                
		return _output;
 
	},
 
	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
 
		for (var n = 0; n < string.length; n++) {
 
			var c = string.charCodeAt(n);
 
			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
 
		}
 
		return utftext;
	},
 
	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c, c1, c2, c3;
		c = c1 = c2 = 0;
 
		while ( i < utftext.length ) {
 
			c = utftext.charCodeAt(i);
 
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
 
		}
 
		return string;
	}
}

$.fn.equalHeights = function(){
    return this.height( Math.max.apply(this, $.map( this , function(e){
        return $(e).height()
        }) ) );
}
$.fn.animateClass=function(className, duration){
    duration = duration || null;
    if($(this).hasClass(className)){
        $(this).switchClass(className, className+"-off", duration);
    }else{
        $(this).switchClass("", className, duration);
    }
}


$.isJSON = function(str) {
    if ($.trim(str) == '') return false;
    str = str.replace(/\\./g, '@').replace(/"[^"\\\n\r]*"/g, '');
    return (/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]*$/).test(str);
}

if($.toJSON==undefined){
    $.toJSON = function (str){
        return eval('(' + str + ')');
    }
}

//Case insensitive :contains selector
$.expr[':'].icontains = function(a,i,m){
    return $(a).text().toUpperCase().indexOf(m[3].toUpperCase())>=0;
};

//Query elements by css style
$.expr[':'].css = function(obj, index, meta, stack){
    var params = meta[3].split(',');

    return ($(obj).css(params[0]) == params[1]);
};

$.fn.marktext = function(pat) {
    function innerMarktext(node, pat) {
        var skip = 0;
        if (node.nodeType == 3) {
            var pos = node.data.toUpperCase().indexOf(pat);
            if (pos >= 0) {
                var spannode = document.createElement('span');
                spannode.className = 'mark';
                var middlebit = node.splitText(pos);
                var endbit = middlebit.splitText(pat.length);
                var middleclone = middlebit.cloneNode(true);
                spannode.appendChild(middleclone);
                middlebit.parentNode.replaceChild(spannode, middlebit);
                skip = 1;
            }
        }
        else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
            for (var i = 0; i < node.childNodes.length; ++i) {
                i += innerMarktext(node.childNodes[i], pat);
            }
        }
        return skip;
    }
    return this.each(function() {
        innerMarktext(this, pat.toUpperCase());
    });
};

$.fn.removeMarktext = function() {
    return this.find("span.mark").each(function() {
        this.parentNode.firstChild.nodeName;
        with (this.parentNode) {
            replaceChild(this.firstChild, this);
            normalize();
            }
    }).end();
};


$.fn.domSort = (function(){
    var sort = [].sort;

    return function(desc, getSortable, comparatorFunction) {
        desc = desc || false;
        comparatorFunction = comparatorFunction || function(a, b){
            if(isNaN(a) || isNaN(b)){
                if(desc==false){
                    return $(a).text() < $(b).text() ? 1 : -1;
                }else{
                    return $(a).text() > $(b).text() ? 1 : -1;
                }
            }else{
                if(desc==false){
                    return parseInt($(a).text(), 10) < parseInt($(b).text(), 10) ? 1 : -1;
                }else{
                    return parseInt($(a).text(), 10) > parseInt($(b).text(), 10) ? 1 : -1;
                }
            }
        };
        getSortable = getSortable || function(){return this;};

        var placements = this.map(function(){

            var sortElement = getSortable.call(this),
                parentNode = sortElement.parentNode,

                // Since the element itself will change position, we have
                // to have some way of storing its original position in
                // the DOM. The easiest way is to have a 'flag' node:
                nextSibling = parentNode.insertBefore(
                    document.createTextNode(''),
                    sortElement.nextSibling
                );

            return function() {

                if (parentNode === this) {
                    throw new Error(
                        "You can't sort elements if any one is a descendant of another."
                    );
                }

                // Insert before flag:
                parentNode.insertBefore(this, nextSibling);
                // Remove flag:
                parentNode.removeChild(nextSibling);

            };

        });

        return sort.call(this, comparatorFunction).each(function(i){
            placements[i].call(getSortable.call(this));
        });

    };

})();

ac.init();
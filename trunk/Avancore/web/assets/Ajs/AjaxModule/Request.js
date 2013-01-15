/**
 * Requires: YAHOO, Ajs_WithOptions, Ajs_Util
 */

Ajs_AjaxModule_Request = function(options) {
    this.id = ++Ajs_AjaxModule_Request.instanceId;
	this.data = {};
	this.rqOptions = {};
    this.callbackArg = {};
	Ajs_WithOptions.call(this, options);
    if (this.autoStart) this.start();
};

Ajs_AjaxModule_Request.prototype = {

		action: '',
		
		data: null,
		
		callbackArg: null,
		
		isPost: false,
		
		showBadResponses: true,
		
		loadingClass: 'loading',
		
		loadingElement: null,
		
		url: null,		
		
		actionParamName: 'action',
		
		ajaxModule: null,
		
		autoStart: true,
        
        id: null,
		
		/**
		 * object {fn, scope}
		 */
		onSuccessCallback: false,
		
		/**
		 * object {fn, scope}
		 */
		onFailCallback: false,
		
		start: function() {
			this._doOnStart();
		},
		
	    _doOnStart: function() {
			if (!this.action) throw "'action' option not set";
			this._fetchData();
	    },

	    _getUrl: function(action, params) {
	        var res = this.url;
	        //if (action) res = res.replace(/\/$*/, '') + '/' + action;
	        var p = null;
	        if (action && this.actionParamName) {
	        	p = {};
	        	p[this.actionParamName] = action;
	        	Ajs_Util.override(p, params);
	        } else {
	        	p = params;
	        }
	        if (p) res += (res.indexOf('?') >= 0? '&' : '?') + Ajs_Util.makeQuery(p, '', true);
	        return res;
	    },
	    
	    _makeRequest: function(url, action, data, isPost, callback) {
	        if (!data) data = {};
            this.callbackArg.requestId = this.id;
	        if (isPost) {
	        	var u = url;
                if (action && this.actionParamName) {
                    var p = {};
                    p[this.actionParamName] = action;
                    Ajs_Util.override(p, data);
                } else {
                    p = data;
                }
	            return YAHOO.util.Connect.asyncRequest(
	            	'POST', 
	                u, 
	                callback, 
	                Ajs_Util.makeQuery(p, '', true));
	        } else {
	            var u = this._getUrl(action, data);
	            return YAHOO.util.Connect.asyncRequest('GET', 
	                u, callback);
	        }	    	
	    },
	    
	    _fetchData: function() {
	        if (this.loadingClass && this.loadingElement  && (this.loadingElement = YAHOO.util.Dom.get(this.loadingElement))) {
	        	YAHOO.util.Dom.addClass(this.loadingElement, this.loadingClass);
	        }
	    	this._makeRequest(
	            	this.url,
	                this.action,
	                this.data,
	                this.isPost,
	                {success: this._fetchOk, failure: this._fetchFail, scope: this}
	        );
	    },

	    _removeLoadingClass: function() {
	    	if (this.loadingClass && this.loadingElement) YAHOO.util.Dom.removeClass(this.loadingElement, this.loadingClass);
	    },
	    
	    _fetchOk: function(oParams) {
	        var json = this.computeJson(oParams.responseText, false);
	        if (!json && this.showBadResponses) json = {isError: true, content: oParams.responseText};
	        this._removeLoadingClass();
	        if (json && (typeof json === 'object')) {
	        	this.ajaxModule.processJson(json, this.callbackArg);
	        	if ((typeof this.onSuccessCallback) == 'object') {
	        		this.onSuccessCallback.fn.call(this.onSuccessCallback.scope || window, json, this.callbackArg, this);
	        	}
	        }
	    },
	    
	    _fetchFail: function(oParams) {
	    	this._removeLoadingClass();
        	if ((typeof this.onFailCallback) == 'object') {
        		this.onFailCallback.fn.call(this.onFailCallback.scope || window, oParams, this);
        	}
	    },
	    

	    computeJson: function (responseText, throwExceptions) {
	        var e = null;
	        this.jsonException = null;
	        try {
	            eval ('e = ' + responseText + ';');
	        } catch(exception) {
	            this.jsonException = exception;
	            if (throwExceptions) throw exception;
	        }
	        return e;
	    }	    
};

Ajs_AjaxModule_Request.instanceId = 0;

Ajs_Util.extend(Ajs_AjaxModule_Request, Ajs_WithOptions);
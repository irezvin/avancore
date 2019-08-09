/**
 * Requires: Ajs_Util, Ajs_WithOptions
 */

if (!window.Ajs_Util) throw "Ajs_AjaxModule requires Ajs_Util namespace; window.Ajs_Util not found";

Ajs_AjaxModule = function (options) {

    this.dialogs = {};
    
    this.dialogOptions = {};
    
    this.defaultDialogOptions = {};

    Ajs_WithOptions.call(this, options);
    
    this.history = [];
    
    this.instanceId = ++Ajs_AjaxModule.instanceId;
    
}

/**
 * Dummy object to pass as a command argument
 */
Ajs_AjaxModule.Forward = {
};

Ajs_AjaxModule.instanceId = 0;


Ajs_AjaxModule.prototype = {

    defaultIsPost: false,

	dialogs: null,
	
	dialogOptions: null,
	
	defaultDialogOptions: null,
	
	view: null,
	
	model: null,
	
	controller: null,
	
	idJsPlaceholder: /{{controller}}/g,
	
	idJsReplacement: '_ajaxModule',
	
	defaultPanelClass: 'ajaxModule',
	
	requestOptions: null,
    
    url: null,
    
    history: null,
	
	/**
	 * Will be added as &c={controllerId} to ajax requests - used by server-side script to tell one controller from another
	 */
	controllerId: '',
	
    getAllForms: function(dialogId) {
        var res = [];
        for (var i in this.dialogs) if (Ajs_Util.hasOwnProperty(this.dialogs, i)) {
            if (!dialogId || i == dialogId) {
                var body = this.dialogs[i].getBodyElement();
                if (body) {
                    var items = body.getElementsByTagName('form');
                    for (var j = 0, l = items.length; j < l; j++) res.push(items[j]);
                }
            }
        }
        return res;
    },
    
    findFormByName: function(name, dialogId) {
        var forms = this.getAllForms(dialogId), res = null;
        for (var i = 0, l = forms.length; i < l && !res; i++) {
            if (forms[i].name == name) res = forms[i];
        }
        return res;
    },
    
	observeForms: function(element, dialogId) {
		var forms = element.getElementsByTagName('form');
		for (var i = 0, c = forms.length; i < c; i++) {
			var form = forms[i];
			YAHOO.util.Event.addListener(forms[i], 'submit', this.handleFormSubmit, form, this);
			forms[i].doSubmit = function(t) {
				return function() { 
					var os = this.getAttribute('onsubmit');
					if (os) eval(os);
					t.handleFormSubmit(null, this);
				};
			} (this);
			var buttons = form.getElementsByTagName('input');
			for (var j = 0, d = buttons.length; j < d; j++) {
				if (buttons[j].type.toLowerCase() == 'submit') {
					YAHOO.util.Event.addListener(buttons[j], 'submit', function() {
						this.form._submitButton = this;
					});
				}
			}
			form._dialogId = dialogId;
			var t = '' + form.target;
			form.removeAttribute('target');
			if (! t.length) t = '' + dialogId;
			form._target  = t;
		}
	},
	
	handleFormSubmit: function(event, form, extraData) {
        
        if (typeof form == 'string') {
            var f = this.findFormByName(form);
            if (!f) throw "No such form: " + form;
            else form  = f;
        }
        
		var action = '' + form.action;
		var pos = action.indexOf('#'), hrefPrefix = '#';
        if (this.controllerId) hrefPrefix +=  this.controllerId + '.';
		if (pos >= 0 && (action.slice(pos, pos + hrefPrefix.length) == hrefPrefix)) {
			var cmdName = action.slice(pos + hrefPrefix.length, action.length);
			if (event) YAHOO.util.Event.preventDefault(event);
            var c = cmdName.split('.'), args = c.slice(1, c.length), queryArgs = {};
            cmdName = c[0];
            if (args[0] && args[0].length && args[0].slice(0, 1) === '&') {
                args[0] = args[0].slice(1, args[0].length);
                queryArgs = Ajs_Util.parseQuery(args.join('.'));
            }
			var data = Ajs_Util.getData(form, form._submitButton || null);
            Ajs_Util.override(queryArgs, data);
            if (extraData && ((typeof extraData) == 'object')) Ajs_Util.override(queryArgs, extraData);
            data = queryArgs;
			var target = '' + form._target;
			var method = '' + form.method.toUpperCase();
			if (form._dialogId && this.dialogs[form._dialogId]) {
				loadingElement = this.dialogs[form._dialogId].getLoadingElement();
			} else {
				loadingElement = null;
			}
			this._doRequest(cmdName, data, {
				loadingElement: loadingElement
			}, method == 'POST', target);
		}
	},
	
	callScripts: function(data, args) {
		return Ajs_Util.callScripts(data, args, this);
	},
	
	stdProcessJson: function(json, callbackArg) {
		if (!callbackArg) callbackArg = {};
		if ((typeof json.content == 'string') && json.content.length) {
			var dialogId = json.dialogId || callbackArg.dialogTarget || 'default',
				dialog = this.getDialog(dialogId);
			dialog.acceptJson(json);
		}
	},
	
	processJson: function(json, callbackArg) {
		if (json instanceof Array) {
			for (var i = 0, c = json.length; i < c; i++) this.processJson(json[i], callbackArg);
		} else {
			if (json.beforeScript) this.callScripts(json.beforeScript, [json]);
			this.stdProcessJson(json, callbackArg);
			if (json.afterScript) this.callScripts(json.afterScript, [json]);
		}
	},
	
	executeCommand: function(command, extraRequestOptions, extraQueryArgs, target) {
		var c = null, args = null, queryArgs = {};
		if (command instanceof Array && command.length) {
			c = command[0];
			args = command.slice(1, command.length);
		} else if (typeof command == 'string') {
			c = command;
			args = [];
		}
		if (args[0] && args[0].length && args[0].slice(0, 1) === '&') {
			args[0] = args[0].slice(1, args[0].length);
			queryArgs = Ajs_Util.parseQuery(args.join('.'));
		}
		if (extraQueryArgs) Ajs_Util.override(queryArgs, extraQueryArgs);
		if (c) {
			var m = 'execute' + Ajs_Util.ucFirst(c);
			if (this[m] && typeof(this[m]) == 'function') {
				if (queryArgs) args = [queryArgs];
				else args = [args];
				args.push(extraRequestOptions);
				
				var res = this[m].apply(this, args);
				/**
				 *  Client-hosted method can return Ajs_AjaxModule.Forward to force the request;
				 *  it can alter args and options before that too. 
				 */ 
				if (res === Ajs_AjaxModule.Forward) {
					if (!queryArgs) {
						queryArgs = {cmd: args};
					}
					this._doRequest(c, queryArgs, extraRequestOptions, target);
				}
				
			} else {
				if (!queryArgs) {
					queryArgs = {cmd: args};
				}
				this._doRequest(c, queryArgs, extraRequestOptions, target);
			}
		}
	},
	
	executeStatus: function(queryArgs) {
		this._doRequest('status', queryArgs, {}, false, false);
	},
	
	_doRequest: function(action, data, extraRequestOptions, isPost, target) {
        if (isPost === undefined) isPost = this.defaultIsPost;
        if (!target) target = null;
        var url = null;
		var options = {
                url: this.url,
				ajaxModule: this,
				action: action,
				data: data,
				autoStart: true,
				isPost: !!isPost,
				controllerId: this.controllerId,
                target: target
			};
		
		if (this.requestOptions) Ajs_Util.override(options, this.requestOptions);
		
		if (!extraRequestOptions || typeof extraRequestOptions !== 'object') extraRequestOptions = {};
		
        if (!extraRequestOptions.callbackArg) extraRequestOptions.callbackArg = {};
        if (target) extraRequestOptions.callbackArg.target = target;
        
        Ajs_Util.override(options, extraRequestOptions);
        
		var request = new Ajs_AjaxModule_Request(options);
        
        this.history.push({
            'id': request.id,
            'action': action,
            'data': data,
            'extraRequestOptions': extraRequestOptions,
            'isPost': isPost,
            'target': target
        });
	},
    
    getHistory: function (dialogId) {
        var res = [];
        if (!dialogId) res = res.concat(this.history);
        else {
            for (var i = 0, l = this.history.length; i < l; i++) {
                if (this.history[i].target == dialogId) res.push(this.history[i]);
            }
        }
        return res;
    },
    
    getHistoryIndex: function(requestOrId) {
        var id = requestOrId instanceof object? requestOrId.id : requestOrId, res = -1;
        for (var i = 0, l = this.history.length; i < l; i++) {
            if (this.history[i].id == id) {
                res = i;
                break;
            }
        }
        return res;
    },
    
    repeatHistoryRequest: function(entry) {
        if (!(entry && typeof entry == 'object')) {
            if (!this.history.length) return false;
            if (!entry) entry = this.history[this.history.length - 1];
                else entry = this.history[entry];
        }
        return this._doRequest(entry.action, entry.data, entry.extraRequestOptions, entry.isPost, entry.target);
    },
	
	/**
	 * @param id
	 * @returns {YAHOO.widget.Panel}
	 */
	getDialog: function(id) {
        if (id === undefined) id = 'default';
		if (!this.dialogs[id]) {
			var defOptions = {constructorFunc: Ajs_AjaxModule_YuiPanelDialog};
			
			if (typeof this.defaultDialogOptions === 'object') 
				Ajs_Util.override(defOptions, this.defaultDialogOptions);
			
			if (typeof this.dialogOptions[id] === 'object')
				Ajs_Util.override(defOptions, this.dialogOptions[id]);
			
			defOptions.id = id;
			defOptions.ajaxModule = this;
			
			this.dialogs[id] = new defOptions.constructorFunc (defOptions);
            
            this.doOnCreateDialog(this.dialogs[id]);
		}
		return this.dialogs[id];
	},
    
    doOnCreateDialog: function(dialog) {
    },
	
	clearDialogs: function() {
		for (var i in this.dialogs) if (Ajs_Util.hasOwnProperty(this.dialogs, i)) this.dialogs[i].destroy();
		this.dialogs = {};
	}
    
}

Ajs_Util.extend(Ajs_AjaxModule, Ajs_WithOptions);
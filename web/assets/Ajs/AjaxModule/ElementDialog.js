/* 
 * Requires: YAHOO, Ajs_Util, Ajs_WithOptions, Ajs_AjaxModule, Ajs_AjaxModule_Dialog
 */


Ajs_AjaxModule_ElementDialog = function(options) {
	
	Ajs_AjaxModule_Dialog.call(this, options);
	
};

Ajs_AjaxModule_ElementDialog.prototype = {
	
	element: null,
	
	header: null,
	
	body: null,
	
	elementClass: 'elementDialog',
	
	headerClass: 'header',
	
	bodyClass: 'body',
    
    elementId: null,
		
	setElement: function(element) {
		this.element = YAHOO.util.Dom.get(element);
		if (!this.element) {
			this.element = document.createElement('div');
			if (typeof element === 'string') this.element.setAttribute('id', element);
		}
		this.prepareElement();
	},
	
	getElement: function() {
		if (!this.element) {
            var id = this.id;
            if (id === 'default') id = this.id + this.ajaxModule.instanceId + '_element';
			this.setElement(id);
		}
		return this.element;
	},
	
	prepareElement: function() {
		
		while (this.element.firstChild) this.element.removeChild(this.element.firstChild);
		
		this.header = document.createElement('div');
		this.element.appendChild(this.header);
		this.body = document.createElement('div');
		this.element.appendChild(this.body);
		
		YAHOO.util.Dom.addClass(this.element, this.elementClass);
		YAHOO.util.Dom.addClass(this.header, this.headerClass);
		YAHOO.util.Dom.addClass(this.body, this.bodyClass);
	},
	
	acceptJson: function(json) {
		this.getElement();
		return Ajs_AjaxModule_Dialog.prototype.acceptJson.call(this, json);
	},
	
	getHeaderElement: function() {
		this.getElement();
		return this.header;
	},
	
	getBodyElement: function() {
		this.getElement();
		return this.body;
	},
	
	getLoadingElement: function() {
		this.getElement();
		return this.element;
	},
	
	destroy: function() {
		if (this.element) {
			//YAHOO.util.Event.purgeElement(this.element);
			delete this.element;
		}
		Ajs_AjaxModule_Dialog.prototype.destroy.call(this);
	}
	
};

Ajs_Util.extend(Ajs_AjaxModule_ElementDialog, Ajs_AjaxModule_Dialog);
/**
 * Requires: YAHOO, Ajs_Util, Ajs_WithOptions, Ajs_AjaxModule, Ajs_AjaxModule_Dialog
 */

Ajs_AjaxModule_YuiPanelDialog = function(options) {
	this.panelOptions = {};
	Ajs_AjaxModule_Dialog.call(this, options);
};

Ajs_AjaxModule_YuiPanelDialog.prototype = {
		
	/**
	 * @type {YAHOO.widget.Panel} 
	 */
	panel: null,

	/**
	 * @type {object}
	 */
	panelOptions: null,
    
    withFooter: null,
	
	acceptJson: function(json) {
		var panel = this.getPanel();
		
		Ajs_AjaxModule_Dialog.prototype.acceptJson.call(this, json);
		
		panel.cfg.setProperty('visible', true);
		panel.changeContentEvent.fire();
	},
	
	getLoadingElement: function() {
		return this.panel? this.panel.innerElement : null;
	},
	
	getHeaderElement: function() {
		return this.panel? this.panel.header : null;
	},
	
	getFooterElement: function() {
		return this.panel? this.panel.footer : null;
	},
	
	getBodyElement: function() {
		return this.panel? this.panel.body : null;
	},
	
	getPanel: function() {
		if (!this.panel) this.panel = this.createPanel();
		return this.panel;
	},
	
	createPanel: function() {
		
		var options = {
				constrainToViewport: true,
				draggable: false,
				effect: [
				         {effect:YAHOO.widget.ContainerEffect.FADE,duration:0.5}
				],
				visible: false
			};
		
			if (typeof this.panelOptions == 'object')
				Ajs_Util.override(options, this.panelOptions);
		
		var body = document.createElement('div');
		body.id =  'ajaxModule_' + this.ajaxModule.instanceId + '_' + this.id;
        var html = "<div class='hd'></div><div class='bd'>&nbsp;</div>";
        if (this.withFooter) html += "<div class='ft'></div>";
		body.innerHTML = html;
		document.body.appendChild(body);
		res = new YAHOO.widget.Panel(body.id, options);
        
        
        // Workaround for YUI to prevent automatic focusing of first modal dialog' element if one of its elements already has focus
        res._focusOnShow = function(type, args, obj) {
            if (args && args[1]) {
                YAHOO.util.Event.stopEvent(args[1]);
            }
            if (document.activeElement && YAHOO.util.Dom.isAncestor(this.element, document.activeElement)) return;
            if (!this.focusFirst(type, args, obj)) {
                if (this.cfg.getProperty("modal")) {
                    this._focusFirstModal();
                }
            }
        }            
        res.unsubscribe("show");
        res.subscribe("show", res._focusOnShow);

		res.render();
		YAHOO.util.Dom.addClass(res.element, this.ajaxModule.defaultPanelClass);
		return res;
	},
	
	destroy: function() {
		if (this.panel) {
			this.panel.destroy();
			delete this.panel;
		}
		Ajs_AjaxModule_Dialog.prototype.destroy.call(this);
	}
};

Ajs_Util.extend(Ajs_AjaxModule_YuiPanelDialog, Ajs_AjaxModule_Dialog);

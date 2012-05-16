/**
 * Requires: YAHOO, Ac_Util, Ac_WithOptions, Ac_AjaxModule, Ac_AjaxModule_Dialog
 */

Ac_AjaxModule_YuiPanelDialog = function(options) {
    this.panelOptions = {};
    Ac_AjaxModule_Dialog.call(this, options);
};

Ac_AjaxModule_YuiPanelDialog.prototype = {
        
    /**
     * @type {YAHOO.widget.Panel} 
     */
    panel: null,

    /**
     * @type {object}
     */
    panelOptions: null,
    
    acceptJson: function(json) {
        var panel = this.getPanel();
        
        Ac_AjaxModule_Dialog.prototype.acceptJson.call(this, json);
        
        panel.cfg.setProperty('visible', true);
        panel.changeContentEvent.fire();
    },
    
    getLoadingElement: function() {
        return this.panel? this.panel.innerElement : null;
    },
    
    getHeaderElement: function() {
        return this.panel? this.panel.header : null;
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
                Ac_Util.override(options, this.panelOptions);
        
        var body = document.createElement('div');
        body.id =  'ajaxModule_' + this.ajaxModule.instanceId + '_' + this.id;
        body.innerHTML = "<div class='hd'></div><div class='bd'>&nbsp;</div><!--div class='ft'></div-->";
        document.body.appendChild(body);
        res = new YAHOO.widget.Panel(body.id, options);
        res.render();
        YAHOO.util.Dom.addClass(res.element, this.ajaxModule.defaultPanelClass);
        return res;
    },
    
    destroy: function() {
        if (this.panel) {
            this.panel.destroy();
            delete this.panel;
        }
        Ac_AjaxModule_Dialog.prototype.destroy.call(this);
    }
};

Ac_Util.extend(Ac_AjaxModule_YuiPanelDialog, Ac_AjaxModule_Dialog);

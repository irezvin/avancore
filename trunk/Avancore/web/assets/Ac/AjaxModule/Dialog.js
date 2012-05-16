/**
 * Requires: Ac_WithOptions, Ac_AjaxModule
 */

Ac_AjaxModule_Dialog = function(options) {
    Ac_WithOptions.call(this, options);
}; 

Ac_AjaxModule_Dialog.prototype = {

    /**
     * Owner of the Dialog
     * @type {Ac_AjaxModule} 
     */
    ajaxModule: null,
    
    /**
     * ID of Dialog
     * @type {string}
     */
    id: null,
        
    acceptJson: function(json) {

        var header = this.getHeaderElement(), body = this.getBodyElement();
        
        if (header) {
            if (json.pageTitle instanceof Array)
                header.innerHTML = '<div class="headerContent">' + json.pageTitle.join(' - ') + '</div>';
            else if (typeof json.pageTitle == 'string') {
                header.innerHTML = '<div class="headerContent">' + json.pageTitle + '</div>';
            }
        }
        if (body) {
            body.innerHTML = json.content.replace(this.ajaxModule.idJsPlaceholder, this.ajaxModule.idJsReplacement);
            this.ajaxModule.observeForms(body, this.id);
        }
        
    },
    
    /**
     * Abstract method that should return an element that will have 'loading' className while ajax request is processed
     * @return {HTMLElement}
     */
    getLoadingElement: function() {
    },
    
    /**
     * Abstract method that should return an element that will have 'loading' className while ajax request is processed  
     * @return {HTMLElement}
     */
    getBodyElement: function() {
    },
    
    getHeaderElement: function() {
    },
    
    destroy: function() {
        delete this.ajaxModule.dialogs[this.id]; 
        this.ajaxModule = null;
    }

};

Ac_Util.extend(Ac_AjaxModule_Dialog, Ac_WithOptions);

AvanManagerRenderer = {
    
    onAttachActionButton: function(action, handler) {
    },
    
    /**
     * @type {AvanControllers.ActionsController.Action} action
     * @type {AvanControllers.ActionsController.ShowImage} handler
     * @type {object} params
     */
    onUpdateActionButton: function(action, handler, params) {
        if (handler.enDis) handler.enDis.update(action, params);
        if (params && params.allowed !== null) {
            if (params.allowed) {
                if (handler.action.image) {
                    handler.element.src = handler.action.image;
                }
            } else {
                if (handler.action.disabledImage) handler.element.src = handler.action.disabledImage;
                    else if (handler.action.image) handler.element.src = handler.action.image;
            }
        }
    }
    
}
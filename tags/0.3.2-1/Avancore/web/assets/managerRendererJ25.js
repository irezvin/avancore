AvanManagerRenderer = {
    
    onAttachActionButton: function(action, handler) {
        var a = action, h = handler;
        if (h.element.tagName.toUpperCase() !== 'DIV') {
            var id = h.element.getAttribute('id');
            var cl = h.element.className;
            var p = h.element.parentNode;
            p.removeChild(h.element);
            h.element = document.createElement('div');
            h.element.setAttribute('id', id);
            h.element.setAttribute('class', cl + ' actionButton');
            if (p.firstChild) p.insertBefore(h.element, p.firstChild)
                else p.appendChild(h.element);
            if (a.image) h.element.style.backgroundImage = "url(" + a.image + ")";
        }
    },
    
    /**
     * @type {AvanControllers.ActionsController.Action} action
     * @type {AvanControllers.ActionsController.ShowImage} handler
     * @type {object} params
     */
    onUpdateActionButton: function(action, handler, params) {
        if (handler.enDis) handler.enDis.update(action, params);
    }
    
}

AvanControllers = {
		
    instances: {},
    
    findManager: function(element) {
        var res = null;
        for(var curr = element; curr && !res; curr = curr.parentNode) {
            if (curr.hasAttribute('data-managerid')) {
                var id = curr.getAttribute('data-managerid');
                res = window.AvanControllers.instances[id];
            }
        }
        return res;
    },

    getElement: function(element) {
		if (element) {
			if (typeof element == 'string') 
				element = document.getElementById(element);
		}
		return element;
	},
    
    findInputs: function(parent, prefix, assoc) {

        var res;
        var l = prefix.length;
        var items = Ajs_Util.getElementsBy(function(elem) {
            var sTag = elem.tagName.toUpperCase();
            return (sTag == "INPUT" || sTag == "TEXTAREA" ||
                    sTag == "SELECT") && (('' + elem.getAttribute('name')).slice(0, l) == prefix);
        }, '*', parent);

        if (assoc) {
            res = {};
            for (var i = 0; i < items.length; i++) {
                res[items[i].getAttribute('name')] = items[i];
            }
        } else {
            res = items;
        }
        return res;
    },
	
    stopEvent: function(event) {
        if (event.stopPropagation) {
        	event.stopPropagation();
        } else {
        	event.cancelBubble = true;
        }
        if (event.preventDefault) {
        	event.preventDefault();
        } else {
        	event.returnValue = false;
        }
    },
    
    _addListener: function() {
		if (window.addEventListener) {
			return function(element, eventType, fn, capture) {
				element.addEventListener(eventType, fn, capture);
			};
		} else if (window.attachEvent) {
			return function(element, eventType, fn, capture) {
				element.attachEvent("on" + eventType, fn);
			};
		} else {
			return function() {};
		}
	} (),
	
	_removeListener: function() {
		if (window.removeEventListener) {
			return function(element, eventType, fn, capture) {
				element.removeEventListener(eventType, fn, capture);
			};
		} else if (window.detachEvent) {
			return function(element, eventType, fn) {
				element.detachEvent("on" + eventType, fn);
			};
		} else {
			return function() {};
		}
	} (),
    
    addListener: function(element, eventType, fn, scope, args) {
		if (!scope) scope = element;
		var extraArgs = Array.prototype.slice.call(arguments, 4);
		var wrappedFn = function(e) {
			var a = [e].concat(extraArgs);			
			return fn.apply(scope, a);
		};
		var res = {'fn': wrappedFn, 'eventType': eventType};
		this._addListener(element, eventType, wrappedFn);
		return res;
	},

	removeListener: function(element, listener) {
		if (listener.fn && listener.eventType)
			this._removeListener(element, listener.eventType, listener.fn);
	},
    
    initFromProps: function(props, target) {
		Ajs_Util.override(target, props);
    },
    
    matchesProps: function(props, target) {
        ok = true;
    	for (var i in props) {
    		if (Ajs_Util.hasOwnProperty(props, i)) {
    			if (target[i] != props[i]) {
    				ok = false;
    				break;
    			}
    		}
    	}
        return ok;
    },
    
    Observable: {
        observe: function (constructor, props, isObserver) {
            if (constructor instanceof Array) {
            	for (var i = constructor.length - 1; i >= 0; i--) {
            		if (constructor[i]) this.observe(constructor[i], props, isObserver);
            	}
            } else {
                if (!this.observers) this.observers = [];
                var res = this.findObserver(constructor, props, isObserver);
                if (!res) {
                    if (!isObserver) {
                        res = new constructor (props);
                    } else {
                        res = constructor;
                    }
                    if (typeof this.configureObserver == 'function') {
                        this.configureObserver(res);
                    }
                    if (res.attach) {
                        res.attach(this);
                    }
                    this.observers.push(res);                    
                }
            }            
            return this;
        },
        
        unobserve: function (constructor, props, isObserver) {
            var observer = null;
            if (this.observers) {
                observer = this.findObserver(constructor, props, isObserver);
                if (observer) {
                    if (observer.detach) observer.detach(this);
                    for (var i = this.observers.length - 1; i >= 0; i--) 
                    	if (this.observers[i] == o) this.observers.splice(i, 1);
                }
            }
            return this;
        },
        
        updateObservers: function(params) {
            if (this.observers) {
            	for (var i = this.observers.length - 1; i >=0; i--) {
            		if (this.observers[i].update) this.observers[i].update(this, params); 
            	}
            }
        },
        
        findObserver: function(constructor, props, isObserver) {
            var res = null;
            for (var i = 0, l = this.observers.length; i < l && res; i++) {
            	var o = this.observers[i];
            	if ((isObserver? o == constructor : o instanceof constructor) && AvanControllers.matchesProps(o, props)) 
            		res = this.obervers[i]; 
            }
            return res;
        },
        
        findAllObservers: function(constructor, props) {
            var res = [];
            for (var i = 0, l = this.observers.length; i < l && res; i++) {
            	var o = this.observers[i];
            	if (o instanceof constructor && AvanControllers.matchesProps(o, props)) 
            		res.push(this.obervers[i]); 
            }
            return res;
        },
        
        unobserveAll: function() {
        	for (var i = this.observers.length - 1; i >= 0; i--) {
        		var o = this.observers[o];
        		if (o.detach) o.detach(this);
        	}
        	this.observers = [];
        }
        
    }
};

// Represents record entry in the admin list
AvanControllers.ListControllerRecord = function (aOptions) {
    if (aOptions) AvanControllers.initFromProps(aOptions, this);
}

AvanControllers.ListControllerRecord.prototype = {
    index: false,
    key: false,
    selected: false,
    lock: false,
    includeActions: false,
    excludeActions: false,
    listController: null,
    
    configureObserver: function(observer) {
        observer.listController = this.listController;
    },
    
    setSelected: function(mode) {
        var before = this.selected;
        this.selected = (mode == 'toggle')? !this.selected : !!mode;
        if (before != this.selected) this.updateObservers({'selected': this.selected});
    }

};

Ajs_Util.augment(AvanControllers.ListControllerRecord.prototype, AvanControllers.Observable);

AvanControllers.Action = function(aOptions) {
    if (aOptions) AvanControllers.initFromProps(aOptions, this);
}

AvanControllers.Action.prototype = {
    
    controller: null,
    index: false,
    id: false, // action id is supplied to the form
    caption: false,
    description: false,
    image: false,
    hoverImage: false,
    disabledImage: false,
    confirmationText: false,
    // Action scope: 'none'|'one'|'some'|'all'|'any'
    scope: 'some',
    needDialog: false,
    managerAction: false,
    managerProcessing: false,
    allowed: false,
    
    updateAllowedStatus: function (records) {
    	records = Ajs_Util.toArray(records);
        
    	var selectedRecords = [];
    	for (var i = 0, l = records.length; i < l; i++) if (records[i].selected) selectedRecords.push(records[i]);
        var oldAllowed = this.allowed;
        
        switch (this.scope) {
            case 'none':
            case 'all':
                this.allowed = true;
                break;
            case 'byRecords':
                this.allowed = this.checkRecords(selectedRecords) && this.checkRecords(selectedRecords, true);
            case 'one':
                this.allowed = selectedRecords.length == 1 && this.checkRecords(selectedRecords) ;
                break;
            case 'some':
                this.allowed = selectedRecords.length > 0 && this.checkRecords(selectedRecords);
                break;
            case 'any':
                this.allowed = this.checkRecords(selectedRecords);
                break;
        }
        
        this.updateObservers({'allowed': this.allowed});
    },
    
    checkRecords: function (records, include) {
        var res;
        if (include && !records.length) {
            res = false;
        } else res = true;
        for (var i = records.length - 1; res && i >= 0; i--) {
        	var r = records[i];
            res = res && include? 
            		r.includeActions && Ajs_Util.indexOf(this.id, Ajs_Util.toArray(r.includeActions)) >= 0 
            	: 
            		!(r.excludeActions && Ajs_Util.indexOf(this.id, Ajs_Util.toArray(r.excludeActions)) >= 0);
        }
        return res;
    },
    
    isAllowed: function () {
        return this.allowed
    },
    
    configureObserver: function(observer) {
        observer.controller = this.controller;
    }
    
}
    
Ajs_Util.augment(AvanControllers.Action.prototype, AvanControllers.Observable);

AvanControllers.ListController = function(aOptions) {
    
    if (aOptions)  {
    
        this.records = [];
        this.presentations = [];
    
        if (aOptions.records instanceof Array) {
            this.addRecords(aOptions.records);
        }
        
        if (aOptions.toggleAllRecordsElement) this.setToggleAllRecordsElement(aOptions.toggleAllRecordsElement);
        
        AvanControllers.initFromProps(aOptions, this);
        
    }
    
}

// List Controller (intended primarily for admin UI)
AvanControllers.ListController.prototype = {
    selectedClass: '',
    deselectedClass: '',
    manager: false,
    
    actionsController: false,
        
    toggleAllRecordsElement: false,
    toggleAllRecordsObserver: false,
    allRecordsAreSelected: false,
    records: null,
    presentations: null,
    nPresentations: 0,
    
    areAllRecordsSelected: function() {return this.allRecordsAreSelected;},
    
    checkIfAllRecordsAreSelected: function() {
        var oldRs = this.allRecordsAreSelected;
        this.allRecordsAreSelected = this.records.length && (this.getSelectedRecords().length == this.records.length);
        if (oldRs != this.allRecordsAreSelected) this.updateObservers({'allSelected' : this.allRecordsAreSelected});
        return this.allRecordsAreSelected;
    },
    
    listRecords: function() {
        var res = new Array();
        for (var i = 0; i < this.records.length; i++) res[res.length] = i;
        return res;
    },
    
    getRecord: function (index) {
        //return {observe: function() {if (!window.foo) window.foo = 1; else window.foo++; document.title = window.foo; return this;}};
        if (!this.records[index]) throw ('No such record: ' + index);
        else return this.records[index];
    },
    
    addRecords: function(prototypes) {
    	for (var i in prototypes) if (Ajs_Util.hasOwnProperty(prototypes, i)) {
            var index = this.records.length;
            var rec = new AvanControllers.ListControllerRecord(prototypes[i]);
            rec.listController = this;
            rec.index = this.records.length;
            rec.observe(this, {}, true);
            this.records[index] = rec;
    	}
    },
    
    changeRecordIndex: function (record, newIndex, isRelative) {
        // TODO
    },
    
    findRecord: function (recordOrKey) {
        if (recordOrKey instanceof AvanControllers.ListControllerRecord) return recordOrKey;
        else {
        	for (var i = this.records.length - 1; i >= 0; i--) {
        		if (this.records[i].key == recordOrKey) return this.records[i];
        	}
        }
        return null;
    },
    
    getSelectedRecords: function () {
    	var res = [];
    	for (var i = 0, l = this.records.length; i < l; i++) {
    		if (!!this.records[i].selected) res.push(this.records[i]);
    	}
    	return res;
    },
    
    // -------------------- miscellaneous public methods ---------------------
    
    setActionsController: function(aActionsController) {
        this.actionsController = aActionsController;
        if (this.actionsController) this.actionsController.update(this.records);
    },
    
    getActionsController: function() {
        return this.actionsController;
    },
    
    // -------------------- methods that are called from user interface --------------
    
    /** @param mode true|false|'toggle' - whether record will be selected|de-selected|toggled (based on current status) */
    selectRecords: function(recordsOrKeys, mode) {
        if (mode == null) mode = 'toggle';
        var recs = [];
        recordsOrKeys = Ajs_Util.toArray(recordsOrKeys);
        for (var i = 0, l = recordsOrKeys.length; i < l; i++) {
        	var r = this.findRecord(recordsOrKeys[i]);
        	if (r) {
        		r.setSelected(mode);
        		recs.push(r);
        	}
        }
    },
    
    /** @param mode true|false|'toggle' - whether record will be selected|de-selected|toggled (based on current status) */
    selectAllRecords: function(mode) {
        if (mode == null) mode = 'toggle';
        this.allRecordsAreSelected = mode == 'toggle'? !this.allRecordsAreSelected : mode;
        this.selectRecords(this.records, this.allRecordsAreSelected);
        this.checkIfAllRecordsAreSelected.apply(this);
    },
    
    update: function(record, params) {
        if (!params || typeof(params.selected != 'undefined')) {
            this.checkIfAllRecordsAreSelected.apply(this);
            if (this.actionsController) {
                this.actionsController.update(this.records);
            }
        }
    }
}

Ajs_Util.augment(AvanControllers.ListController.prototype, AvanControllers.Observable);

// Represents record list pagination
AvanControllers.ListControllerPagination = function(aOptions) {
    if (aOptions) AvanControllers.initFromProps(aOptions, this);
};

AvanControllers.ListControllerPagination.prototype = {
    pageNo: false,
    totalPages: false,
    recordsPerPage: false
}

AvanControllers.ListController.ShowSelected = function (options) {
        if (options && options.element) {
            this.element = AvanControllers.getElement(options.element);
        }
}

AvanControllers.ListController.ShowSelected.prototype = {
    element: false,
    selectedClass: 'selected',
    deselectedClass: 'deselected',

    update: function(record, params) {
        if (!params || typeof(params.selected) != 'undefined' ) {
            if (record.selected) {
                Ajs_Util.addRemoveClassName(this.element, this.selectedClass, this.deselectedClass);
                if (this.element.tagName.toLowerCase() == 'input' && this.element.type.toLowerCase() == 'checkbox')
                    this.element.checked = true;
            } else {
                Ajs_Util.addRemoveClassName(this.element, this.deselectedClass, this.selectedClass);
                if (this.element.tagName.toLowerCase() == 'input' && this.element.type.toLowerCase() == 'checkbox')
                    this.element.checked = false;
            }
        }
    }
}

AvanControllers.ListController.ShowAllSelected = function (options) {
    if (options && options.element) this.element = AvanControllers.getElement(options.element);
}

AvanControllers.ListController.ShowAllSelected.prototype = {
    element: false,
    selectedClass: 'selected',
    deselectedClass: 'deselected',

    update: function(listController, params) {
        if (!params || typeof(params.allSelected) != 'undefined' ) {
            if (listController.areAllRecordsSelected()) {
                Ajs_Util.addRemoveClassName(this.element, this.selectedClass, this.deselectedClass);
                if (this.element.tagName.toLowerCase() == 'input' && this.element.type.toLowerCase() == 'checkbox')
                    this.element.checked = true;
            } else {
                Ajs_Util.addRemoveClassName(this.element, this.deselectedClass, this.selectedClass);
                if (this.element.tagName.toLowerCase() == 'input' && this.element.type.toLowerCase() == 'checkbox')
                    this.element.checked = false;
            }
        }
    }
}
    
AvanControllers.ListController.ToggleAllSelected = function (options) {
    if (options && options.element) this.element = AvanControllers.getElement(options.element);
    if (options && options.controller) this.listController = options.controller;
}


AvanControllers.ListController.ToggleAllSelected.prototype = {
    element: false,
    eventName: 'click',
    toggleMode: 'toggle',
    ignoreSubElements: ['input', 'a', 'select', 'textarea'],
    listController: null,

    eventHandler: false,

    attach: function(listController) {
        this.listController = listController;
        if (this.element) this.eventHandler = AvanControllers.addListener(this.element, this.eventName, this.invoke, this, this.listController);
    },

    detach: function() {
        if (this.eventHandler) AvanControllers.removeListener(this.element, this.eventHandler);
    },

    invoke: function(event) {
        var target = event.target? event.target: event.srcElement;
        if (target == this.element || this.ignoreSubElements.indexOf(target.tagName.toLowerCase()) < 0) {
            this.listController.selectAllRecords(this.toggleMode);
        }
    }
}
    
AvanControllers.ListController.ToggleSelected = function (options) {
    if (options && options.element) this.element = AvanControllers.getElement(options.element);
    if (options && options.controller) this.listController = options.controller;
}

AvanControllers.ListController.ToggleSelected.prototype = {
    element: false,
    eventName: 'click',
    toggleMode: 'toggle',
    ignoreSubElements: ['input', 'a', 'select', 'textarea'],
    listController: null,

    eventHandler: false,

    attach: function(record) {
        if (this.element) this.eventHandler = AvanControllers.addListener(this.element, this.eventName, this.invoke, this, record);
    },

    detach: function(record) {
        if (this.eventHandler) AvanControllers.removeListener(this.element, this.eventHandler);
    },

    invoke: function(event, record) {
        var target = event.target? event.target: event.srcElement;
        if (target == this.element || this.ignoreSubElements.indexOf(target.tagName.toLowerCase()) < 0) {
            record.setSelected(this.toggleMode);
        }
    }
}
    
AvanControllers.ListController.EditRecord = function(options) {
    this.ignoreSubElements = ['input', 'a', 'select', 'textarea'];
    if (options) Ajs_Util.override(this, options);
    if (options && options.controller) this.listController = options.controller;
    if (options && options.element) this.element = AvanControllers.getElement(options.element);
}

AvanControllers.ListController.EditRecord.prototype = {
    actionsController: false,
    element: false,
    eventName: 'click',
    toggleMode: 'toggle',
    ignoreSubElements: null,
    listController: null,
    actionName: 'edit',
    action: false,

    eventHandler: false,

    attach: function(record) {
        if (this.element) this.eventHandler = AvanControllers.addListener(this.element, this.eventName, this.invoke, this, record);
        if (!this.action && this.actionName && this.actionsController) this.action = this.actionsController.getAction(this.actionName);
    },

    detach: function(record) {
        if (this.eventHandler) AvanControllers.removeListener(this.element, this.eventHandler);
    },

    invoke: function(event, record) {
        var target = event.target? event.target: event.srcElement;
        if (target == this.element || this.ignoreSubElements.indexOf(target.tagName.toLowerCase()) < 0) {
            var ac = this.actionsController;
            if (!ac && this.listController) ac = this.listController.getActionsController();
            if (this.listController && ac && (this.action || this.actionName)) {
                var act = this.action;
                if (!act && this.actionName) act = ac.getAction(this.actionName);
                var lr = this.listController.listRecords();
                for (var i = 0, l = lr.length; i < l; i++) {
                    var rec = this.listController.getRecord(i);
                    if (rec != record) rec.setSelected(false); else rec.setSelected(true);
                }
                if (act) ac.invokeAction(act);
            }
            AvanControllers.stopEvent(event);
        }
    }
}

AvanControllers.ActionsController = function(aOptions) {
    AvanControllers.initFromProps(aOptions, this);
    this.actions = {};
    if (aOptions.actions) {
        this.addActions(aOptions.actions);
    }
}

AvanControllers.ActionsController.prototype = {
    actions: null,
    nActions: 0,
    
    enabledClass: 'enabled',
    disabledClass: 'disabled',
    
    managerController: false,
    listController: false,
    
    addActions: function(prototypes) {
    	for (var i in prototypes) if (Ajs_Util.hasOwnProperty(prototypes, i)) {
    		var prot = prototypes[i];
            
            if (prot.id) {
                prot.controller = this;
                var act = new AvanControllers.Action(prot);
                if (!act.id) act.id = 'autoId'.nActions;
                this.actions['action_' + act.id] = act;
                this.nActions++;
            }
    	}
    },
    
    update: function (records) {
    	for (var i in this.actions) if (Ajs_Util.hasOwnProperty(this.actions, i)) {
    		this.actions[i].updateAllowedStatus(records);
    	}
    },
    
    listActions: function () {
    	var res = [];
    	for (var i in this.actions) if (Ajs_Util.hasOwnProperty(this.actions, i)) res.push(i);
    	return res;
    },
    
    getAction: function(idOrAction)  { 
        var res = null;
        if (idOrAction instanceof AvanControllers.Action) res = idOrAction;
        else {
            if (this.actions['action_' + idOrAction]) res = this.actions['action_' + idOrAction];
        }
        if (!res) throw 'No such action: ' + idOrAction;
        return res;
    },
    
    actionClick: function(event, action) {
        this.invokeAction(action);
        AvanControllers.stopEvent(event);
    },
    
    invokeAction: function(action) {
        var a = this.getAction(action);
        if (a && a.isAllowed()) {
            var canInvoke = true;
            if (a.confirmationText && a.confirmationText.length) canInvoke = window.confirm(a.confirmationText);
            if (canInvoke) {
                if (this.manager) this.manager.invokeAction(action);
                else window.alert(action.id + ' invoked!');
            }
        }
    },
    
    callOverlib: function (action) {
        return overlib(action.description, CAPTION, action.caption, BELOW, RIGHT, FGCOLOR, '#eeeeee', BGCOLOR, 'darkblue', DELAY, 1000);
    },
    
    actionMouseOver: function(event, action) {
        if (this.alwaysShowDescription || action.isAllowed()) {
            var res = this.callOverlib(action);
            if (!res) AvanControllers.stopEvent(event);
        }
    },
    
    actionMouseOut: function(event, action) {
        var res = nd();
        if (!res) AvanControllers.stopEvent(event);
    }

}

AvanControllers.ActionsController.ShowImage = function(props) {
    if (props.element) this.element = AvanControllers.getElement(props.element); else this.element = false;
    if (props.controller) this.controller = props.controller;
}

AvanControllers.ActionsController.ShowImage.prototype = {

    action: false,
    enDis: undefined,
    controller: null,

    attach: function(action) {
        this.action = action;
        if (action.controller) this.controller = action.controller;
        AvanManagerRenderer.onAttachActionButton(action, this); 
        this.enDis = new AvanControllers.ActionsController.EnabledDisabled({element: this.element, controller: this.controller});
        this.enDis.attach(action);
        if (this.action) this.update(this.action, {caption: this.action.caption, allowed: this.action.isAllowed()});
    },

    detach: function(action) {
        if (this.enDis) this.enDis.detach(action, params);
    },

    update: function(action, params) {
        /*if (this.enDis) this.enDis.update(action, params);
        if (params && params.allowed !== null) {
            if (params.allowed) {
                if (this.action.image) {
                    this.element.src = this.action.image;
                }
            } else {
                if (this.action.disabledImage) this.element.src = this.action.disabledImage;
                    else if (this.action.image) this.element.src = this.action.image;
            }
        }*/
        
       return AvanManagerRenderer.onUpdateActionButton(action, this, params); 
        
    }
}

AvanControllers.ActionsController.EnabledDisabled = function(props) {
    if (props.controller) this.controller = props.controller;
    if (props.element) this.element = AvanControllers.getElement(props.element); else this.element = false;
}
    
AvanControllers.ActionsController.EnabledDisabled.prototype = {
    
    action: false,
    enDis: true,
    
    attach: function(action) {
        this.action = action;
        if (this.element) {
            this.update(action, {allowed: action.isAllowed()});
        }
    },
    
    update: function(action, params) {
        if (!params) params = {caption: this.action.caption, allowed: this.action.isAllowed()};
        
        if (params.allowed !== null) {
            if (params.allowed) {
                if (this.element) {
                    Ajs_Util.addRemoveClassName(this.element, this.controller.enabledClass, this.controller.disabledClass);
                }
            } else {
                if (this.element) {
                    Ajs_Util.addRemoveClassName(this.element, this.controller.disabledClass, this.controller.enabledClass);
                }
            }
        }
    }
}

AvanControllers.ActionsController.ShowCaption = function(props) {
    if (props.controller) this.controller = props.controller;
    if (props.element) this.element = AvanControllers.getElement(props.element); else this.element = false;
}

AvanControllers.ActionsController.ShowCaption.prototype = {
    
    action: false,
    enDis: undefined,
    
    attach: function(action) {
        this.action = action;
        if (this.element) {
            this.enDis = new AvanControllers.ActionsController.EnabledDisabled({element: this.element, controller: this.controller});
            this.enDis.attach(action);
            var cap;
            if (typeof(action.caption) == 'string') cap = action.caption; else cap = action.id;
            this.update(action, {caption: cap});
        }
    },
    
    detach: function(action) {
        if (this.enDis) this.enDis.detach(action);
    },
    
    update: function(action, params) {
        if (!params) params = {caption: this.action.caption, allowed: this.action.isAllowed()};
        if (this.enDis) this.enDis.update(action, params);
        if (this.element && (typeof(params.caption) == 'string') && params.caption.length) {
            this.element.innerHTML = params.caption;
        }
    }
}
    
AvanControllers.ActionsController.ShowHint = function(props) {
    if (props.controller) this.controller = props.controller;
    if (props.element) this.element = AvanControllers.getElement(props.element); else this.element = false;
}

AvanControllers.ActionsController.ShowHint.prototype = {
    action: false,
    mouseoverHandler: undefined,
    mouseoutHandler: undefined,
    attach: function(action) {
        this.action = action;
        if (this.element) {
            if (typeof(this.action.description == 'string') && this.action.description.length) {
                this.mouseoverHandler = AvanControllers.addListener(this.element, 'mouseover', this.controller.actionMouseOver, this.controller, action);
                this.mouseoutHandler = AvanControllers.addListener(this.element, 'mouseout', this.controller.actionMouseOut, this.controller, action);
            }
        }
    },
    detach: function(action) {
        if (this.mouseoverHandler && this.element) AvanControllers.removeListener(this.element, this.mouseoverHandler);
        if (this.mouseoutHandler && this.element) AvanControllers.removeListener(this.element, this.mouseoutHandler);
    }
}

AvanControllers.ActionsController.Click = function(props) {
    if (props.controller) this.controller = props.controller;
    this.ignoreSubElements = ['input', 'a', 'select', 'textarea'];
    if (props) Ajs_Util.override(this, props);
    if (props.element) this.element = AvanControllers.getElement(props.element); else this.element = false;
}

AvanControllers.ActionsController.Click.prototype = {
    action: false,
    eventName: 'click',
    ignoreSubElements: null,
    element: false,

    clickHandler: false,

    attach: function(action) {
        this.action = action;
        if (this.element) {
            this.clickHandler = AvanControllers.addListener(this.element, this.eventName, this.invoke, this, action);
        }
    },

    detach: function(action) {
        if (this.clickHandler && this.element) AvanControllers.removeListener(this.element, this.clickHandler);
    },

    invoke: function(event, action) {
        var target = event.target? event.target: event.srcElement;
        if ((target == this.element) || this.ignoreSubElements.indexOf(target.tagName.toLowerCase()) < 0)
            this.controller.invokeAction(action);
        AvanControllers.stopEvent(event);
    }

}

AvanControllers.FormController = function(aOptions) {
    this.records = [];
        
    if (aOptions)  {
    
        AvanControllers.initFromProps(aOptions, this);
    
        if (aOptions.records instanceof Array) {
            this.addRecords(aOptions.records);
        }
    }
}

AvanControllers.FormController.prototype = {
    form: null,
    manager: null,
    records: null,
    actionsController: false,
    addRecords: function(prototypes) {
    	for (var i in prototypes) if (Ajs_Util.hasOwnProperty(prototypes, i)) {
            var index = this.records.length;
            var rec = new AvanControllers.ListControllerRecord(prototypes[i]);
            rec.listController = this;
            rec.index = this.records.length;
            rec.observe(this, {}, true);
            this.records[index] = rec;
            rec.setSelected(true);
    	}
        if (this.actionsController) this.actionsController.update(this.records);
    },
    setActionsController: function(aActionsController) {
        this.actionsController = aActionsController;
        if (this.actionsController) this.actionsController.update(this.records);
    },
    update: function(record, params) {
        if (!params || typeof(params.selected != 'undefined')) {
            if (this.actionsController) {
                this.actionsController.update(this.records);
            }
        }
    }
}

AvanControllers.ManagerController = function(aOptions) {
    if (aOptions) AvanControllers.initFromProps(aOptions, this);
}

AvanControllers.ManagerController.prototype = {
    listController: false,
    formController: false,
    actionsController: false,
    paginationController: false,
    parentManager: false,
    
    formElement: false,
    managerActionElement: false,
    managerProcessingElement: false,
    processingParamsPrefix: false,
    containerElementId: false,
    managerParamsPrefix: false,
    
    submitForm: function() {
        if (this.parentManager) this.parentManager.submitFormByChildManager(this); 
        else {
            var formElement = AvanControllers.getElement(this.formElement);
            window.theForm = formElement;
            var inp = document.createElement('input');
            inp.setAttribute('type', 'hidden');
            inp.setAttribute('name', this.managerParamsPrefix + '[_fragment]');
            inp.setAttribute('value', this.containerElementId + '_bookmark');            
            formElement.appendChild(inp);
            var action = formElement.getAttribute('action').split('#')[0];
            formElement.setAttribute('action', action + '#' + this.containerElementId + '_bookmark');
            if (formElement) formElement.submit(); else throw 'No Form Element';
        }
    },
    
    getFormElement: function() {
        if (this.parentManager) return this.parentManager.getFormElement();
            else return AvanControllers.getElement(this.formElement);
    },
    
    /**
     * @param {String} actionName Name of manager action (is passed to PHP backend)
     * @param recordsOrKeys Optional array of records of their keys to select before the form will be submitted (or true to select all records) 
     */
    executeManagerAction: function(actionName, recordsOrKeys) {
    	if (recordsOrKeys) {
    		if (recordsOrKeys === true) {
    			// just make all records 'checked'
    			this.getListController().selectAllRecords(true);
    		} else {
    			this.getListController().selectAllRecords(false);
    			this.getListController().selectRecords(recordsOrKeys, true);
    		}
    	}
    	
        var managerActionElement = AvanControllers.getElement(this.managerActionElement);
        if (managerActionElement) managerActionElement.value = actionName;
        this.submitForm();
    },
    
    /**
     * @param {String} processingName Name of manager processing (is passed to PHP backend)
     * @param recordsOrKeys Optional array of records of their keys to select before the form will be submitted 
     */
    executeProcessing: function(processingName, recordsOrKeys, params, override, managerParams) {
        
        var managerProcessingElement = AvanControllers.getElement(this.managerProcessingElement);
        if (managerProcessingElement) managerProcessingElement.value = processingName;
        
        var queryArgs, bogusItems, items, f, i, item, elem, n;
        
        if (params && typeof params === 'object') {
            f = this.getFormElement();
            if (!f) throw 'No form element';
            queryArgs = Ajs_Util.makeQuery(params, this.processingParamsPrefix, false, true);
            bogusItems = [];
            items = window.AvanControllers.findInputs(f, this.processingParamsPrefix);
            
            // set existing element' values
            for (i = 0; i < items.length; i++) {
                item = items[i], name = item.getAttribute('name') + '';
                if (queryArgs[name] !== undefined) {
                    item.value = queryArgs[name];
                    delete queryArgs[name];
                } else {
                    bogusItems.push(item);
                }
            }
            
            // create missing parameters
            for (n in queryArgs) if (queryArgs.hasOwnProperty(n)) {
                elem = document.createElement('input');
                elem.setAttribute('type', 'hidden');
                elem.setAttribute('name', n);
                elem.setAttribute('value', queryArgs[n]);
                f.appendChild(elem);
            }
            
            if (override) {
                for (i = 0; i < bogusItems.length; i++) {
                    bogusItems[i].parentNode.removeChild(bogusItems[i]);
                }
            }
        }
        
        if (managerParams && typeof managerParams === 'object') {
            f = this.getFormElement();
            if (!f) throw 'No form element';
            queryArgs = Ajs_Util.makeQuery(managerParams, this.managerParamsPrefix, false, true);
            bogusItems = [];
            items = window.AvanControllers.findInputs(f, this.managerParamsPrefix);
            
            // set existing element' values
            for (i = 0; i < items.length; i++) {
                item = items[i], name = item.getAttribute('name') + '';
                if (queryArgs[name] !== undefined) {
                    item.value = queryArgs[name];
                    delete queryArgs[name];
                } else {
                    bogusItems.push(item);
                }
            }
            
            // create missing parameters
            for (n in queryArgs) if (queryArgs.hasOwnProperty(n)) {
                elem = document.createElement('input');
                elem.setAttribute('type', 'hidden');
                elem.setAttribute('name', n);
                elem.setAttribute('value', queryArgs[n]);
                f.appendChild(elem);
            }
            
        }
            
        this.executeManagerAction('processing', recordsOrKeys);
    },
    
    submitFormByChildManager: function(manager) {
        this.submitForm.call(this);
    },
    
    invokeAction: function(action) {
        if (action.isAllowed()) {
            if (action.managerProcessing) this.executeProcessing(action.managerProcessing, null, action.processingParams, false, action.managerParams);
            else {
                if (action.managerAction) this.executeManagerAction(action.managerAction);
            }
        }
    },
    
    setListController: function(aListController) {
        if ((this.listController = aListController)) this.listController.manager = this;
    },
    
    getListController: function() {
    	return this.listController;
    },
    
    setActionsController: function(aActionsController) {
        if ((this.actionsController = aActionsController)) {
            this.actionsController.manager = this;
            this.actionsController.update();
        }
    },
    
    setFormController: function(aFormController) {
        if ((this.formController = aFormController)) this.formController.manager = this;
    },
    
    setPaginationController: function(aPaginationController) {
        if ((this.paginationController = aPaginationController)) this.paginationController.manager = this;
    },
    
    setParentManager: function(aParentManager) {
        this.parentManager = aParentManager;
    }
};


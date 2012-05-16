/**
 * New version of avanManager (as of 2010-09-02) requires core.js from PaxMvc
 */

var AvanControllers = {
		
    instances: [],

    getElement: function(element) {
		if (element) {
			if (typeof element == 'string') 
				element = document.getElementById(element);
		}
		return element;
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
		Ac_Util.override(target, props);
    },
    
    matchesProps: function(props, target) {
        ok = true;
    	for (var i in props) {
    		if (Ac_Util.hasOwnProperty(props, i)) {
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
    this.index = false;
    this.key = false;
    this.selected = false;
    this.lock = false;
    this.includeActions = false;
    this.excludeActions = false;
    
    if (aOptions) AvanControllers.initFromProps(aOptions, this);
    Ac_Util.augment(this, AvanControllers.Observable);
    
    /** @param mode true|false|'toggle' - whether record will be selected|de-selected|toggled (based on current status) */
    this.setSelected = function(mode) {
        var before = this.selected;
        this.selected = (mode == 'toggle')? !this.selected : !!mode;
        if (before != this.selected) this.updateObservers({'selected': this.selected});
    };

};

// Represents admin UI action
AvanControllers.Action = function (aOptions) {
    this.index = false;
    this.id = false; // action id is supplied to the form
    this.caption = false;
    this.description = false;
    this.image = false;
    this.hoverImage = false;
    this.disabledImage = false;
    this.confirmationText = false;
    // Action scope: 'none'|'once'|'some'|'all'|'any'
    this.scope = 'some';
    this.needDialog = false;
    this.managerAction = false;
    this.managerProcessing = false;
    
    var allowed = false;
    
    if (aOptions) AvanControllers.initFromProps(aOptions, this);
    
    this.updateAllowedStatus = function (records) {
    	records = Ac_Util.toArray(records);
        
    	var selectedRecords = [];
    	for (var i = 0, l = records.length; i < l; i++) if (records[i].selected) selectedRecords.push(records[i]);
        var oldAllowed = allowed;
        
        switch (this.scope) {
            case 'none':
            case 'all':
                allowed = true;
                break;
            case 'byRecords':
                allowed = this.checkRecords(selectedRecords) && this.checkRecords(selectedRecords, true);
            case 'one':
                allowed = selectedRecords.length == 1 && this.checkRecords(selectedRecords);
                break;
            case 'some':
                allowed = selectedRecords.length > 0 && this.checkRecords(selectedRecords);
                break;
            case 'any':
                allowed = this.checkRecords(selectedRecords);
                break;
        }
        
        this.updateObservers({'allowed': allowed});
    };
    
    this.checkRecords = function (records, include) {
        var res;
        if (include && !records.length) {
            res = false;
        } else res = true;
        for (var i = records.length - 1; res && i >= 0; i--) {
        	var r = records[i];
            res = res && include? 
            		r.includeActions && Ac_Util.indexOf(this.id, Ac_Util.toArray(r.includeActions)) >= 0 
            	: 
            		!(r.excludeActions && Ac_Util.indexOf(this.id, Ac_Util.toArray(r.excludeActions)) >= 0);
        }
        return res;
    };
    
    this.isAllowed = function () {
        return allowed;
    };
    
    Ac_Util.augment(this, AvanControllers.Observable);
};

// Represents record list pagination
AvanControllers.ListControllerPagination = function(aOptions) {
    this.pageNo = false;
    this.totalPages = false;
    this.recordsPerPage = false;
    
    if (aOptions) AvanControllers.initFromProps(aOptions, this);
};

// List Controller (intended primarily for admin UI)
AvanControllers.ListController = function(aOptions) {
    this.selectedClass = '';
    this.deselectedClass = '';
    this.manager = false;
    
    var actionsController = false;
        
    var toggleAllRecordsElement = false;
    var toggleAllRecordsObserver = false;
    var allRecordsAreSelected = false;
    var records = [];
    var presentations = [];
    var nPresentations = 0;
    
    var _controller = this;
    
    this.ShowSelected = function (options) {
        this.element = false;
        this.selectedClass = 'selected';
        this.deselectedClass = 'deselected';
        if (options && options.element) this.element = AvanControllers.getElement(options.element);
        
        this.update = function(record, params) {
            if (!params || typeof(params.selected) != 'undefined' ) {
                if (record.selected) {
                	Ac_Util.addRemoveClassName(this.element, this.selectedClass, this.deselectedClass);
                    if (this.element.tagName.toLowerCase() == 'input' && this.element.type.toLowerCase() == 'checkbox')
                        this.element.checked = true;
                } else {
                	Ac_Util.addRemoveClassName(this.element, this.deselectedClass, this.selectedClass);
                    if (this.element.tagName.toLowerCase() == 'input' && this.element.type.toLowerCase() == 'checkbox')
                        this.element.checked = false;
                }
            }
        };
    };
    
    this.ShowAllSelected = function (options) {
        this.element = false;
        this.selectedClass = 'selected';
        this.deselectedClass = 'deselected';
        if (options && options.element) this.element = AvanControllers.getElement(options.element);
        
        this.update = function(listController, params) {
            if (!params || typeof(params.allSelected) != 'undefined' ) {
                if (listController.areAllRecordsSelected()) {
                	Ac_Util.addRemoveClassName(this.element, this.selectedClass, this.deselectedClass);
                    if (this.element.tagName.toLowerCase() == 'input' && this.element.type.toLowerCase() == 'checkbox')
                        this.element.checked = true;
                } else {
                	Ac_Util.addRemoveClassName(this.element, this.deselectedClass, this.selectedClass);
                    if (this.element.tagName.toLowerCase() == 'input' && this.element.type.toLowerCase() == 'checkbox')
                        this.element.checked = false;
                }
            }
        };
    };
    
    this.ToggleAllSelected = function (options) {
        this.element = false;
        this.eventName = 'click';
        this.toggleMode = 'toggle';
        this.ignoreSubElements = ['input', 'a', 'select', 'textarea'];
        this.listController = _controller;
        if (options && options.element) this.element = AvanControllers.getElement(options.element);
        
        var eventHandler = false;
        
        this.attach = function(listController) {
            if (this.element) eventHandler = AvanControllers.addListener(this.element, this.eventName, this.invoke, this, listController);
        };
        
        this.detach = function(listController) {
            if (eventHandler) AvanControllers.removeListener(this.element, eventHandler);
        };
        
        this.invoke = function(event, listController) {
        	var target = event.target? event.target: event.srcElement;
            if (target == this.element || this.ignoreSubElements.indexOf(target.tagName.toLowerCase()) < 0) {
                listController.selectAllRecords(this.toggleMode);
            }
        };
    };
    
    this.ToggleSelected = function (options) {
        this.element = false;
        this.eventName = 'click';
        this.toggleMode = 'toggle';
        this.ignoreSubElements = ['input', 'a', 'select', 'textarea'];
        this.listController = _controller;
        if (options && options.element) this.element = AvanControllers.getElement(options.element);
        
        var eventHandler = false;
        
        this.attach = function(record) {
        	if (this.element) eventHandler = AvanControllers.addListener(this.element, this.eventName, this.invoke, this, record);
        };
        
        this.detach = function(record) {
        	if (eventHandler) AvanControllers.removeListener(this.element, eventHandler);
        };
        
        this.invoke = function(event, record) {
        	var target = event.target? event.target: event.srcElement;
            if (target == this.element || this.ignoreSubElements.indexOf(target.tagName.toLowerCase()) < 0) {
                record.setSelected(this.toggleMode);
            }
        };
    };
    
    this.EditRecord = function(options) {
        this.actionsController = false;
        this.element = false;
        this.eventName = 'click';
        this.toggleMode = 'toggle';
        this.ignoreSubElements = ['input', 'a', 'select', 'textarea'];
        this.listController = _controller;
        this.actionName = 'edit';
        this.action = false;
        if (options) Ac_Util.override(this, options);
        if (options && options.element) this.element = AvanControllers.getElement(options.element);
        
        var eventHandler = false;
        
        this.attach = function(record) {
        	if (this.element) eventHandler = AvanControllers.addListener(this.element, this.eventName, this.invoke, this, record);
            if (!this.action && this.actionName && this.actionsController) this.action = this.actionsController.getAction(this.actionName);
        };
        
        this.detach = function(record) {
        	if (eventHandler) AvanControllers.removeListener(this.element, eventHandler);
        };
        
        this.invoke = function(event, record) {
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
        };
    };
    
    this.areAllRecordsSelected = function() { return allRecordsAreSelected; };
    
    var checkIfAllRecordsAreSelected = function() {
        var oldRs = allRecordsAreSelected;
        allRecordsAreSelected = records.length && (this.getSelectedRecords().length == records.length);
        if (oldRs != allRecordsAreSelected) this.updateObservers({'allSelected' : allRecordsAreSelected});
        return allRecordsAreSelected;
    };
    
    this.listRecords = function() {
        var res = new Array();
        for (var i = 0; i < records.length; i++) res[res.length] = i;
        return res;
    };
    
    this.getRecord = function (index) {
        //return {observe: function() {if (!window.foo) window.foo = 1; else window.foo++; document.title = window.foo; return this;}};
        if (!records[index]) throw ('No such record: ' + index);
        else return records[index];
    };
    
    
    this.addRecords = function(prototypes) {
    	for (var i in prototypes) if (Ac_Util.hasOwnProperty(prototypes, i)) {
            var index = records.length;
            var rec = new AvanControllers.ListControllerRecord(prototypes[i]);
            rec.index = records.length;
            rec.observe(this, {}, true);
            records[index] = rec;
    	}
    };
    
    this.changeRecordIndex = function (record, newIndex, isRelative) {
        // TODO
    };
    
    this.findRecord = function (recordOrKey) {
        if (recordOrKey instanceof AvanControllers.ListControllerRecord) return recordOrKey;
        else {
        	for (var i = records.length - 1; i >= 0; i--) {
        		if (records[i].key == recordOrKey) return records[i];
        	}
        }
        return null;
    };
    
    this.getSelectedRecords = function () {
    	var res = [];
    	for (var i = 0, l = records.length; i < l; i++) {
    		if (!!records[i].selected) res.push(records[i]);
    	}
    	return res;
    };
    
    // -------------------- miscellaneous public methods ---------------------
    
    this.setActionsController = function(aActionsController) {
        actionsController = aActionsController;
        if (actionsController) actionsController.update(records);
    };
    
    this.getActionsController = function() {
        return actionsController;
    };
    
    // -------------------- methods that are called from user interface --------------
    
    /** @param mode true|false|'toggle' - whether record will be selected|de-selected|toggled (based on current status) */
    this.selectRecords = function(recordsOrKeys, mode) {
        if (mode == null) mode = 'toggle';
        var recs = [];
        recordsOrKeys = Ac_Util.toArray(recordsOrKeys);
        for (var i = 0, l = recordsOrKeys.length; i < l; i++) {
        	var r = this.findRecord(recordsOrKeys[i]);
        	if (r) {
        		r.setSelected(mode);
        		recs.push(r);
        	}
        }
    };
    
    /** @param mode true|false|'toggle' - whether record will be selected|de-selected|toggled (based on current status) */
    this.selectAllRecords = function(mode) {
        if (mode == null) mode = 'toggle';
        allRecordsAreSelected = mode == 'toggle'? !allRecordsAreSelected : mode;
        this.selectRecords(records, allRecordsAreSelected);
        checkIfAllRecordsAreSelected.apply(this);
    };
    
    this.update = function(record, params) {
        if (!params || typeof(params.selected != 'undefined')) {
            checkIfAllRecordsAreSelected.apply(this);
            if (actionsController) {
                actionsController.update(records);
            }
        }
    };
    
    // ------------------ initialization code --------------
    
    if (aOptions)  {
    
        if (aOptions.records instanceof Array) {
            this.addRecords(aOptions.records);
        }
        
        if (aOptions.toggleAllRecordsElement) this.setToggleAllRecordsElement(aOptions.toggleAllRecordsElement);
        
        AvanControllers.initFromProps(aOptions, this);
        
    }
    
    Ac_Util.augment(this, AvanControllers.Observable);

};

AvanControllers.ActionsController = function(aOptions) {
    var actions = {};
    var nActions = 0;
    
    this.enabledClass = 'enabled';
    this.disabledClass = 'disabled';
    
    this.managerController = false;
    this.listController = false;
    
    this.addActions = function(prototypes) {
    	
    	for (var i in prototypes) if (Ac_Util.hasOwnProperty(prototypes, i)) {
    		var prot = prototypes[i];
            if (prot.id) {
                var act = new AvanControllers.Action(prot);
                if (!act.id) act.id = 'autoId'.nActions;
                actions['action_' + act.id] = act;
                nActions++;
            }
    	}
    };
    
    this.update = function (records) {
    	for (var i in actions) if (Ac_Util.hasOwnProperty(actions, i)) {
    		actions[i].updateAllowedStatus(records);
    	}
    };
    
    this.listActions = function () {
    	var res = [];
    	for (var i in actions) if (Ac_Util.hasOwnProperty(actions, i)) res.push(i);
    	return res;
    };
    
    this.getAction = function(idOrAction)  { 
        var res = null;
        if (idOrAction instanceof AvanControllers.Action) res = idOrAction;
        else {
            if (actions['action_' + idOrAction]) res = actions['action_' + idOrAction];
        }
        if (!res) throw 'No such action: ' + idOrAction;
        return res;
    };
    
    this.actionClick = function(event, action) {
        this.invokeAction(action);
        AvanControllers.stopEvent(event);
    };
    
    this.invokeAction = function(action) {
        var a = this.getAction(action);
        if (a && a.isAllowed()) {
            var canInvoke = true;
            if (a.confirmationText && a.confirmationText.length) canInvoke = window.confirm(a.confirmationText);
            if (canInvoke) {
                if (this.manager) this.manager.invokeAction(action);
                else window.alert(action.id + ' invoked!');
            }
        }
    };
    
    var _me = this;
    
    this.callOverlib = function (action) {
        return overlib(action.description, CAPTION, action.caption, BELOW, RIGHT, FGCOLOR, '#eeeeee', BGCOLOR, 'darkblue', DELAY, 1000);
    };
    
    this.actionMouseOver = function(event, action) {
        if (this.alwaysShowDescription || action.isAllowed()) {
            var res = this.callOverlib(action);
            if (!res) AvanControllers.stopEvent(event);
        }
    };
    
    this.actionMouseOut = function(event, action) {
        var res = nd();
        if (!res) AvanControllers.stopEvent(event);
    };
    
    this.ShowImage = function(props) {
        if (props.element) this.element = AvanControllers.getElement(props.element); else this.element = false;
        this.controller = _me;
        this.action = false;
        
        var enDis;
        
        this.attach = function(action) {
            this.action = action;
            enDis = new this.controller.EnabledDisabled({element: this.element}); enDis.attach(action);
            if (this.action) this.update(this.action, {caption: this.action.caption, allowed: this.action.isAllowed()});
        };
        
        this.detach = function(action) {
            if (enDis) enDis.detach(action, params);
        };
        
        this.update = function(action, params) {
            if (enDis) enDis.update(action, params);
            if (params && params.allowed !== null) {
                if (params.allowed) {
                    if (this.action.image) {
                        this.element.src = this.action.image;
                    }
                } else {
                    if (this.action.disabledImage) this.element.src = this.action.disabledImage;
                        else if (this.action.image) this.element.src = this.action.image;
                }
            }
        };
    };
    
    this.EnabledDisabled = function(props) {
        this.controller = _me;
        this.action = false;
        this.enDis = true;
        if (props.element) this.element = AvanControllers.getElement(props.element); else this.element = false;
        this.attach = function(action) {
            this.action = action;
            if (this.element) {
                this.update(action, {allowed: action.isAllowed()});
            }
        };
        this.update = function(action, params) {
            if (!params) params = {caption: this.action.caption, allowed: this.action.isAllowed()};
            if (params.allowed !== null) {
                if (params.allowed) {
                    if (this.element) {
                    	Ac_Util.addRemoveClassName(this.element, this.controller.enabledClass, this.controller.disabledClass);
                    }
                } else {
                    if (this.element) {
                    	Ac_Util.addRemoveClassName(this.element, this.controller.disabledClass, this.controller.enabledClass);
                    }
                }
            }
        };
    };
    
    this.ShowCaption = function(props) {
        this.controller = _me;
        this.action = false;
        if (props.element) this.element = AvanControllers.getElement(props.element); else this.element = false;
        var enDis;
        this.attach = function(action) {
            this.action = action;
            if (this.element) {
                enDis = new this.controller.EnabledDisabled({element: this.element});
                enDis.attach(action);
                var cap;
                if (typeof(action.caption) == 'string') cap = action.caption; else cap = action.id;
                this.update(action, {caption: cap});
            }
        };
        this.detach = function(action) {if (enDis) enDis.detach(action); };
        this.update = function(action, params) {
            if (!params) params = {caption: this.action.caption, allowed: this.action.isAllowed()};
            if (enDis) enDis.update(action, params);
            if (this.element && (typeof(params.caption) == 'string') && params.caption.length) {
                this.element.innerHTML = params.caption;
            }
        };
    };
    
    this.ShowHint = function(props) {
        var overHandler = false;
        var outHandler = false;
        this.controller = _me;
        this.action = false;
        var mouseoverHandler, mouseoutHandler;
        if (props.element) this.element = AvanControllers.getElement(props.element); else this.element = false;
        this.attach = function(action) {
            this.action = action;
            if (this.element) {
                if (typeof(this.action.description == 'string') && this.action.description.length) {
                	mouseoverHandler = AvanControllers.addListener(this.element, 'mouseover', this.controller.actionMouseOver, this.controller, action);
                	mouseoutHandler = AvanControllers.addListener(this.element, 'mouseout', this.controller.actionMouseOut, this.controller, action);
                    //this.element.observe('mouseover', mouseoverHandler = this.controller.actionMouseOver.bind(_me, action));
                    //this.element.observe('mouseout', mouseoutHandler = this.controller.actionMouseOut.bind(_me, action));
                }
            }
        };
        this.detach = function(action) {
        	if (mouseoverHandler && this.element) AvanControllers.removeListener(this.element, mouseoverHandler);
        	if (mouseoutHandler && this.element) AvanControllers.removeListener(this.element, mouseoutHandler);
            //if (mouseoverHandler && this.element) this.element.unobserve('mouseover', mouseoverHandler);
            //if (mouseoutHandler && this.element) this.element.unobserve('mouseout', mouseoutHandler);
        };
    };
    
    this.Click = function(props) {
        this.controller = _me;
        this.action = false;
        this.eventName = 'click';
        this.ignoreSubElements = ['input', 'a', 'select', 'textarea'];
        
        if (props) Ac_Util.override(this, props);
        if (props.element) this.element = AvanControllers.getElement(props.element); else this.element = false;
        
        var clickHandler = false;
        
        
        this.attach = function(action) {
            this.action = action;
            if (this.element) {
            	clickHandler = AvanControllers.addListener(this.element, this.eventName, this.invoke, this, action);
                //this.element.observe(this.eventName, clickHandler = this.invoke.bindAsEventListener(this, action));
            }
        };
        
        this.detach = function(action) {
        	if (clickHandler && this.element) AvanControllers.removeListener(this.element, clickHandler);
            //if (clickHandler && this.element) this.element.unobserve(this.eventName, clickHandler);
        };
        
        this.invoke = function(event, action) {
        	var target = event.target? event.target: event.srcElement;
            if ((target == this.element) || this.ignoreSubElements.indexOf(target.tagName.toLowerCase()) < 0)
                this.controller.invokeAction(action);
            AvanControllers.stopEvent(event);
        };
        
    };
    
    AvanControllers.initFromProps(aOptions, this);
    
    if (aOptions.actions) {
        this.addActions(aOptions.actions);
    }
};

AvanControllers.FormController = function(aOptions) {
    this.form = null;
    this.manager = null;
};


AvanControllers.ManagerController = function(aOptions) {
    var listController = false;
    var formController = false;
    var actionsController = false;
    var paginationController = false;
    var parentManager = false;
    
    this.formElement = false;
    this.managerActionElement = false;
    this.managerProcessingElement = false;
    
    var submitForm = function() {
        if (this.parentManager) this.parentManager.submitFormByChildManager(this); 
        else {
            var formElement = AvanControllers.getElement(this.formElement);
            if (formElement) formElement.submit(); else throw 'No Form Element';
        }
    };
    
    /**
     * @param {String} actionName Name of manager action (is passed to PHP backend)
     * @param recordsOrKeys Optional array of records of their keys to select before the form will be submitted (or true to select all records) 
     */
    this.executeManagerAction = function(actionName, recordsOrKeys) {
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
        submitForm.call(this);
    };
    
    /**
     * @param {String} actionName Name of manager processing (is passed to PHP backend)
     * @param recordsOrKeys Optional array of records of their keys to select before the form will be submitted 
     */
    this.executeProcessing = function(processingName, recordsOrKeys) {
        var managerProcessingElement = AvanControllers.getElement(this.managerProcessingElement);
        if (managerProcessingElement) managerProcessingElement.value = processingName;
        this.executeManagerAction('processing', recordsOrKeys);
    };
    
    this.submitFormByChildManager = function(manager) {
        submitForm.call(this);
    };
    
    this.invokeAction = function(action) {
        if (action.isAllowed()) {
            if (action.managerProcessing) this.executeProcessing(action.managerProcessing);
            else {
                if (action.managerAction) this.executeManagerAction(action.managerAction);
            }
        }
    };
    
    this.setListController = function(aListController) {
        if (listController = aListController) listController.manager = this;
    };
    
    this.getListController = function() {
    	return listController;
    }
    
    this.setActionsController = function(aActionsController) {
        if (actionsController = aActionsController) {
            actionsController.manager = this;
            actionsController.update();
        }
    };
    
    this.setFormController = function(aFormController) {
        if (formController = aFormController) formController.manager = this;
    };
    
    this.setPaginationController = function(aPaginationController) {
        if (paginationController = aPaginationController) paginationController.manager = this;
    };
    
    this.setParentManager = function(aParentManager) {
        parentManager = aParentManager;
    };
    
    if (aOptions) AvanControllers.initFromProps(aOptions, this);
};


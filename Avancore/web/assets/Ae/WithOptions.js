/**
 * Base class for objects that are initialized by a configuration array.
 * Requires: YAHOO, Pmt_Util
 */
Ae_WithOptions = function(options) {
    if (!this._initVars) this._initVars = new Array();
    if (!this._optionsAreSet) this.setOptions(options);
    this._doInitialize();
}

Ae_WithOptions.prototype = {

    /**
     * Names of variables that will be initialized from options array, 
     * or true to initialize all non-private (starting with '_') members.
     *
     * @type Array|true
     */
    _initVars: true,

    /**
     * Whether setOptions() has already been called
     * @type boolean
     */
    _optionsAreSet: false,

    /**
     * Adds extra members to this._initVars array (if it's not already set to true).
     * This function is intended to be called from constructors of Citex.WithOptions subclasses.
     *
     * @protected
     */
    _addInitVars: function() {
        if (this._initVars !== true) {
            if (!this._initVars instanceof Array) this._initVars = [];
            for (var i = 0; i < this.arguments.length; i++) {
                if (arguments[i] instanceof Array) this._addInitVars[i];
                    else this._initVars.push[i];
            }
        }
    },

    /**
     * Sets members of this object according to _initVars array and also
     * calls setFoo(value) method for each 'foo' key in options.
     *
     * Keys that point to the YAHOO.util.CustomEvent are not assigned; their
     * event handlers are assigned instead.
     *
     * @param object options
     */
    setOptions: function(options) {
        if (typeof options == 'object') {
            if (this._initVars instanceof Array) {
                for (var i = 0; i < this._initVars.length; i++) {
                    if (options[this._initVars[i]] !== undefined) {
                        var varName = this._initVars[i];
                        if (this[varName] === undefined && this['_' + varName] !== undefined)
                                varName = '_' + varName;
                        if (this[varName] instanceof YAHOO.util.CustomEvent) {
                            var handler = options[this._initVars[i]];
                            if (!handler instanceof Array) handler = [handler];
                            this[varName].subscribe.apply(this[varName], handler);
                        } else {
                            this[varName] = options[this._initVars[i]];
                        }
                    }
                }
            }
        }

        for (var opt in options) {
            if (YAHOO.lang.hasOwnProperty(options, opt)) {
                var setter = 'set' + Pmt_Util.ucFirst(opt);
                if (typeof this[setter] == 'function') this[setter] (options[opt]);
                    else {
                        if (this._initVars === true && (opt.slice(0, 1) !== '_') && this[opt] !== undefined) {
                            if (this[opt] instanceof YAHOO.util.CustomEvent) {
                                var handler1 = options[opt];
                                if (!handler1 instanceof Array) handler1 = [handler1];
                                this[opt].subscribe.apply(this[opt], handler1);
                            } else {
                                this[opt] = options[opt];
                            }
                        }
                    }
            }
        }
    },

    /**
     * Template method to perform additional initialization after options are set.
     * @protected
     */
    _doInitialize: function() {
    }

};
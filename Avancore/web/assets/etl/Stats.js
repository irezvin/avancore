Stats = function(options) {
    this.id = options.id;
    this._initTags(options.tags);
    this.table = document.getElementById(this.id);
    this._initExpandCollapse();
    //this._initToggleShowMore();
    
    this._resetDisplay();

    this._initSearch();
}

Stats.prototype = {
    id: null,
    
    tags: null,
    items: null,
    
    table: null,
    
    animation: false,
    
    _searchCall: null,
    
    _searchValue: '',
    
    _searchInput: null,
    
    _initSearch: function() {
        if (!this._searchCall) {
            this._searchCall = new Ajs_Util.DelayedCall(this._doSearch, null, this, [], 500);
        }
        if ((this._searchInput = document.getElementById(this.id + '_search'))) {
            var t = this;
            this._searchInput.value = '';
            Ajs_Util.addListener(this._searchInput, 'keyup', function(event) {
                if (this._searchValue !== this._searchInput.value || event.keyCode == 13) {
                    this._searchValue = this._searchInput.value;
                    if (event.keyCode == 13) this._searchCall.immediate();
                        else this._searchCall.call();
                }
            }, this);
        }
    },
   
    _escapeRegExp: function(string) {
        return string.replace(/[.*+?|()\[\]{}\\$^]/g,'\\$&');
    },
    
    _findKeys: function(hash, pattern, property) {
        if (!(pattern instanceof RegExp)) {
            pattern = new RegExp(this._escapeRegExp(pattern), 'ig');
        }
        var res = [];
        for (var i in hash) if (hash.hasOwnProperty(i)) {
            var val = property !== undefined? hash[i][property] : hash[i];
            if ((val + '').match(pattern)) {
                res.push(i);
            }
        }
        return res;
    },
    
    _resetDisplay: function(dontShow) {
        var tmp = this.animation;

        this.animation = false;

        // hide EVERYTHING
        this.setVisible(false, false, true, true, true);
        // show only top-level tags
        if (!dontShow) this.setVisible(true, false, true, false, false);

        this.animation = tmp;
    },
    
    _doSearch: function() {
        var sv = this._searchValue;
        if (!sv.length) {
            this._resetDisplay();
            $(this._searchInput).removeClass('notFound');
        }
        else {
            var tagKeys = this._findKeys(this.tags, sv, 'name');
            var itemKeys = this._findKeys(this.items, sv, 'message');
            if ((tagKeys.length + itemKeys.length) == 0) {
               $(this._searchInput).addClass('notFound');
            } else {
                $(this._searchInput).removeClass('notFound');
                var tagsToShow = [], itemsToShow = [], i, j;
                for (i = 0; i < tagKeys.length; i++) {
                    tagsToShow = tagsToShow.concat(this._getTagWithAncestors(tagKeys[i]));
                }
                for (i = 0; i < itemKeys.length; i++) {
                    var item = this.items[itemKeys[i]];
                    for (j in item.rows) if (item.rows.hasOwnProperty(j)) {
                        tagsToShow = tagsToShow.concat(this._getTagWithAncestors(j));
                        itemsToShow.push(item.rows[j]);
                    }
                }
                
                this._resetDisplay(true);
                
                var shownTags = {};
                for (i = 0; i < tagsToShow.length; i++) {
                    if (!shownTags[tagsToShow[i]]) {
                        $('tr.tag.direct_' + this.escapeTagName(tagsToShow[i])).show();
                        shownTags[tagsToShow[i]] = true;
                    }
                }
                var sel;
                for (i = 0; i < itemsToShow.length; i++) {
                    
                    $(sel = 'tr.item.ofitem_' + itemsToShow[i]).show();
                }
            }
        }
        
    },
    
    _getTagWithAncestors: function(tagName) {
        var n = tagName.split('/'), res = [];
        for (var i = 1; i <= n.length; i++) {
            res.push(n.slice(0, i + 1).join('/'));
        }
        return res;
    },
    
    _initTags: function(tags) {
        this.tags = {};
        this.items = {};
        for (var i = 0; i < tags.length; i++) {
            var t = tags[i];
            t.tr = document.getElementById(t.name);
            this.tags[t.name] = t;
            for (var j = 0; j < t.items.length; j++) {
                var it = t.items[j], itm;
                if (!this.items[it.messageId]) {
                    itm = Ajs_Util.override({}, it);
                    delete itm.id;
                    itm.rows = {};
                    this.items[it.messageId] = itm;
                } else {
                    itm = this.items[it.messageId];
                }
                itm.rows[t.name] = it.id;
            }
        }
    },
    
    getSubTagNames: function(tagName, direct) {
        var res = [], rx = new RegExp('^' + tagName + '/' + (direct? '[^/]+' : '.+' + '$'));
        for (var i in this.tags) if (this.tags[i].hasOwnProperty(i)) {
            if (rx.match(i)) res.push(i);
        }
        return res;
    },
    
    escapeTagName: function(tagName) {
        return tagName.replace(/\//g, '-').replace(/([^0-9a-zA-Z_-])/g, '\\$1');
    },
    
    getSelector: function(tagName, tags, items, recursive, excludeParentTag) {
        var sels = [];
        if (tagName) {
            var tn = this.escapeTagName(tagName);
            if (tags) {
                if (!excludeParentTag) sels.push('tr.tag.direct_' + tn);
                if (recursive) sels.push('tr.tag.child_' + tn);
                    else sels.push('tr.tag.parent_' + tn);
            }
            if (items) {
                sels.push('tr.item.parent_' + tn);
                if (recursive) sels.push('tr.item.child_' + tn);
            }
        } else {
            var sfx = recursive? '' : '.level1';
            if (tags) sels.push('tr.tag' + sfx);
            if (items) sels.push('tr.item' + sfx);
        }
        return sels.join(',');
    },
    
    setVisible: function(isVisible, tagName, tags, items, recursive, excludeParentTag) {
        if (isVisible === 'toggle') {
            var curr = this.getVisible(tagName, tags, items, recursive, excludeParentTag);
            isVisible = !curr;
        }
        
        var sel = this.getSelector(tagName, tags, items, recursive, excludeParentTag);
        var elements = jQuery(sel);
        if (this.animation) {
            if (isVisible) elements.show(250);
                else elements.hide(250);
        } else {
            elements.css('display', isVisible? '' : 'none');
        }
    },
    
    /**
     * Returns 4-state "bool" value ;-)
     * 
     * true if all elements are visible
     * false if none is visible
     * undefined if there are both visible and invisible elements
     * null if no element is found
     */
    getVisible: function(tagName, tags, items, recursive, excludeParentTag) {
        var s = null, sel = this.getSelector(tagName, tags, items, recursive, excludeParentTag);
        jQuery(sel)
            .each(function() {
                var vis = this.style.display !== 'none';
                if ((typeof (s) === 'boolean') && s !== vis) {
                    s = undefined;
                    return false;
                }
                s = vis;
            });
        return s;
    },
    
    tagNameClick: function(tr, isShift) {
        tagName = tr.getAttribute('stats_tag');
        var vis = this.getVisible(tagName, true, false, isShift, true), includeItems = false, includeTags = true;
        if (vis === null) {
            vis = this.getVisible(tagName, false, true);
            includeTags = false;
            includeItems = true;
            if (vis === null) return; // there are neither 'child' tags nor items
            else vis = !vis;
        } else {
            if (vis) {
                vis = false;
                includeItems = true;
                isShift = true;
            } else {
                vis = true;
            }
        }
        this.setVisible(vis, tagName, includeTags, includeItems, isShift, true);
    },
    
    tagCountClick: function(tr) {
        tagName = tr.getAttribute('stats_tag');
        var vis = this.getVisible(tagName, false, true);
        if (vis === null) return;
        vis = !vis;
        this.setVisible(vis, tagName, false, true);
    },
    
    _initExpandCollapse: function() {
       
       var t = this;
       
       jQuery(this.table).find('tr.tag th.tagName').on('click', function(event){
           t.tagNameClick(this.parentNode, event.shiftKey);
       });
       jQuery(this.table).find('tr.tag td.count').on('click', function(event){
           t.tagCountClick(this.parentNode);
       });
    }
}

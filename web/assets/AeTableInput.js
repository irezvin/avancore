AcTableInput = function(options) {
    if (options) Object.extend(this, options);
    if (this.data) this.data.observe(this, {}, true);
}

AcTableInput.prototype = {

    inputNamePrefix: 'tableInput',
    
    _event: null,

    // Element that contains whole widget
    element: false,
    // User can add tables
    canAddTables: true,
    // User can add rows
    canAddRows: true,
    // User can add columns
    canAddColumns: true,
    // User can edit row headings
    canEditRows: true,
    // User can edit column headings
    canEditColumns: true,
    
    editorSize: false,
    
    tablePrototype: {},
    
    listeners: [],
    
    canDeleteTables: true,
    canDeleteRows: true,
    canDeleteColumns: true,
    
    data: null,
    
    rebuild: function() {
        if (!this.element) throw "Cannot rebuild when 'element' property is not set";
        this.removeListeners(this.element);
        while(this.element.firstChild) this.element.removeChild(this.element.firstChild);
        if (!this.data) return;
        this.element.aeLink = {aeTableInput: this};
        if (this.canAddTables) this.element.appendChild(this.createAddFirstTable());
        for(var t = this.data.firstTable; t; t = t.next) {
            var tableContainer = this.createTableContainer(t);
            this.element.appendChild(tableContainer);
        }
        this.addListener(this.element, 'click', this.elementClick.bindAsEventListener(this));
        this.addListener(this.element, 'keydown', this.elementKeyUp.bindAsEventListener(this));
        //this.addListener(this.element, 'change', this.editorChange.bindAsEventListener(this));
    },
    
    findListeners: function(element) {
        for (var i = 0; i < this.listeners.length; i++) 
            if (this.listeners[i].element == element) return this.listeners[i];
        return null;
    },
    
    removeListeners: function(element) {
        var resListeners = [];
        for (var i = 0; i < this.listeners.length; i++) 
            if (this.listeners[i].element == element) {
                for (var j = 0; j < this.listeners[i].handlers.length; j++) {
                    Event.stopObserving(this.listeners[i].element, this.listeners[i].handlers[j].eventType, this.listeners[i].handlers[j].func);
                }
            } else {
                resListeners[resListeners.length] = this.listeners[i];
            }
        this.listeners = resListeners;
    },
    
    addListener: function(element, eventType, func) {
        Event.observe(element, eventType, func);
        var l = this.findListeners(element);
        var res = {'eventType': eventType, 'func': func};
        if (!l) this.listeners[this.listeners.length] = l = {'element': element, 'handlers': []};
        l.handlers[l.handlers.length] = res;
        return res;
    },
    
    findTableContainer: function(table) {return document.getElementById(this.getTableContainerName(table));},
    
    findTableHeader: function(table) {return document.getElementById(this.getTableContainerName(table, '_header'));},
    
    findTableBody: function(table) {return document.getElementById(this.getTableContainerName(table, '_body'));},
    
    findRowContainer: function(row) {return document.getElementById(this.getRowColContainerName(row, '_data'));},
    
    findHeader: function(rowCol) {return document.getElementById(this.getRowColContainerName(rowCol));},
    
    findCellContainer: function(table, row, column) {return document.getElementById(this.getCellContainerName(table, row, column));},
    
    getTableContainerName: function(table, sfx) {
        if (!sfx) sfx = '';
        return 'aeTi_tbl_' + table.id + sfx;
    },
    
    getRowColContainerName: function(rowCol, sfx) {
        if (!sfx) sfx = '';
        return 'aeTi_' + rowCol.rcType + '_' + rowCol.id + sfx;
    },
    
    getCellContainerName: function(table, row, col) {
        return 'aeTi_cell_' + table.id + '_' + row.id + '_' + col.id;
    },
    
    createAddFirstTable: function() {
        var elem = document.createElement('div');
        elem.className = 'addFirstTable';
        elem.appendChild(this.createAddControl({'action': 'addTable'}));
        return elem;
    },
    
    createTableContainer: function(table) {
        var elem = document.createElement('div');
        elem.className = 'tableContainer';
        elem.id = this.getTableContainerName(table);
        if (this.canAddTables || this.canDeleteTable(table)) {
            var controlsCnt = document.createElement('div');
            elem.className = 'tableContainer';
            if (this.canAddTables) elem.appendChild(this.createAddControl({'table': table, 'action': 'addTable'}));
            if (this.canDeleteTable(table)) elem.appendChild(this.createDeleteControl({'table': table, 'action': 'deleteTable'}));
        }
        var tblElement = document.createElement('table');
        tblElement.className = 'aeData';
        
        var tblHead = document.createElement('thead');
        tblElement.appendChild(tblHead);
        tblHead.appendChild(this.createHeaderRow(table));
        
        var tblBody = document.createElement('tbody');
        tblBody.id = this.getTableContainerName(table, '_body');
        tblElement.appendChild(tblBody);
        for (var r = table.firstRow; r; r = r.next) {
            tblBody.appendChild(this.createDataRow(r));
        }
        elem.appendChild(tblElement);
        return elem;
    },
    
    createHeaderRow: function(table) {
        var tr = document.createElement('tr');
        tr.className = 'headerRow';
        tr.id = this.getTableContainerName(table, '_header');
        tr.appendChild(this.createCornerCell(table));
        for (var c = table.firstColumn; c; c = c.next) {
            tr.appendChild(this.createHeaderCell(c, this.canEditRowCol(c)));
        }
        return tr;
    },
    
    createDataRow: function(row) {
        var tr = document.createElement('tr');
        tr.className = 'dataRow';
        tr.id = this.getRowColContainerName(row, '_data');
        tr.appendChild(this.createHeaderCell(row, this.canEditRowCol(row)));
        for (var c = row.table.firstColumn; c; c = c.next) {
            tr.appendChild(this.createDataCell(row.table, row, c));
        }
        return tr;
    },
    
    createHeaderCell: function(rowCol, isEditable) {
        var val = rowCol.value;
        var isRow = rowCol.rcType == 'row';
        var td = document.createElement('th');
        var linkOptions = {'table': rowCol.table};
        td.className = rowCol.rcType + 'Header';
        td.id = this.getRowColContainerName(rowCol);
        linkOptions[isRow? 'row' : 'column'] = rowCol;
        td.aeLink = linkOptions;
        if (isEditable) {
            var aRow = isRow? rowCol : null;
            var aCol = isRow? null: rowCol;
            td.appendChild(this.createInput(linkOptions, this.getInputName(rowCol.table, aRow, aCol), rowCol.value));
        } else {
            td.appendChild(this.createStaticValue(linkOptions, rowCol.value));
        }
        if (!isRow) td.appendChild(document.createElement('br'));
        if (this.canDeleteRowCol(rowCol)) {
            var linkOptionsDelete = {'table': rowCol.table, 'action': isRow? 'deleteRow' : 'deleteColumn'};
            linkOptionsDelete[isRow? 'row' : 'column'] = rowCol;
            td.appendChild(this.createDeleteControl(linkOptionsDelete));
        }
        if (isRow? this.canAddRows : this.canAddColumns) {
            var linkOptionsAdd = {'table': rowCol.table, 'action': isRow? 'addRow' : 'addColumn'};
            linkOptionsAdd[isRow? 'row' : 'column'] = rowCol;
            td.appendChild(this.createAddControl(linkOptionsAdd));
        }
        return td;
    },
    
    createCornerCell: function(table) {
        var e = document.createElement('th');
        e.className = 'corner';
        
        if (this.canAddColumns) e.appendChild(this.createAddControl({'table': table, 'action': 'addColumn'}));
        if (this.canAddRows) e.appendChild(this.createAddControl({'table': table, 'action': 'addRow'}));
        if (!this.canAddColumns && !this.canAddRows) e.appendChild(document.createTextNode('\u2014'));
        return e;
    },
    
    createDataCell: function (table, row, column) {
        var td = document.createElement('td');
        var value = table.getCell(row, column);
        if (value == null) value = ''; else value = value + '';
        var linkOptions = {'table': table, 'row' : row, 'column' : column};
        td.aeLink = linkOptions;
        td.className = 'cell';
        td.id = this.getCellContainerName(table, row, column);
        td.appendChild (this.createInput(linkOptions, this.getInputName(table, row, column), value));
        return td;
    },
        
    createAddControl: function(linkOptions) {
        var elem = document.createElement('a');
        elem.href = '#';
        elem.className = 'add';
        elem.appendChild(document.createTextNode('[+]'));
        if (linkOptions) elem.aeLink = linkOptions;
        return elem;
    },
    
    createDeleteControl: function(linkOptions) {
        var elem = document.createElement('a');
        elem.href = '#';
        elem.className = 'delete';
        elem.appendChild(document.createTextNode("[\u2212]"));
        if (linkOptions) elem.aeLink = linkOptions;
        return elem;
    },
    
    createInput: function(linkOptions, name, value) {
        var elem = document.createElement('input');
        elem.setAttribute('type', 'text');
	elem.setAttribute('autocomplete', 'off');
        if (this.editorSize) elem.setAttribute('size', this.editorSize);
        if (typeof name != 'undefined') elem.setAttribute('name', name);
        if (typeof value != 'undefined') elem.setAttribute('value', value);
        elem.className = 'textInput';
        if (linkOptions) elem.aeLink = linkOptions;
        //this.addListener(elem, 'change', this.editorChange.bindAsEventListener(this));
        //this.addListener(elem, 'focus', this.editorFocus.bindAsEventListener(this));
        //this.addListener(elem, 'blur', this.editorBlur.bindAsEventListener(this));
        Event.observe(elem, 'change', this.editorChange.bindAsEventListener(this));
        Event.observe(elem, 'focus', this.editorFocus.bindAsEventListener(this));
        Event.observe(elem, 'blur', this.editorBlur.bindAsEventListener(this));
        
        return elem;
    },
    
    createStaticValue: function(linkOptions, value) {
        var elem = document.createElement('span');
        var strValue = value + '';
        elem.className = 'value';
        if (!strValue.length) strValue = '&nbsp;';
        elem.appendChild(document.createTextNode(strValue));
        return elem;        
    },
    
    addTableView: function(table) {return this.moveTableView(table);},
    
    moveTableView: function(table) {
        var beforeContainer = null;
        var tableContainer = null;
        tableContainer = this.findTableContainer(table);
        if (tableContainer) {
            this.element.removeChild(tableContainer);
        } else {
            tableContainer = this.createTableContainer(table);
        }
        if (table.next) beforeContainer = this.findTableContainer(table.next);
        if (beforeContainer) {
            this.element.insertBefore(this.createTableContainer(table), beforeContainer);
        } else {
            this.element.appendChild(this.createTableContainer(table));
        }
    },
    
    deleteTableView: function(table) {
        var tableContainer = this.findTableContainer(table);
        if (tableContainer) this.element.removeChild(tableContainer);
    },
    
    addColumnView: function(column) {return this.moveColumnView(column);},
    
    moveColumnView: function(column) {
        var table = column.table;
        var headerContainer = null, beforeContainer = null, tableHeader = null, nextColumn = null, cellContainer = null, nextCellContainer = null;
        
        // 1. Move header
        headerContainer = this.findHeader(column);
        if (headerContainer) {
            if (headerContainer.parentNode) headerContainer.parentNode.removeChild(headerContainer);
        } else {
            headerContainer = this.createHeaderCell(column, this.canEditColumns);
        }
        if (nextColumn = column.next) beforeContainer = this.findHeader(column.next);
        tableHeader = this.findTableHeader(column.table);
        if (!tableHeader) throw "Cannot find table header for table " + column.table.id;
        if (beforeContainer) tableHeader.insertBefore(headerContainer, beforeContainer);
            else tableHeader.appendChild(headerContainer);
            
        // 2. Move cell
        for (var row = column.table.firstRow; row; row = row.next) {
            cellContainer = this.findCellContainer(column.table, row, column);
            rowContainer = this.findRowContainer(row);
            if (!rowContainer) throw "Cannot find container for row " + row.id;
            if (cellContainer) {
                if (cellContainer.parentNode) cellContainer.parentNode.removeChild(cellContainer);
            } else {
                cellContainer = this.createDataCell(column.table, row, column);
            }
            if (nextColumn) nextCellContainer = this.findCellContainer(column.table, row, nextColumn);
                else nextCellContainer = null;
            if (nextCellContainer) rowContainer.insertBefore(cellContainer, nextCellContainer); 
                else rowContainer.appendChild(cellContainer);
        }
    },
    
    deleteColumnView: function(column) {
        var headerContainer = null, cellContainer = null;
        if (headerContainer = this.findHeader(column)) 
            if (headerContainer.parentNode) 
                headerContainer.parentNode.removeChild(headerContainer);
        for (var row = column.table.firstRow; row; row = row.next) {
            if (cellContainer = this.findCellContainer(column.table, row, column)) {
                if (cellContainer.parentNode) 
                    cellContainer.parentNode.removeChild(cellContainer);
            }
        }
    },
    
    addRowView: function(row) {return this.moveRowView(row);},
    
    moveRowView: function(row) {
        var bodyContainer = this.findTableBody(row.table), rowContainer = null, nextRowContainer = null;
        if (!bodyContainer) throw "Cannot find body container for table " + row.table.id;
        if (rowContainer = this.findRowContainer(row)) {
            if (rowContainer.parentNode) rowContainer.parentNode.removeChild(rowContainer);
        } else {
            rowContainer = this.createDataRow(row);
        }
        if (row.next) nextRowContainer = this.findRowContainer(row.next);
        if (nextRowContainer) bodyContainer.insertBefore(rowContainer, nextRowContainer);
            else bodyContainer.appendChild(rowContainer);
    },
    
    deleteRowView: function(row) {
        var rowContainer = this.findRowContainer(row);
        if (rowContainer && rowContainer.parentNode) rowContainer.parentNode.removeChild(rowContainer);
    },
    
    getInputName: function(table, row, column) {
        var res = this.inputNamePrefix;
        if (table) {
            res = res + '[' + table.id + ']';
            if (row && column) {
                res = res + '[data][' + row.id + ']' + '[' + column.id + ']';
            }
            else if (row) {
                res = res + '[rows][' + row.id + ']';
            } 
            else if (column) {
                res = res + '[columns][' + column.id + ']';
            }
        }
        return res;
    },
    
    canDeleteTable: function(table) {
        return this.canDeleteTables;
    },
    
    canDeleteRowCol: function(rowCol) {
        switch (rowCol.rcType) {
            case 'row': return this.canDeleteRows;
            case 'col': return this.canDeleteColumns;
        }
        return false;
    },
    
    canEditRowCol: function(rowCol) {
        switch (rowCol.rcType) {
            case 'row': return this.canEditRows;
            case 'col': return this.canEditColumns;
        }
        return false;
    },
    
    attach: function(data) {
        this.data = data;
        if (this.element) this.rebuild();
    },
    
    update: function(tblData, details) {
        //console.log(details);
        if (details.newRow) this.addRowView(details.newRow);
        if (details.newColumn) this.addColumnView(details.newColumn);
        if (details.newTable) this.addTableView(details.newTable);
        if (details.deleteRow) this.deleteRowView(details.deleteRow);
        if (details.deleteColumn) this.deleteColumnView(details.deleteColumn);
        if (details.deleteTable) this.deleteTableView(details.deleteTable);
    },
   
    getAeLink: function(element) {
        while (element) {
            if (element.aeLink) {
                var res = {};
                Object.extend(res, element.aeLink);
                return res;
            }
            element = element.parentNode;
        }
        return null;
    },
   
    elementClick: function(event) {
        var aeLink = this.getAeLink(event.target);
        //console.log("Element clicked", event.target.tagName, aeLink);
        if (aeLink) { 
            if (aeLink.table) {
                var table = null;
                if (aeLink.action) {
                    if (aeLink.action == 'addColumn' && (table = aeLink.table)) {
                        var position = aeLink.column? aeLink.column : 'first';
                        table.addColumn({}, position);
                    }
                    else if (aeLink.action == 'deleteColumn' && aeLink.column && (table = aeLink.table)) table.deleteColumn(aeLink.column);
                    else if (aeLink.action == 'addRow' && (table = aeLink.table)) {
                        var position = aeLink.row? aeLink.row : 'first';
                        table.addRow({}, position);
                    }
                    else if (aeLink.action == 'deleteRow' && aeLink.row && (table = aeLink.table)) table.deleteRow(aeLink.row);
                    else if (aeLink.action == 'addTable') {
                        var position = aeLink.table? aeLink.table: 'first';
                        this.data.addTable(this.tablePrototype, position);
                    }
                    else if (aeLink.action == 'deleteTable' && (table = aeLink.table)) {
                        this.data.deleteTable(table);
                    }
                }
            } 
            else if (aeLink.action == 'addTable') {
                this.data.addTable(this.tablePrototype, 'first');
            }
        }
        event.stop();
    },
    
    editorChange: function(event) {
        var aeLink = this.getAeLink(event.target);
        //console.log("Editor changed", aeLink);
        if (event.target.tagName.toLowerCase() == 'input' && aeLink && aeLink.table) {
            if (aeLink.row && aeLink.column) {
                this.data.setCell(aeLink.table.id, aeLink.row.id, aeLink.column.id, event.target.value);
            } 
            else if (aeLink.row) aeLink.row.setValue(event.target.value);
            else if (aeLink.column) aeLink.column.setValue(event.target.value);
        }
        //event.stop();
    },
    
    focusInto: function(element) {
        var tags = element.getElementsByTagName('input');
        if (tags.length) {
            try {
                tags[0].focus();
                if (tags[0].value && tags[0].selectionStart && tags[0].selectionEnd) tags[0].selectionStart = tags[0].selectionEnd = tags[0].value.length;
            } catch (e) {
            }
            return true;
        } else {
            return false;
        }
    },
    
    focusOn: function(aeLink) {
        var element = element;
        if (aeLink.row && aeLink.column) {
            element = this.findCellContainer(aeLink.row.table, aeLink.row, aeLink.column);
        } else if (aeLink.row) {
            element = this.findHeader(aeLink.row);
        } else if (aeLink.column) {
            element = this.findHeader(aeLink.column);
        } else if (aeLink.table && aeLink.table.firstColumn) {
            return this.focusOn({'table': aeLink.table, 'column': aeLink.table.firstColumn});
        }
        if (element) return this.focusInto(element);
        else return false;
    },
    
    focusFirst: function (aeLink, dontMove) {
        var stop = false;
        if (aeLink.column && aeLink.column.table) {
            if (aeLink.row && this.canEditRows) delete aeLink.column;
            else aeLink.column = aeLink.table.firstColumn;
            stop = true;
        }
        if (!dontMove) stop = stop && this.focusOn(aeLink);
        return stop;
    },
    
    focusLast: function (aeLink, dontMove) {
        var stop = false;
        if (aeLink.column && aeLink.row) {
            aeLink.column = aeLink.column.getLast(); stop = true;
        } else if (aeLink.column) {
            aeLink.column = aeLink.column.getLast(); stop = true;
        } else if (aeLink.row) {
            if (aeLink.row.table.firstColumn) aeLink.column = aeLink.row.table.firstColumn.getLast(); stop = true;
        }
        if (!dontMove) stop = stop && this.focusOn(aeLink);
        return stop;
    },
    
    focusTop: function(aeLink, dontMove) {
        if (aeLink.table) {
            var oldTable = aeLink.table;
            if (aeLink.table.prev) { 
                aeLink.table = aeLink.table.prev; stop = true;
            } else {
                aeLink.table = aeLink.table.getLast(); stop = true;
            }
        }
        if (aeLink.table != oldTable) {
            if (aeLink.row) delete aeLink.row;
            if (aeLink.column) delete aeLink.column;
        } else stop = false;
        if (!dontMove) stop = stop && this.focusOn(aeLink);
        return stop;
    },
    
    focusBottom: function(aeLink, dontMove) {
        if (aeLink.table) {
            var oldTable = aeLink.table;
            if (aeLink.table.next) { 
                aeLink.table = aeLink.table.next; stop = true;
            } else {
                aeLink.table = aeLink.table.getFirst(); stop = true;
            }
        }
        if (aeLink.table != oldTable) {
            if (aeLink.row) delete aeLink.row;
            if (aeLink.column) delete aeLink.column;
        } else stop = false;
        if (!dontMove) stop = stop && this.focusOn(aeLink);
        return stop;
    },
    
    focusRight: function(aeLink, dontMove) {
        var stop = false;
        if (aeLink.column) {
            if (aeLink.column.next) { 
                aeLink.column = aeLink.column.next;
                stop = true;
            } else {
               if (this.focusDown(aeLink, true)) stop = true;
               if (this.focusFirst(aeLink, true)) stop = true;
            }
        } else if (aeLink.row) {
            if (aeLink.row.table.firstColumn) {
                aeLink.column = aeLink.row.table.firstColumn;
                stop = true;
            } else {
                stop = this.focusDown(aeLink, true);
            }
        }
        if (!dontMove) stop = stop && this.focusOn(aeLink);
        return stop;
    },
    
    focusLeft: function(aeLink, dontMove) {
        var stop = false;
        if (aeLink.column) {
            //console.log(aeLink.column);
            if (aeLink.column.prev) { 
                aeLink.column = aeLink.column.prev;
                stop = true;
            } else {
                if (this.canEditRows && aeLink.row) {
                    delete aeLink.column;
                    stop = true;
                } else {
                    if (this.focusUp(aeLink, true)) stop = true;
                    if (this.focusLast(aeLink, true)) stop = true;
                }
            }
        } else if (aeLink.row) {
            if (aeLink.row.table.firstColumn) {
                //aeLink.column = aeLink.row.table.firstColumn.getLast();
                if (this.focusUp(aeLink, true)) stop = true;
                if (this.focusLast(aeLink, true)) stop = true;
                stop = true;
            } else {
                stop = this.focusUp(aeLink, true);
            }
        }
        if (!dontMove) stop = stop && this.focusOn(aeLink);
        return stop;
    },
    
    focusDown: function(aeLink, dontMove) {
        var stop = false;
        if (aeLink.row) {
            if (aeLink.row.next) { aeLink.row = aeLink.row.next; stop = true; }
            else if (aeLink.row.table.next) { aeLink.table = aeLink.row.table.next; delete aeLink.row; stop = true; }
            else if (aeLink.row.table) { aeLink.table = aeLink.row.table.getFirst(); delete aeLink.row; stop = true; }
        } else {
            if (aeLink.column) {
                if (aeLink.column.table.firstRow) { aeLink.row = aeLink.table.firstRow; stop = true; }
                else if (aeLink.column.table.next) { aeLink.table = aeLink.column.table.next; delete aeLink.column; stop = true; }
                else if (aeLink.column.table) { aeLink.table = aeLink.column.table.getFirst(); delete aeLink.column; stop = true; }
            }
        }
        if (!dontMove) stop = stop && this.focusOn(aeLink);
        return stop;
    },
    
    focusUp: function(aeLink, dontMove) {
        var stop = false;
        if (aeLink.row) {
            if (aeLink.row.prev) { aeLink.row = aeLink.row.prev; stop = true; }
            else if (this.canEditColumns && aeLink.column) { /*console.log("lets switch to column header");*/ delete aeLink.row; stop = true }
            else if (aeLink.row.table.prev) { aeLink.table = aeLink.row.table.prev; delete aeLink.row; stop = true; }
            else if (aeLink.row.table) { aeLink.table = aeLink.row.table.getLast(); delete aeLink.row; stop = true; }
        } else {
            if (aeLink.column) {
                if (aeLink.column.table.firstRow) { aeLink.row = aeLink.table.firstRow.getLast(); stop = true; }
                else if (aeLink.column.table.prev) { aeLink.table = aeLink.column.table.prev; delete aeLink.column; stop = true; }
                else if (aeLink.column.table) { aeLink.table = aeLink.column.table.getLast(); delete aeLink.column; stop = true; }
            }
        }
        if (!dontMove) stop = stop && this.focusOn(aeLink);
        return stop;
    },
    
    elementKeyUp: function(event) {
        var aeLink = this.getAeLink(event.target);
        //console.log(" key up ", event.keyCode, event.altKey, aeLink);
        var stop = true;
        if (event.altKey) {
            if (event.keyCode == Event.KEY_UP) stop = this.focusUp(aeLink, true);
            else if (event.keyCode == Event.KEY_DOWN) stop = this.focusDown(aeLink, true);
            else if (event.keyCode == Event.KEY_LEFT) stop = this.focusLeft(aeLink, true);
            else if (event.keyCode == Event.KEY_RIGHT) stop = this.focusRight(aeLink, true);
            else if (event.keyCode == Event.KEY_PAGEUP) stop = this.focusTop(aeLink, true);
            else if (event.keyCode == Event.KEY_PAGEDOWN) stop = this.focusBottom(aeLink, true);
            else stop = false;
        } else {
            stop = false;
        }
        if (stop) {
            event.stop();
            this.focusOn(aeLink);
        }
    },
    
    editorFocus: function(event) {
        $(event.target.parentNode).addClassName('focused');
    },
    
    editorBlur: function(event) {
        $(event.target.parentNode).removeClassName('focused');
    }
    
}


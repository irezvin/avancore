if (!window.console) console = {log: function(){}}

AcBiNode = function (options) {
    this.id = this._id;
    AcBiNode.prototype._id++;
    if (options) Object.extend(this, options);
}

AcBiNode.prototype = {
    prev: null,
    next: null,
    _id: 0,
    id: 0,
    
    insertAfter: function(node) {
        if (node == this) throw "Cannot insert node after itself";
        node.remove();
        node.next = this.next;
        node.prev = this;
        if (this.next) this.next.prev = node;
        this.next = node;
        return node;
    },
    
    insertBefore: function(node) {
        if (node == this) throw "Cannot insert node before itself";
        node.remove();
        node.prev = this.prev;
        if (this.prev) this.prev.next = node;
        this.prev = node;
        node.next = this;
        return node;
    },
    
    getFirst: function() {
        var curr = this;
        while (curr.prev) curr = curr.prev;
        return curr;
    },
    
    getLast: function() {
        var curr = this;
        while (curr.next) curr = curr.next;
        return curr;
    },
    
    getAll: function() {
        var before = [];
        var after = [this];
        var curr;
        curr = this.prev; while (curr) { before[before.length] = curr; curr = curr.prev; }
        curr = this.next; while (curr) { after[after.length] = curr; curr = curr.next; }
        before.reverse();
        return before.concat(after);
    },
    
    findById: function(id) {
        if (this.id == id) return this;
        curr = this.prev; while (curr) {if (curr.id == id) return curr; curr = curr.prev; }
        curr = this.next; while (curr) {if (curr.id == id) return curr; curr = curr.next; }
        return null;
    },
    
    findByOffset: function(offset, nonStrict) {
        var val = parseInt(offset);
        if (isNaN(val)) throw "Wrong offset: " + offset;
        if (val == 0) return this;
        var res, curr = this;
        for (var i = 0; curr && (i < Math.abs(val)); i++) {
            res = curr;
            curr = val < 0? curr.prev : curr.next;
        }
        if (!curr && !nonStrict) res = null;
        return res;
    },
    
    getOffset: function(fromEnd) {
        var c = this, res = -1;
        while(c) {
            res++;
            c = fromEnd? c.next : c.prev;
        }
        return res;
    },
    
    remove: function() {
        if (this.prev) this.prev.next = this.next;
        if (this.next) this.next.prev = this.prev;
        return this;
    }
}


AcTableData = function(options) {
    Object.extend(this, AvanControllers.Observable);
    if (options.tables instanceof Array && options.tables.length) {
        var tbl = this.firstTable = new this.Table (this, options.tables[0]);
        for (var i = 1; i < options.tables.length; i++) 
            tbl = tbl.insertAfter(new this.Table (this, options.tables[i]));
    }
}

AcTableData.prototype = {
    firstTable: null,
    log: false,
    Table: function(aeTableData, options) {
        Object.extend(this, new AcBiNode);
        if (options) Object.extend(this, options);
        this.aeTableData = aeTableData;
        var i, j;
        
        if (options && options.rows instanceof Array && options.rows.length) {
            this.firstRow = new this.aeTableData.RowCol (this, options.rows[0]);
            var r = this.firstRow;
            r.rcType = 'row';
            for(i = 1; i < options.rows.length; i++) {
                r = r.insertAfter (new this.aeTableData.RowCol (this, options.rows[i]));
                r.rcType = 'row';
            }
        }
        
        if (options.columns instanceof Array && options.columns.length) {
            var c = this.firstColumn = new this.aeTableData.RowCol (this, options.columns[0]);
            c.rcType = 'col';
            for(i = 1; i < options.columns.length; i++) {
                c = c.insertAfter (new this.aeTableData.RowCol (this, options.columns[i]));
                c.rcType = 'col';
            }
        }
        
        var idata = options.data? options.data : [];
        this.data = {};
        if (this.firstRow && this.firstColumn) {
            var rows = this.firstRow.getAll();
            var columns = this.firstColumn.getAll();
            for (i = 0; i < rows.length; i++) {
                var rowId = 'row_' + rows[i].id;
                this.data[rowId] = {};
                for (j = 0; j < columns.length; j++) {
                    var colId = 'col_' + columns[j].id;
                    if (idata[i] instanceof Array && typeof idata[i][j] != 'undefinded')
                        this.data[rowId][colId] = idata[i][j];
                    else
                        this.data[rowId][colId] = '';
                }
            }
        }
    },
    
    RowCol: function(table, options) {
        Object.extend(this, new AcBiNode);
        this.value = '';
        this.table = table;
        if (typeof options == 'object') {
            if (options instanceof Array) {
                //this.setData(options);
            } else {
                if (options.data instanceof Array) {
                    //this.setData(options.data);
                    //delete options.data;
                }
                Object.extend(this, options);
            }
        } else {
            this.value = options;
        }
    },
    
    getTable: function(id) {
        if (this.firstTable) return this.firstTable.findById(id);
            else return null;
    },
    
    getCell: function(tableId, rowId, colId) {
        var t = this.getTable([tableId]);
        if (!t) throw "No such Table: " + tableId;
        return t.getCell(rowId, colId);
    },
    
    setCell: function(tableId, rowId, colId, value) {
        var tbl = this.getTable(tableId);
        if (!tbl) throw "No such Table: " + tableId;
        return tbl.setCell(rowId, colId, value);
    },
    
    addTable: function(table, position) {
        if (!table) table = {};
        if (!position) position = 'first';
        
        if (typeof table == 'object') { 
            if (table instanceof this.Table) table.remove();
            else table =  new this.Table(this, table);
        } else table = new this.Table(this, {});
        this.moveTable(position, table, true);
        this.updateObservers({'newTable': table});
        return table;
    },
    
    moveTable: function(position, table, noTrigger) {
        if (!position) position = 'first';
        var oldPosition;
        if (!noTrigger) oldPosition = {'prev': table.prev, 'next': table.next};
        switch (true) {
            case position == 'first': 
                if (!this.firstTable) this.firstTable = table;
                else { 
                    table.next = this.firstTable;
                    this.firstTable.prev = table;
                    this.firstTable = table;
                }
                break;
            case position == 'last':
                if (!this.firstTable) this.firstTable = table;
                else this.firstTable.getLast().insertAfter(table);
                break;
            case position instanceof this.Table:
                position.insertAfter(table);
                break;
            default: 
                throw "Wrong position: " + position + ", first/last/Table instance accepted only";
        }
        if (!noTrigger && (oldPosition.prev != table.prev || oldPosition.next != table.next)) {
            this.updateObservers({'moveTable': table, 'oldPosition': oldPosition});
        }
        return table;
    },
    
    deleteTable: function(table) {
        if (typeof table == 'object' && table instanceof this.Table) {
            if (table == this.firstTable) this.firstTable = table.next;
            table.remove();
            this.updateObservers({'deleteTable': table});
        }
    }
    
    /* 
        BTW there are following types of events:
        - created: when table or row/column are created and placed into the list
        - deleted: when table or row/column are deleted and removed from the list
        - moved: when table or row/column changed their order in the list
        - data: when data has changed
    */
    
}

AcTableData.prototype.Table.prototype = {
    id: 0,
    _id: 0,
    rows: [],
    firstRow: null,
    firstColumn: null,
    defaultValue: null,
    
    getRows: function() { return this.firstRow? this.firstRow.getAll() : []; },
    
    getColumns: function() { return this.firstColumn? this.firstColumn.getAll() : []; },
    
    getRow: function(id) { if (typeof id == 'object' && id instanceof this.aeTableData.RowCol) return id; return this.firstRow? this.firstRow.findById(id) : null; },
    
    getColumn: function(id) { if (typeof id == 'object' && id instanceof this.aeTableData.RowCol) return id; return this.firstColumn? this.firstColumn.findById(id) : null; },
    
    getCell: function(row, column) {
        var r, c, rowId, colId;
        rowId = typeof row == 'object' && row instanceof this.aeTableData.RowCol? row.id : row;
        colId = typeof column == 'object' && column instanceof this.aeTableData.RowCol? column.id : column;
        c = 'col_' + colId;
        r = 'row_' + rowId;
        if (typeof(this.data[r]) != 'object') throw ("getCell: No such row: " + rowId);
        
        /*
        if (typeof(this.data[r][c]) == 'undefined') {
            console.log(this.data);
            console.log("getCell: No such column: " + colId);
            foo = bar.baz.quux; // this construction allows to pop "standard" firebug exception and see stack trace...
        }
        if (typeof(this.data[r][c]) == 'undefined') throw ("getCell: No such column: " + colId);
        */
        if (typeof(this.data[r][c]) == 'undefined') this.data[r][c] = "";
        
        return this.data[r][c];
    },
    
    getColData: function(column) {
        var c, columnId, i, row, r;
        if (!this.firstRow) return [];
        if (column = !this.getColumn(column)) throw ("No such column");
        columnId = column.id;
        c = 'col_' + colId;
        var res = [];
        for (row = this.firstRow; row; row = row.next) res[res.length] = this.data['row_' + row.id][c];
        return res;
    },
    
    getRowData: function(row) {
        var c, rowId, i, column, r;
        if (!this.firstColumn) return [];
        if (row = !this.getRow(row)) throw ("No such row");
        rowId = row.id;
        r = 'row_' + rowId;
        var res = [];
        for (column = this.firstColumn; column; column = column.next) res[res.length] = this.data[r]['col_' + column.id];
        return res;
    },
    
    getData: function() {
        var r, c, res;
        if (!this.firstRow || !this.firstColumn) return [];
        res = [];
        for (var row = this.firstRow; row; row = row.next) {
            r = 'row_' + row.id;
            var rowData = [];
            for (var column = this.firstColumn; column; column = column.next) rowData[rowData.length] = this.data[r]['col_' + column.id];
            res[res.length] = rowData;
        }
        return res;
    },
    
    setCell: function(row, col, data, noTrigger) {
        var r, c, rowId, colId;
        rowId = typeof row == 'object' && row instanceof this.data.RowCol? row.id : row;
        colId = typeof column == 'object' && col instanceof this.data.RowCol? col.id : col;
        c = 'col_' + colId;
        r = 'row_' + rowId;
        if (typeof(this.data[r]) != 'object') throw ("setCell: No such row: " + rowId);
        if (typeof(this.data[r][c]) == 'undefined') throw ("setCell: No such column: " + colId);        
        var oldData = this.data[r][c];
        this.data[r][c] = data;
        if (!noTrigger && oldData != data) {
            var row = this.getRow(rowId);
            var col = this.getColumn(colId);
            this.aeTableData.updateObservers({'cells': [{'table': this, 'row': row, 'column': col, 'old': oldData, 'new': data}], 'table' : this});
        }
        return oldData;
    },
    
    setRowData: function(row, data, noTrigger) {
        if (!(row = this.getRow(row))) throw ("No such row");
        if (!this.firstColumn) return [];
        
        var i = 0, r = 'row_' + row.id, res = [], cells = [], value;
        
        for (var column = this.firstColumn; column; column = column.next) {
            var c = 'col_' + column.id;
            var oldValue = res[res.length] = this.data[r][c];
            if (!data instanceof Array) value = data;
                else {
                    value = typeof data[i] == 'undefined'? this.defaultValue : data[i];
                    i++;
                }
            this.data[r][c] = value;
            if (!noTrigger && oldValue != value)
                cells[cells.length] = {'table' : this, 'row' : row, 'column': column, 'old': oldValue, 'new': value};
        }
        if (!noTrigger && cells.length) this.aeTableData.updateObservers({'cells': cells, 'table': this, 'row' : row});
        return res;
    },
    
    setColumnData: function(column, data, noTrigger) {
        if (!(column = this.getColumn(column))) throw ("No such column");
        if (!this.firstRow) return [];
        
        var i = 0, c = 'col_' + column.id, res = [], cells = [], value;
        
        for (var row = this.firstRow; row; row = row.next) {
            var r = 'row_' + row.id;
            var oldValue = res[res.length] = this.data[r][c];
            if (!data instanceof Array) value = data;
                else {
                    value = typeof data[i] == 'undefined'? this.defaultValue : data[i];
                    i++;
                }
            this.data[r][c] = value;
            if (!noTrigger && oldValue != value)
                cells[cells.length] = {'table' : this, 'row' : row, 'column': column, 'old': oldValue, 'new': value};
        }
        if (!noTrigger && cells.length) this.aeTableData.updateObservers({'cells': cells, 'table': this, 'column' : columns});
        return res;
    },
    
    setData: function(data, noTrigger) {
        var r, c, row, column, i, j, res = [], cells = [], value;
        
        if (!this.firstRow || !this.firstColumn) return [];
        
        for (row = this.firstRow; row; row = row.next) {
            r = 'row_' + row.id;
            for (column = this.firstColumn; column; column = column.next) {
                c = 'col_' + column.id;
                var oldValue = res[res.length] = this.data[r][c];
                if (!data instanceof Array) value = data;
                    else {
                        if (typeof data[i] != 'undefined') {
                            if (data[i] instanceof Array) {
                                value = typeof data[i][j] == 'undefined'? this.defaultValue : data[i][j];
                            } else {
                                value = data[i];
                            }
                        } else value = this.defaultValue;
                        j++;
                    }
                this.data[r][c] = value;
                if (!noTrigger && oldValue != value)
                    cells[cells.length] = {'table' : this, 'row' : row, 'column': column, 'old': oldValue, 'new': value};
            }
            i++;
        }
        if (!noTrigger && cells.length) this.aeTableData.updateObservers({'cells': cells, 'table': this});
        return res;
    },
    
    deleteRow: function(row, noTrigger) {
        return this._deleteRc('firstRow', row, noTrigger? false : 'deleteRow', function(aRow) {
            var ri = 'row_' + aRow.id; if (typeof this.data[ri] != 'undefined') delete this.data[ri];
        }.bind(this));
    },
    
    deleteColumn: function(column, noTrigger) {
        return this._deleteRc('firstColumn', column, noTrigger? false : 'deleteColumn', function(aColumn) {
            var ci = 'col_' + aColumn.id, ri; 
            for (var r = this.firstRow; r; r = r.next) {
                ri = 'row_' + r.id;
                if (this.data[ri] instanceof Array && typeof this.data[ri][ci] != 'undefined') delete this.data[ri][ci];
            }
        }.bind(this));
    },
    
    addRow: function(row, position, noTrigger) {
        if (typeof position == 'undefined') position = 'last';
        return this._addRc('firstRow', row, position, noTrigger? false : 'newRow', 'row', function(aRow) {
            var ri = 'row_' + aRow.id, ci;
            this.data[ri] = {};
            for (var c = this.firstColumn; c; c = c.next) {
                ci = 'col_' + c.id;
                this.data[ri][ci] = this.defaultValue;
            }
            if (this.aeTableData.log) console.log(this.data);
            if (typeof row == 'object') {
               if (row instanceof Array) this.setRowData(aRow, row, true);
               if (typeof row.data != 'undefined') this.setRowData(aRow, row.data, true);
            }
        }.bind(this));
    },
    
    addColumn: function(column, position, noTrigger) {
        if (typeof position == 'undefined') position = 'last';
        return this._addRc('firstColumn', column, position, noTrigger? false : 'newColumn', 'col', function(aColumn) {
            var ci = 'col_' + aColumn.id, ri;
            for (var r = this.firstRow; r; r = r.next) {
                ri = 'row_' + r.id;
                if (this.aeTableData.log) console.log(ri, ci);
                this.data[ri][ci] = this.defaultValue;
            }
            if (typeof column == 'object') {
               if (column instanceof Array) this.setColumnData(aColumn, column, true);
               if (typeof column.data != 'undefined') this.setColumnData(aColumn, column.data, true);
            }
        }.bind(this));
    },
    
    moveRow: function (row, direction, noTrigger) {
        return this._moveRc('firstRow', row, direction, noTrigger? false : 'moveRow');
    },
    
    moveColumn: function (column, direction, noTrigger) {
        return this._moveRc('firstColumn', column, direction, noTrigger? false : 'moveColumn');
    },
    
    _getRc: function(firstPropName, rc) {
        var res = null;
        if (typeof rc == 'object' && rc instanceof this.aeTableData.RowCol) res = rc;
        else {
            if (this[firstPropName]) res = this[firstPropName].findById(rc);
        }
        return res;
    },
    
    _deleteRc: function(firstPropName, rc, eventKey, delFun) {
        var rcObj;
        if (!(rcObj = this._getRc(firstPropName, rc))) throw "No such row/column";
        if (rcObj == this[firstPropName]) {
            this[firstPropName] = this[firstPropName].next;
        }
        rcObj.remove();
        delFun(rcObj);
        if (eventKey) {
            var eventParams = {table: this};
            eventParams[eventKey] = rcObj;
            this.aeTableData.updateObservers(eventParams);
        }
        return rcObj;
    },
    
    // position: first, last or RowCol object
    _addRc: function(firstPropName, rc, position, eventKey, rcType, afterRcFun) {
        var rcObj;
        if (typeof rcObj == 'object' && rcObj instanceof this.aeTableData.RowCol) {
            rcObj.remove();
        } else {
            rcObj = new this.aeTableData.RowCol(this, rc);
            rcObj.rcType = rcType;
        }
        switch (true) {
            case position == 'first': 
                if (!this[firstPropName]) this[firstPropName] = rcObj;
                else { 
                    rcObj.next = this[firstPropName];
                    this[firstPropName].prev = rcObj;
                    this[firstPropName] = rcObj;
                }
                break;
            case position == 'last':
                if (!this[firstPropName]) this[firstPropName] = rcObj;
                else {
                    this[firstPropName].getLast().insertAfter(rcObj);
                    console.log(rcObj);
                }
                break;
            case position instanceof this.aeTableData.RowCol:
                position.insertAfter(rcObj);
                break;
            default: 
                throw "Wrong position: " + position + ", first/last/RowCol instance accepted only";
        }
        afterRcFun(rcObj);
        if (eventKey) {
            var eventParams = {table: this};
            eventParams[eventKey] = rcObj;
            this.aeTableData.updateObservers(eventParams);
        }
        if (this.aeTableData.log) console.log(this.data);
        return rcObj;
    },
    
    // direction can be: positive or negative offset; 'first'; 'last'; RowCol object
    _moveRc: function(rc, firstPropName, direction, eventKey) {
        if (!this[firstPropName]) throw "Cannot move row/column within empty list";
        var rcObj = this._getRc(firstPropName, rc);
        if (!rcObj) throw "No such row/column";
        var oldPos = {'prev': rcObj.prev, 'next': rcObj.next};
        var val;
        switch (true) {
            case direction instanceof this.aeTableData.RowCol:
                if (direction != rcObj) direction.insertAfter(rcObj);
                break;
            case direction == 'first':
                rcObj.remove();
                rcObj.next = this[firstPropName];
                this[firstPropName].prev = rcObj;
                this[firstPropName] = rcObj;
                break;
            case direction == 'last':
                this[firstPropName].getLast().insertAfter(rcObj);
                break;
            case !isNaN(val = parseInt(direction)):
                var target = rc.findByOffset(direction);
                if (target != rcObj) {
                    if (target) {
                        target.insertAfter(rcObj);
                    } else {
                        if (val > 0) this[firstPropName].getLast().insertAfter(rcObj);
                        if (val < 0) {
                            rcObj.next = this[firstPropName];
                            this[firstPropName].prev = rcObj;
                            this[firstPropName] = rcObj;
                        }
                    }
                }
                break;
        }
        if (eventKey && (rcObj.next != oldPos.next || rcObj.prev != oldPos.prev)) {
            var eventParams = {'oldPos': oldPos, 'table' : this};
            eventParams[eventKey] = rcObj;
            this.aeTableData.updateObservers(eventParams);
        }
        return rcObj;
    }
    
}

AcTableData.prototype.RowCol.prototype = {
    id: 0,
    _id: 0,
    table: null,
    ordering: 0,
    value: null,
    rcType: null,
    
    setValue: function(value, noTrigger) {
        this.value = value;
        if (!noTrigger) {
            var eventParams = {table: this.table, value: value};
            eventParams[this.rcType + 'Changed'] = this;
            this.table.aeTableData.updateObservers(eventParams);
        }
        return this;
    }
    
}
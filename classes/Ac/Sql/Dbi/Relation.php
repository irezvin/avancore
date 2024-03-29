<?php

class Ac_Sql_Dbi_Relation extends Ac_Sql_Dbi_Object {
    
    /**
     * @var string External table name
     */
    var $table = false;
    
    /**
     * @var array ('thisCol' => 'otherCol', ...)
     */
    var $columns = false;
    
    /**
     * @var Ac_Sql_Dbi_Table
     */
    var $ownTable = false;
    
    /**
     * @var Ac_Sql_Dbi_Table 
     */
    var $_foreignTable = false;
    
    var $_thisRecordUnique = '?';
    
    var $_otherRecordUnique = '?';
    
    var $comment = false;
    
    function __construct(array $prototype = array()) {
        parent::__construct($prototype);
        $this->table = $this->ownTable->_database->prefixizeTable($this->table);
    }
    
    /**
     * @return Ac_Sql_Dbi_Table
     */
    function getForeignTable() {
        if ($this->_foreignTable === false) {
            if (in_array($this->table, $this->ownTable->_database->listTables()))
                $this->_foreignTable = $this->ownTable->_database->getTable($this->table); 
            else {
                throw new Exception("WTF: relation {$this->name} of {$this->ownTable->name} points to non-existent table {$this->table}");
            }
        }
        return $this->_foreignTable;
    }
    
    function isThisRecordUnique() {
        if ($this->_thisRecordUnique === '?') {
            $colNames = array_keys($this->columns);
            $uniqueIndices = $this->ownTable->findIndicesByColumns($colNames, Ac_Sql_Dbi_Table::INDEX_ACCEPT_SMALLER, true);
            $this->_thisRecordUnique = count($uniqueIndices) > 0;
        }
        return $this->_thisRecordUnique;
    }
    
    function isOtherRecordUnique() {
        if ($this->_otherRecordUnique === '?') {
            $colNames = Ac_Util::array_values($this->columns);
            $ft = $this->getForeignTable();
            $uniqueIndices = $ft->findIndicesByColumns($colNames, Ac_Sql_Dbi_Table::INDEX_ACCEPT_SMALLER, true);
            $this->_otherRecordUnique = count($uniqueIndices) > 0;
        }
        return $this->_otherRecordUnique;
    }
    
}


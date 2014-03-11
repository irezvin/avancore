<?php

class Ac_Sql_Dbi_Table extends Ac_Sql_Dbi_Object {

    /**
     * Return indexes that have at least one matching column - intersection
     */
    const INDEX_ACCEPT_ALL = 0;
    
    /**
     * Return indexes that have same columns (at any order) - equality
     */
    const INDEX_ACCEPT_SAME = 1;
    
    /**
     * Return indexes that have all specified columns, but may have more columns - superset
     */
    const INDEX_ACCEPT_LARGER = 2;
    
    /**
     * Return indexes that with every column of the index listed in the speicifed columns, but may have less number of columns - subset
     */
    const INDEX_ACCEPT_SMALLER = 3;
    
    /**
     * @var Ac_Sql_Dbi_Database
     */
    var $_database = false;
    
    var $_columns = false;
    
    var $_indices = false;
    
    var $_relations = false;
    
    var $_extras = array();
    
    function Ac_Sql_Dbi_Table(& $inspector, $name, $database, $extras = array()) {
        parent::Ac_Sql_Dbi_Object($inspector, $name);
        $this->_assignProperties(array('name' => $name));
        $this->_database = $database;
        if (is_array($extras)) $this->_extras = $extras;
    }
    
    function listColumns() {
        if ($this->_columns === false) {
            $allColumns = $this->_inspector->getColumnsForTable($this->_database->deprefixizeTable($this->name), $this->_database->name);
            if (isset($this->_extras['columns']) && is_array($this->_extras['columns'])) Ac_Util::ms($allColumns, $this->_extras['columns']); 
            foreach ($allColumns as $colName => $colData) {
                $col = new Ac_Sql_Dbi_Column($this->_inspector, $colName, $this, $colData);
                $this->_columns[$colName] = $col; 
            }
        }
        return array_keys($this->_columns);
    }
    
    /**
     * @return Ac_Sql_Dbi_Column {
     */
    function getColumn($name) {
        if (!in_array($name, $this->listColumns())) trigger_error('No such column: \''.$name.'\' in table \''.$this->name.'\'', E_USER_ERROR);
        return $this->_columns[$name];
    }
    
    function listIndices() {
        if ($this->_indices === false) {
            $this->_indices = array();
            $allIndices = $this->_inspector->getIndicesForTable($this->_database->deprefixizeTable($this->name), $this->_database->name);
            if (isset($this->_extras['indices']) && is_array($this->_extras['indices'])) {
                Ac_Util::ms($allIndices, $this->_extras['indices']);
            }
            foreach ($allIndices as $name => $data) {
                $obj = new Ac_Sql_Dbi_Index($this->_inspector, $name, $this, $data);
                $this->_indices[$name] = $obj; 
            }
        }
        return array_keys($this->_indices);
    }
    
    /**
     * @return Ac_Sql_Dbi_Index {
     * 
     */
    function getIndex($name) {
        if (!in_array($name, $this->listIndices())) trigger_error('No such index: \''.$name.'\' in table \''.$this->name.'\'', E_USER_ERROR);
        return $this->_indices[$name];
    }
    
    function listRelations() {
        if ($this->_relations === false) {
            $this->_relations = array();
            $allRelations = $this->_inspector->getRelationsForTable($this->_database->deprefixizeTable($this->name), $this->_database->name);
            if (isset($this->_extras['relations']) && is_array($this->_extras['relations'])) Ac_Util::ms($allRelations, $this->_extras['relations']);
            foreach ($allRelations as $name => $data) {
                $obj = new Ac_Sql_Dbi_Relation($this->_inspector, $name, $this, $data);
                $this->_relations[$name] = $obj; 
            }
        }
        return array_keys($this->_relations);
    }
    
    function listRelationsTo($tableName) {
        $res = array();
        foreach ($this->listRelations() as $name) {
            $rel = $this->getRelation($name);
            if ($rel->table == $tableName) $res[] = $name;
        }
        return $res;
    }
    
    /**
     * @return Ac_Sql_Dbi_Relation {
     */
    function getRelation($name) {
        if (!in_array($name, $this->listRelations())) trigger_error('No such relation: \''.$name.'\' in table \''.$this->name.'\'', E_USER_ERROR);
        return $this->_relations[$name];
    }
    
    /**
     * @return array (array(tableName, relationName), array(tableName2, relationName2), ...) 
     */
    function listIncomingRelations() {
        $res = array();
        foreach ($this->_database->listTables() as $tblName) {
            $tbl = $this->_database->getTable($tblName);
            foreach ($tbl->listRelationsTo($this->name) as $relName) $res[] = array($tblName, $relName);
        }
        return $res;
    }
    
    /**
     * @return Ac_Sql_Dbi_Relation
     */
    function getIncomingRelation($idx) {
        if (!is_array($idx) || !isset($idx[0]) || !isset($idx[1])) trigger_error ('Wrong incoming relation name', E_USER_ERROR);
        list ($tableName, $relationName) = $idx;
        $tbl = $this->_database->getTable($tableName);
        $rel = $tbl->getRelation($relationName);
        if ($rel->table != $this->name) trigger_error ('Wrong incoming relation name', E_USER_ERROR);
        return $rel;
    }
    
    function listPkFields() {
        $res = array();
        if (in_array('PRIMARY', $this->listIndices())) {
            $pIdx = $this->getIndex('PRIMARY');
            $res = $pIdx->listColumns();
        }
        return $res;
    }
    
    /**
     * @return array('uniqueIndexName1' => array('fieldName1', 'fieldName2'), 'uniqueIndexName2' => array(...), ...)
     */
    function getUniqueIndexData() {
        $res = array();
        foreach ($this->listIndices() as $name) {
            $idx = $this->getIndex($name);
            if ($idx->unique) $res[$name] = $idx->listColumns();
        }
        return $res;
    }
    
    /**
     * Finds indices of current table that has specified columns in them.
     *
     * @param array $colNames Names of columns that have to be in index (in any order)
     * @param bool $mode How indices are found (see self::INDEX_ACCEPT* constants)
     * @param bool $mustBeUnique Only return names of unique indices
     * 
     * @return array names of indices found
     */
    function findIndicesByColumns($colNames, $mode = self::INDEX_ACCEPT_SAME, $mustBeUnique = false) {
        if (!in_array((int) $mode, array(0, 1, 2, 3))) 
            throw Ac_E_InvalidCall::outOfConst('mode', $mode, 'INDEX_ACCEPT', __CLASS__);
        $mode = (int) $mode;
        
        $res = array();
        foreach ($this->listIndices() as $name) {
            $idx = $this->getIndex($name);
            if ($mustBeUnique && !$idx->unique) continue;
            $colMinusIdx = !array_diff($colNames, $idx->listColumns());
            $idxMinusCol = !array_diff($idx->listColumns(), $colNames);
            
            if ($mode == self::INDEX_ACCEPT_ALL) $ok = $colMinusIdx || $idxMinusCol;
            elseif ($mode == self::INDEX_ACCEPT_SAME) $ok = $colMinusIdx && $idxMinusCol;
            elseif ($mode == self::INDEX_ACCEPT_LARGER) $ok = $colMinusIdx;
            elseif ($mode == self::INDEX_ACCEPT_SMALLER) $ok = $idxMinusCol;
            
            if ($ok) $res[] = $name;
        }
        return $res;
    }
    
    /**
     * Tries to determine whether this table is used as junction between two or more tables.
     * Returns following values: 
     * - FALSE if this table holds any fields except ones that are used in relations
     * - array with references to corresponding incoming and outgoing relations if all fields of the table are used in relations 
     * 
     * @return array|false
     */
    function hasOnlyReferenceFields(array $ignoredOtherColumns = array()) {
        $columns = array();
        $relations = array();
        foreach ($this->listRelations() as $relId) {
            $rel = $this->getRelation($relId);
            $columns = array_merge($columns, array_keys($rel->columns));
            $relations[] = $rel;
        }
        foreach ($this->listIncomingRelations() as $relId) {
            $rel = $this->getIncomingRelation($relId);
            $columns = array_merge($columns, Ac_Util::array_values($rel->columns));
        }
        if (!count($ad = array_diff($this->listColumns(), $columns, $ignoredOtherColumns))) { // all fields are used in relations
             $res = $relations;
        } else {
            $res = false;
        }
        return $res;
    }
    
    /**
     * Determines whether all fields of this table are used in two <em>outgoing</em> relations
     * Returns array with relations if this table is bi-junction and false if this table fails to comply such requirement.  
     * @see Ac_Sql_Dbi_Table::hasOnlyReferenceFields
     * @return array|false  
     */
    function isBiJunctionTable(array $ignoredOtherColumns = array()) {
        $res = $this->hasOnlyReferenceFields($ignoredOtherColumns);
        if (is_array($res) && count($res) == 2) {
            $allAreOutgoing = true;
            $relFields = array();
            foreach (array_keys($res) as $r) {
                $rel = $res[$r];
                if ($rel->table == $this->name) { // this is an incoming relation...
                    $allAreOutgoing = false; 
                    break;
                } else {
                    Ac_Util::ms($relFields, array_keys($rel->columns)); 
                }
            }
            // Fields that participate in the relations must form primary key...
            if ($allAreOutgoing && array_diff($this->listPkFields(), $relFields)) $res = false; 
        } else {
            $res = false;
        }
        return $res;
    }
    
}


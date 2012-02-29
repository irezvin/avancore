<?php

Ae_Dispatcher::loadClass('Ae_Sql_Dbi_Inspector');

class Ae_Sql_Dbi_Inspector_MsSql extends Ae_Sql_Dbi_Inspector {
	
    /**
     * @var Ae_Sql_Db
     */
    var $_db = false;
    
    var $defaultDatabaseName = false;

    function Ae_Sql_Dbi_Inspector_MsSql(& $sqlDb, $defaultDatabaseName = false) {
    	if (is_a($sqlDb, 'Ae_Legacy_Database')) {
    		Ae_Dispatcher::loadClass('Ae_Sql_Db_Ae');
    		$this->_db = new Ae_Sql_Db_Ae($sqlDb);
    	} elseif (!is_a($sqlDb, 'Ae_Sql_Db')) trigger_error("\$sqlDb must be an instance of Ae_Sql_Db");
    	else $this->_db = & $sqlDb;
        $this->defaultDatabaseName = $defaultDatabaseName;
    }
    
    // --------------------- DATABASE-RELATED METHODS ----------------------
    
    /**
     * @return array(tableName1, tableName2...)
     */
    function listTablesForDatabase($databaseName = false) {
        $dbName = $this->_getDbName($databaseName);
        $res = $this->_db->fetchColumn('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '.$this->_db->q($dbName));
        return $res;
    }
    
    // ----------------------- TABLE-RELATED METHODS ------------------------
    
    /**
     * @return array(columnName => array('type'=>, 'width'=>, 'decimals'=>,'default'=>, 'autoInc'=>true/false, 'comment'=>columnComment/false, 'enumValues'=>array(enumValues)/false), 
     *  columnName2 => array()...)
     */
    function getColumnsForTable($tableName, $databaseName = false) {
        $q = 
            "
				SELECT 
					c.name AS [name]
					, t.name AS [type]
					, CASE c.precision WHEN 0 THEN c.max_length ELSE c.precision END AS width
					, c.scale AS decimals
					, c.is_identity AS [autoInc] 
					, c.is_nullable AS nullable
				FROM 
					sys.columns c
					INNER JOIN sys.types t ON t.system_type_id = c.system_type_id
				WHERE 
				c.[object_id] = OBJECT_ID(".$this->_db->q($databaseName.".".$tableName).")            
			";
        $cols = $this->_db->fetchArray($q, "name");
        foreach (array_keys($cols) as $i) {
        	$col = & $cols[$i];
        	$col['autoInc'] = (bool) (int) $col['autoInc'];
        	$col['nullable'] = (bool) (int) $col['nullable'];
        }
        return $cols;
    }
        
    /**
     * @return array(indexName=>array('primary'=>true/false,'unique'=>true/false,'columns'=>array('fieldName1','fieldName2'))
     */
    function getIndicesForTable($tableName, $databaseName = false) {
        $dbName = $this->_getQDbName($databaseName);
        $res = array();
        foreach ($this->_db->fetchArray("
	        	SELECT 
					(CASE i.is_primary_key WHEN 1 THEN 'PRIMARY' ELSE i.name END) AS [Key_name]
					, ic.key_ordinal AS [Seq_in_index]
					, c.name AS [Column_name]
					,(CASE i.is_unique WHEN 1 THEN 0 ELSE 1 END) AS [Non_unique]
				FROM sys.indexes i
					INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND ic.index_id = i.index_id
					INNER JOIN sys.columns c ON (c.object_id = ic.object_id) AND (c.column_id = ic.column_id)
				WHERE i.[object_id] = OBJECT_ID(".$this->_db->q($databaseName.".".$tableName).")
			") as $idxData) {
            $idxName = $idxData['Key_name'];
            if (!isset($res[$idxName])) $res[$idxName] = array(
                'primary' => ($idxName === 'PRIMARY'), 
                'unique' => (!$idxData['Non_unique']), 
                'columns' => array(),
            );
            $res[$idxName]['columns'][$idxData['Seq_in_index']] = $idxData['Column_name'];
        }
        return $res;
    }
    
    /**
     * @return array(relationName=>array('table'=>externalTableName,'columns'=>array('thisField1' => 'thatField1', 'thisField2' => 'thatField2'...)))
     */
    function getRelationsForTable($tableName, $databaseName = false) {
        $qdbn = $this->_db->q($this->_getDbName($databaseName));
        $res = array();
        $q = "SELECT fk.name AS con, fc.name AS col, tt.name AS tbl, tc.name AS refcol
				FROM sys.foreign_keys fk
					INNER JOIN sys.foreign_key_columns fkc ON fkc.constraint_object_id = fk.[object_id]
					INNER JOIN sys.columns fc ON fkc.parent_object_id = fc.[object_id] AND fkc.parent_column_id = fc.column_id 
					INNER JOIN sys.columns tc ON fkc.referenced_object_id = tc.[object_id] AND fkc.referenced_column_id = tc.column_id 
					INNER JOIN sys.tables tt ON tt.[object_id] = fkc.referenced_object_id
				WHERE
					fk.parent_object_id = OBJECT_ID(".$this->_db->q($databaseName.".".$tableName).")
		";
        foreach ($this->_db->fetchArray($q) as $relData) {
            $cName = $relData['con'];
            if (!isset($res[$cName])) $res[$cName] = array(
                'table' => $relData['tbl'],
                'columns' => array()
            );
            $res[$cName]['columns'][$relData['col']] = $relData['refcol'];
        }
        return $res;
    }
        
    // --------------------------- PRIVATE METHODS --------------------------
    
    function _getDbName($dbName) {
        if ($dbName) $res = $dbName;
        else {
            if (!$this->defaultDatabaseName) trigger_error ('Default database name not specified', E_USER_ERROR);
            $res = $this->defaultDatabaseName;
        }
        return $res;
    }
    
    function _getQDbName($dbName) {
        $res = $this->_db->n($this->_getDbName($dbName));
        return $res;
    }
    
    function _getQTableName($dbName, $tableName) {
        $res = $this->_db->n(array($dbName, $tableName));
        return $res;
    }
    
}
<?php

class Ac_Sql_Dbi_Inspector_Legacy extends Ac_Sql_Dbi_Inspector {

	/**
	 * @var ormDatabase
	 */
	var $_db = false;
	
	var $prefix = '';
	
	var $_myPx = '';
	
	function Ac_Sql_Dbi_Inspector_Legacy($prefix = '') {
		$this->_db = ormDatabase::getInstance();
		$this->prefix = $prefix;
		if (strlen($this->prefix)) {
			//$this->_myPx = preg_replace('/^'.preg_quote($this->prefix).'/', '#__', $this->_db->globalPrefix);
			$this->_myPx = $this->prefix;
		}
	}
	
    /**
     * @return array(tableName1, tableName2...)
     */
    function listTablesForDatabase($databaseName = false) {
    	$res = array();
    	foreach (array_keys($this->_db->tables) as $k) {
    		if (strlen($this->_db->tables[$k]->sqlName)) $name = $this->_db->tables[$k]->sqlName;
    			else $name = $this->_myPx.$k;  
    		$res[] = $name;
    	}
    	return $res;
    }
    
    // ----------------------- TABLE-RELATED METHODS ------------------------
    
    /**
     * @return ormDatabaseTable
     */
    function _findTableBySqlName($sqlName) {
    	$res = null;
    	
    	$n = $sqlName;
    	if (($l = strlen($this->_myPx)) && !strncmp($sqlName, $this->_myPx, $l)) $n = substr($sqlName, $l);
    	elseif (($l = strlen($this->prefix)) && !strncmp($sqlName, $this->prefix, $l)) $n = substr($sqlName, $l);
    	
    	if (isset($this->_db->tables[$n])) {
    		$res = $this->_db->tables[$n];
    		$sqlName = $n;
    	} else {
    		foreach (array_keys($this->_db->tables) as $k) if ($this->_db->tables[$k]->sqlName == $sqlName) { $res = $this->_db->tables[$k]; $sqlName = $k; break; } 
    	}
    	return $res;
    }
    
    /**
     * @return array(columnName => array('type'=>, 'width'=>, 'decimals'=>,'default'=>, 'autoInc'=>true/false, 'comment'=>columnComment/false, 'enumValues'=>array(enumValues)/false), 
     *  columnName2 => array()...)
     */
    function getColumnsForTable($tableName, $databaseName = false) {
    	
    	$t = $this->_findTableBySqlName($tableName);
    	
    	$typeMap = array(
    		ORM_DT_INTEGER => 'int',
    		ORM_DT_FLOAT => 'float',
    		//ORM_DT_SET => 'set',
    		ORM_DT_ENUM => 'enum',
    		ORM_DT_DATE => 'date',
    		ORM_DT_DATETIME => 'datetime',
    		ORM_DT_TIME => 'time',
    		ORM_DT_TIMESTAMP => 'timestamp',
    		ORM_DT_STRING => 'varchar',
    	);
    	
    	foreach (array_keys($t->fields) as $fieldName) {
    		$field = $t->fields[$fieldName];
    		
    		$fieldInfo = array();
    		$fieldInfo['type'] = $typeMap[$field->dataType];
    		if ($field->maxLength) $fieldInfo['width'] = $field->maxLength;
    		if ($field->decimals) $fieldInfo['decimals'] = $field->decimals;
    		if ($field->isAutoInc) $fieldInfo['autoInc'] = true;
    		if (strlen($c = $field->sureGet('interface.short'))) $fieldInfo['comment'] = $c;
    		if ($field->enumValue) {
    			$fieldInfo['enumValues'] = $field->enumValue;
    		}
    		if ($field->isText) $fieldInfo['type'] = 'text';
    		//if (!$field->isNotNull) $fieldInfo['nullable'] = true;
    		if (!is_null($field->defaultValue)) $fieldInfo['default'] = $field->defaultValue;
    		$res[$fieldName] = $fieldInfo;
    	}
    	
        return $res;
    }
    
    /**
     * @return array(indexName=>array('primary'=>true/false,'unique'=>true/false,'columns'=>array('fieldName1','fieldName2'))
     */
    function getIndicesForTable($tableName, $databaseName = false) {
        $res = array();
        $t = $this->_findTableBySqlName($tableName);
        
        foreach (array_keys($t->indices) as $name) {
        	$idx = $t->indices[$name];
        	$idxInfo = array('primary' => $idx->isPrimary, 'unique' => $idx->isPrimary || $idx->isUnique);
        	if ($idx->isPrimary && $name !== 'PRIMARY') continue;
	        $i = 1;
        	foreach ($idx->fieldNames as $fn) $idxInfo['columns'][$i++] = $fn;
        	$res[$name] = $idxInfo;
        }
        return $res;
    }
    
    /**
     * @return array(relationName=>array('table'=>externalTableName,'columns'=>array('thisField1' => 'thatField1', 'thisField2' => 'thatField2'...)))
     */
    function getRelationsForTable($tableName, $databaseName = false) {
        $res = array();
        $t = $this->_findTableBySqlName($tableName);
        foreach (array_keys($t->fields) as $name) {
        	$f = $t->fields[$name];
        	if ($f->foreign) {
        		$tbl = $this->_db->tables[$f->foreign['table']];
        		$sn = strlen($tbl->sqlName)? $tbl->sqlName : $this->_myPx.$f->foreign['table'];
				$res[] = array('table' => $sn, 'columns' => array($name => $f->foreign['field']));        		
        	}
        }
        return $res;
    }	
	
}
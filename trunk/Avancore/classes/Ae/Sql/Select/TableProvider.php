<?php

class Ae_Sql_Select_TableProvider {
	
	var $_id = false;
	
    var $_tables = array();
    
    var $_tableProviders = array();
    
    /**
     * @var Ae_Sql_Select_TableProvider
     */
    var $_parent = false;
    
    /**
     * Whether hasTable() / getTable() has to _searchTable before calling _doHasTable() / _doGetTable().
     * @var bool
     */
    var $_lookInProvidersBeforeCheckOwn = false;
    
    /**
     * Info where non-own tables were found. Is populated by methods that search in _tableProviders.
     * @var array table alias => id of table provider 
     */
    var $_foundTables = array();
    
    /**
     * Static method to create mapper-based Ae_Sql_Select with automatic table provider based on mapper' relations.
     * 
     * @param string $mapperClass
     * @param Ae_Sql_Db|null $db If no database is provided, Ae_Sql_Db_Ae will be automatically created
     * @param string $alias Alias of primary table
     * @return Ae_Sql_Select
     */
    static function createSelect($mapperClass, & $db, $alias = 't') {
		Ae_Dispatcher::loadClass('Ae_Sql_Select');
		if (empty($db)) {
			Ae_Dispatcher::loadClass('Ae_Sql_Db_Ae');
			$d = & Ae_Dispatcher::getInstance();
			$aDb = new Ae_Sql_Db_Ae($d->database);
		} else {
			$aDb = & $db;
		}
		$m = & Ae_Model_Mapper::getMapper($mapperClass);
		$res = new Ae_Sql_Select($aDb, array(
			'tables' => array(
				$alias => array(
					'name' => $m->tableName,
				),
			),
			'tableProviders' => array(
				'modelSql' => array(
					'class' => 'Ae_Model_Sql_TableProvider',
					'mapperClass' => $mapperClass,
				),
			),
		));
		return $res;
    }
    
    /**
     * @param array $options
     * @param Ae_Sql_Db $db
     * @return Ae_Sql_Select
     */
    function Ae_Sql_Select_TableProvider($options = array()) {
        if (!is_array($options)) trigger_error("\$options must be an array", E_USER_ERROR);
    	Ae_Util::bindAutoparams($this, $options, true);
    }
    
    function _setTables($tables) {
    	if (!is_array($tables)) trigger_error("\$tables must be an array", E_USER_ERROR);
    	foreach (array_keys($tables) as $alias) $this->addTable($tables[$alias], $alias);
    }
    
    function _setTableProviders($tableProviders) {
    	if (!is_array($tableProviders)) trigger_error('\$tableProviders must be an array');
    	foreach (array_keys($tableProviders) as $id) $this->addTableProvider($tableProviders[$id], $id);
    }
    
    /**
     * @param Ae_Sql_Select_TableProvider $tableProvider
     */
    function setParent(& $tableProvider) {
        //if ($this->_tableProvider) trigger_error ("Can set 'tableProvider' property only once", E_USER_ERROR);
        if (!is_a($tableProvider, 'Ae_Sql_Select_TableProvider'))  trigger_error("\$tableProvider must be an instance of Ae_Sql_Select_TableProvider", E_USER_ERROR);
        $this->_parent = & $tableProvider;
    }
    
    /**
     * @return Ae_Sql_Select_TableProvider
     */
    function getParent() {
    	$res = & $this->_parent;
    	return $res;
    }
    
    /**
     * Adds existing table or creates it by prototype.
     * 
     * @param Ae_Sql_Select_Table|array $options Either an Ae_Sql_Select_Table instance of it's prototype array
     * @param string $alias
     * @return Ae_Sql_Select_Table
     */
    function & addTable($options, $alias = false) {
    	if (is_a($options, 'Ae_Sql_Select_Table')) {
            $t = $options;
            $t->setTableProvider($this);
        }
        else {
            if (!is_array($options)) trigger_error("\$options must be an array or an Ae_Sql_Select_Table instance", E_USER_ERROR);
            if (strlen($alias)) $options['alias'] = $alias;
            if (isset($options['class'])) 
                $class = $options['class']; 
                else $class = 'Ae_Sql_Select_Table';
            if (class_exists('Ae_Dispatcher')) Ae_Dispatcher::loadClass($class);
                else if (!class_exists($class)) require(str_replace('_', '/', $class).'.php');
            $t = new $class ($this, $options);
        }
        $alias = $t->alias? $t->alias : $t->name;
        if (!strlen($alias)) trigger_error("name of table must be provided", E_USER_ERROR);
        if (isset($this->_tables[$alias])) {
            trigger_error("table with alias '{$alias}' is already in tables collection", E_USER_ERROR);
        }
        $this->_tables[$alias] = $t;
        return $t;
    }
    
    /**
     * Adds existing tableProvider or creates it by prototype.
     * 
     * @param Ae_Sql_Select_TableProvider|array $options Either an Ae_Sql_Select_TableProvider instance of it's prototype array
     * @param string $id
     * @return Ae_Sql_Select_TableProvider
     */

    function & addTableProvider(& $options, $id = false) {
    	if (is_a($options, 'Ae_Sql_Select_TableProvider')) {
    		$t = & $options;
    		$t->setParent($this);
    	} else {
    		if (!is_array($options)) trigger_error("\$options must be an array or an Ae_Sql_Select_TableProvider instance", E_USER_ERROR);
    		if (strlen($id)) $options['id'] = $id;
    		if (!isset($options['id']) || !strlen($options['id'])) $options['id'] = count($this->_tableProviders) + 1;
    		$options['parent'] = & $this;
    		$t = & Ae_Util::factoryWithOptions ($options, 'Ae_Sql_Select_TableProvider', 'class', true, true);
    	}
    	$id = $t->getId();
    	if (isset($this->_tableProviders[$id])) trigger_error("table provider with id '{$id}' is already in table providers collection", E_USER_ERROR);
    	$this->_tableProviders[$id] = & $t;
    	return $t;
    }

	function hasTable($alias) {
		$res = false;
		if (isset($this->_tables[$alias])) $res = true;
		else {
			if ($this->_lookInProvidersBeforeCheckOwn) {
				if ($this->_doHasTable($alias)) $res = true;
				else {
					$t = null;
					$this->_searchTable($alias, false, $res);
				}
			} else {
				$t = null;
				$this->_searchTable($alias, false, $res);
				if (!$res) {
					$res = $this->_doHasTable($alias);
				}
			}
		}
		return $res;
	}
	
	function & _searchTable($alias, $returnTable, & $found) {
		$res = null;
		
		$found = false;
		if (isset($this->_foundTables[$alias])) {
			$found = true;
			if ($returnTable) {
				$res = & $this->_tableProviders[$this->_foundTables[$alias]]->getTable($alias);
			}		
		} else {
			foreach (array_keys($this->_tableProviders) as $i) {
				if ($this->_tableProviders[$i]->hasTable($alias)) {
					$found = true;
					$this->_foundTables[$alias] = $i;
					if ($returnTable) {
						$res = & $this->_tableProviders[$i]->getTable($alias);
					}
					break;
				}
			}
		}
		return $res;
	}
	
	function _doHasTable($alias) {
		return false;
	}
	
	function & _doGetTable($alias) {
		$res = null;
		return $res;
	}
    
    /**
     * @param string $alias Alias of table that we need
     * @param bool $dontTriggerError Don't trigger_error if table is not found
     * @return Ae_Sql_Select_Table
     */
    function getTable($alias, $dontTriggerError = false) {
    	$res = null;
		if (isset($this->_tables[$alias])) {
			$res = & $this->_tables[$alias];
		} else {
			if ($this->_lookInProvidersBeforeCheckOwn) {
				if ($res = & $this->_doGetTable($alias)) {
					if (!isset($this->_tables[$alias])) $this->_tables[$alias] = & $res;
				} else {
					$res = & $this->_searchTable($alias, true, $found);
				}
			} else {
				$res = & $this->_searchTable($alias, true, $found);
				if (!$found) {
					$res = & $this->_doGetTable($alias);
					if ($res) if (!isset($this->_tables[$alias]))  $this->_tables[$alias] = & $res;
				}
			}
		}
		if (!is_object($res) && !$dontTriggerError) trigger_error("No such table '{$alias}'; check with hasTable() first", E_USER_ERROR);
		return $res;
	}
	
	function _setId($id) {
		$this->_id = $id;
	}
	
	function getId() {
		return $this->_id;
	}
    
    function cleanupReferences() {
        foreach ($this->_tables as $t) $t->_tableProvider = null;
        $this->_tables = array();
        foreach ($this->_tableProviders as $t) {
            $t->cleanupReferences();
            $t->_parent = null;
        }
        $this->_tableProviders = array();
        $this->_foundTables = array();
    }
    
}
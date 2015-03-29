<?php

class Ac_Sql_Select_TableProvider implements Ac_I_Prototyped {
	
	var $_id = false;
	
    var $_tables = array();
    
    var $_tableProviders = array();
    
    /**
     * @var Ac_Sql_Select
     */
    var $_sqlSelect = false;
    
    /**
     * @var Ac_Sql_Select_TableProvider
     */
    var $_parent = false;
    
    /**
     * Whether hasTable() / getTable() has to _searchTable before calling _doHasTable() / _doGetTable().
     * @var bool
     */
    var $_lookInProvidersBeforeCheckOwn = false;
    
    /**
     * Info where non-own tables were found. Is populated by methods that search in _tableProviders.
     * @var array table alias => id of table provider A
     */
    var $_foundTables = array();
    
    /**
     * Static method to create mapper-based Ac_Sql_Select with automatic table provider based on mapper' relations.
     * 
     * @param string $mapperClass
     * @param Ac_Sql_Db|null $db If no database is provided, Ac_Sql_Db_Ae will be automatically created
     * @param string $alias Alias of primary table
     * @return Ac_Sql_Select
     */
    static function createSelect($mapperClass, $db, $alias = 't') {
		if (empty($db)) {
			$aDb = Ac_Application::getDefaultInstance()->getDb();
		} else {
			$aDb = $db;
		}
		$m = Ac_Model_Mapper::getMapper($mapperClass);
		$res = new Ac_Sql_Select($aDb, array(
			'tables' => array(
				$alias => array(
					'name' => $m->tableName,
				),
			),
			'tableProviders' => array(
				'modelSql' => array(
					'class' => 'Ac_Model_Sql_TableProvider',
					'mapperClass' => $mapperClass,
				),
			),
		));
		return $res;
    }
    
    /**
     * @param array $options
     * @param Ac_Sql_Db $db
     * @return Ac_Sql_Select
     */
    function __construct(array $options = array()) {
        if (!is_array($options)) trigger_error("\$options must be an array", E_USER_ERROR);
    	Ac_Util::bindAutoparams($this, $options, true);
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
     * @param Ac_Sql_Select_TableProvider $tableProvider
     */
    function setParent($tableProvider) {
        //if ($this->_tableProvider) trigger_error ("Can set 'tableProvider' property only once", E_USER_ERROR);
        if (!is_a($tableProvider, 'Ac_Sql_Select_TableProvider'))
            trigger_error("\$tableProvider must be an instance of Ac_Sql_Select_TableProvider", E_USER_ERROR);
        $this->_parent = $tableProvider;
        $this->notifyParentChanged();
    }
    
    function notifyParentChanged() {
        $this->_sqlSelect = false;
        foreach ($this->_tables as $t) $t->notifyParentChanged();
        foreach ($this->_tableProviders as $p) $p->notifyParentChanged();
    }
    
    /**
     * @return Ac_Sql_Select
     */
    function getSqlSelect() {
        if ($this->_sqlSelect === false) {
            $this->_sqlSelect = $this;
            while ($this->_sqlSelect && !($this->_sqlSelect instanceof Ac_Sql_Select)) 
                $this->_sqlSelect = $this->_sqlSelect->getParent();
            if (!$this->_sqlSelect || !($this->_sqlSelect instanceof Ac_Sql_Select)) {
                $this->_sqlSelect = null;
            }
        }
        return $this->_sqlSelect;
    }
    
    
    /**
     * @return Ac_Sql_Select_TableProvider
     */
    function getParent() {
    	$res = $this->_parent;
    	return $res;
    }
    
    
    
    /**
     * Adds existing table or creates it by prototype.
     * 
     * @param Ac_Sql_Select_Table|array $options Either an Ac_Sql_Select_Table instance of it's prototype array
     * @param string $alias
     * @return Ac_Sql_Select_Table
     */
    function addTable($options, $alias = false) {
    	if (is_a($options, 'Ac_Sql_Select_Table')) {
            $t = $options;
        }
        else {
            if (!is_array($options)) trigger_error("\$options must be an array or an Ac_Sql_Select_Table instance", E_USER_ERROR);
            if (strlen($alias)) $options['alias'] = $alias;
            if (isset($options['class'])) 
                $class = $options['class']; 
                else $class = 'Ac_Sql_Select_Table';
            $t = new $class ($this, $options);
        }
        $t->setTableProvider($this);
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
     * @param Ac_Sql_Select_TableProvider|array $options Either an Ac_Sql_Select_TableProvider instance of it's prototype array
     * @param string $id
     * @return Ac_Sql_Select_TableProvider
     */

    function addTableProvider($options, $id = false) {
    	if (is_a($options, 'Ac_Sql_Select_TableProvider')) {
    		$t = $options;
    		$t->setParent($this);
    	} else {
    		if (!is_array($options)) trigger_error("\$options must be an array or an Ac_Sql_Select_TableProvider instance", E_USER_ERROR);
    		if (strlen($id)) $options['id'] = $id;
    		if (!isset($options['id']) || !strlen($options['id'])) $options['id'] = count($this->_tableProviders) + 1;
    		$options['parent'] = $this;
    		$t = Ac_Util::factoryWithOptions ($options, 'Ac_Sql_Select_TableProvider', 'class', true, true);
    	}
    	$id = $t->getId();
    	if (isset($this->_tableProviders[$id])) trigger_error("table provider with id '{$id}' is already in table providers collection", E_USER_ERROR);
    	$this->_tableProviders[$id] = $t;
    	return $t;
    }
    
    /**
     * @return Ac_Sql_Select_TableProvider
     */
    function getTableProvider($id) {
        if (!isset($this->_tableProviders[$id])) throw Ac_E_InvalidCall::noSuchItem("tableProvider", $id, "listTableProviders");
        return $this->_tableProviders[$id];
    }
    
    function listTableProviders() {
        return array_keys($this->_tableProviders);
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
	
	function _searchTable($alias, $returnTable, & $found) {
		$res = null;
		
		$found = false;
		if (isset($this->_foundTables[$alias])) {
			$found = true;
			if ($returnTable) {
				$res = $this->_tableProviders[$this->_foundTables[$alias]]->getTable($alias);
			}		
		} else {
			foreach (array_keys($this->_tableProviders) as $i) {
				if ($this->_tableProviders[$i]->hasTable($alias)) {
					$found = true;
					$this->_foundTables[$alias] = $i;
					if ($returnTable) {
						$res = $this->_tableProviders[$i]->getTable($alias);
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
	
	function _doGetTable($alias) {
		$res = null;
		return $res;
	}
    
    /**
     * @param string $alias Alias of table that we need
     * @param bool $dontTriggerError Don't trigger_error if table is not found
     * @return Ac_Sql_Select_Table
     */
    function getTable($alias, $dontTriggerError = false) {
    	$res = null;
		if (isset($this->_tables[$alias])) {
			$res = $this->_tables[$alias];
		} else {
			if ($this->_lookInProvidersBeforeCheckOwn) {
				if ($res = $this->_doGetTable($alias)) {
					if (!isset($this->_tables[$alias])) $this->_tables[$alias] = $res;
				} else {
					$res = $this->_searchTable($alias, true, $found);
				}
			} else {
				$res = $this->_searchTable($alias, true, $found);
				if (!$found) {
					$res = $this->_doGetTable($alias);
					if ($res) if (!isset($this->_tables[$alias]))  $this->_tables[$alias] = $res;
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
        foreach ($this->_tables as $t) {
            $t->setTableProvider(null);
        }
        $this->_tables = array();
        foreach ($this->_tableProviders as $t) {
            $t->cleanupReferences();
            $t->setParent(null);
        }
        $this->_tableProviders = array();
        $this->_foundTables = array();
    }
    
    function hasPublicVars() {
        return true;
    }    
    
    /**
     * return Ac_Sql_Select_TableProvider
     */
    function cloneObject() {
        return clone $this;
    }
 
    function __clone() {
        foreach ($this->_tableProviders as $i => $tp) {
            $this->_tableProviders[$i] = clone $tp;
            $this->_tableProviders[$i]->setParent($this);
        }
        $this->_foundTables = array();
        foreach ($this->_tables as $i => $t) {
            $this->_tables[$i] = clone $t;
            $this->_tables[$i]->setTableProvider($this);
        }
    }
    
}
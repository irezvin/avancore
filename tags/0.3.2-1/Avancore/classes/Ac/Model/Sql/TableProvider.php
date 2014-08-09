<?php

class Ac_Model_Sql_TableProvider extends Ac_Sql_Select_TableProvider {
	
	var $_mapperClass = false;
	var $_mapper = false;
	
	/**
	 * Cached info on alias paths.
	 * Format: 
	 * <code>
	 * array (
	 *     "$baseAlias" => array('propName' => $baseAlias, 'relationId' => $relationId, 'mapperClass' => $mapperClass),	
	 *     "{$baseAlias}[{$subTable}]' => array('prevAlias' => $baseAlias, 'propName' => $subTable, 'relationId' => $relationId2, 'mapperClass' => $mapperClass2)
	 *     "$badAlias" => false
	 *     //...
	 * )
	 * </code>
	 * @var array 
	 */
	var $_aliasPaths = array();
	
    protected $defaultJoinType = 'LEFT JOIN';
    
    protected $ignoreMidWhere = false;

    function setDefaultJoinType($defaultJoinType) {
        $this->defaultJoinType = $defaultJoinType;
    }

    function getDefaultJoinType() {
        return $this->defaultJoinType;
    }

    function setIgnoreMidWhere($ignoreMidWhere) {
        $this->ignoreMidWhere = $ignoreMidWhere;
    }

    function getIgnoreMidWhere() {
        return $this->ignoreMidWhere;
    }    
    
    function _setMapperClass($mapperClass) {
		$this->_mapperClass = $mapperClass;
	}
	
	function getMapperClass() {
		if ($this->_mapper) $res = Ac_Util::fixClassName(get_class($this->_mapper));
			else $res = $this->_mapperClass;
		return $res;
	} 
	
	function _setMapper($mapper) {
		if (!is_a($mapper, 'Ac_Model_Mapper')) trigger_error("\$mapper should be an instance of Ac_Model_Mapper", E_USER_ERROR);
		$this->_mapper = $mapper; 
	}
	
	function getMapper($required = false) {
		if (!$this->_mapper && strlen($this->_mapperClass)) {
			$this->_mapper = Ac_Model_Mapper::getMapper($this->_mapperClass);
		}
		$res = $this->_mapper;
		if ($required && !$res) trigger_error("Neither \$mapper nor \$mapperClass are provided", E_USER_ERROR);
		return $res;
	}
	
	/**
	 * Parses and retrieves info on $alias; returns entry from $this->_aliasPaths
	 * @param $alias
	 * @return array|false
	 */
	function _searchPath($alias) {
		if (!isset($this->_aliasPaths[$alias])) {
			$path = Ac_Util::pathToArray($alias);
			$last = $path[count($path) - 1];
            list($last, $suffix) = array_merge(explode(':', $last, 2), array(''));
			if (count($path) > 1) {
				$baseAlias = Ac_Util::arrayToPath(array_slice($path, 0, count($path) - 1));
				$baseInfo = $this->_searchPath($baseAlias);
			} else {
				$baseInfo = array('mapperClass' => $this->getMapperClass());
			}
			if ($baseInfo) {
				$mapper = Ac_Model_Mapper::getMapper($baseInfo['mapperClass']);
                if (!$mapper) throw new Exception("Mapper '{$baseInfo['mapperClass']}' not found");
				$proto = $mapper->getPrototype();
				$pi = $proto->getPropertyInfo($last, true);
				if (isset($pi->mapperClass) && $pi->mapperClass && isset($pi->relationId) && ($pi->relationId)) {
					$info = array('propName' => $last, 'mapperClass' => $pi->mapperClass, 'relationId' => $pi->relationId);
					if (count($path) > 1) $info['prevAlias'] = $baseAlias;
					$this->_aliasPaths[$alias] = $info; 
				} else {
					$this->_aliasPaths[$alias] = false;
				}
			} else {
				$this->_aliasPaths[$alias] = false;
			}
		}
		return $this->_aliasPaths[$alias];
	}
	
	function _doHasTable($alias) {
        if (!strncmp($alias, 'mid__', 5)) $alias = substr($alias, 5);
		return $this->_searchPath($alias) !== false;
	}
	
	function _doGetTable($alias, $prototypeOnly = false) {
        $origAlias = $alias;
        if (!strncmp($alias, 'mid__', 5)) $alias = substr($alias, 5);
        $p = $this->_searchPath($alias);
		if ($p) {
			$m = Ac_Model_Mapper::getMapper($p['mapperClass']);
			if (isset($p['prevAlias'])) {
				$prevPath = $this->_searchPath($p['prevAlias']);
				$prevMapper = Ac_Model_Mapper::getMapper($prevPath['mapperClass']);
				$joinsAlias = $p['prevAlias'];	
			}
			else {
				$sqs = $this->getSqlSelect(true);
				$joinsAlias = $sqs->getEffectivePrimaryAlias();
				$prevMapper = $this->getMapper(true);
			}
			$rel = $prevMapper->getRelation($p['relationId']);
			$protos = array();
			if ($rel->midTableName) {
				$midAlias = 'mid__'.$alias;
                if (!isset($this->_tables[$midAlias])) {
                    $protos[$midAlias] = array(
                        'name' => $rel->midTableName,
                        'joinsAlias' => $joinsAlias,
                        'joinType' => $this->defaultJoinType,
                        'joinsOn' => array_flip($rel->fieldLinks),
                    );
                    if ($rel->midWhere !== false && !$this->ignoreMidWhere) {
                        $protos[$midAlias]['joinsOn'][] = new Ac_Sql_Expression($rel->getStrMidWhere($midAlias));
                    }
                    $joinsAlias = $midAlias;
                }
			}
			$protos[$alias] = array(
				'name' => $m->tableName,
				'joinsAlias' => $joinsAlias,
				'joinType' => $this->defaultJoinType,
				'joinsOn' => $rel->midTableName? array_flip($rel->fieldLinks2) : array_flip($rel->fieldLinks)
			);
			foreach ($protos as $alias => $proto) {
				$t = $this->addTable($proto, $alias);
			}
			$res = $this->_tables[$origAlias];
		} else {
			$res = null;
		}
		return $res;
	}
	
    /**
     * @param Ac_Sql_Select $sqlSelect
     */
    function getSqlSelect($required = false) {
    	$res = $this->getParent();
    	while ($res && !is_a($res, 'Ac_Sql_Select')) {
    		$res = $res->getParent();
    	}
    	if ($required && !$res) trigger_error("Cannot retrieve an instance of Ac_Sql_Select (it isn't in any of the parents)", E_USER_ERROR);
    	return $res;
    }
	
}
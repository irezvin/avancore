<?php

Ae_Dispatcher::loadClass('Ae_Sql_Select_TableProvider');

class Ae_Model_Sql_TableProvider extends Ae_Sql_Select_TableProvider {
	
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
	
	function _setMapperClass($mapperClass) {
		$this->_mapperClass = $mapperClass;
	}
	
	function getMapperClass() {
		if ($this->_mapper) $res = Ae_Util::fixClassName(get_class($this->_mapper));
			else $res = $this->_mapperClass;
		return $res;
	} 
	
	function _setMapper(& $mapper) {
		if (!is_a($mapper, 'Ae_Model_Mapper')) trigger_error("\$mapper should be an instance of Ae_Model_Mapper", E_USER_ERROR);
		$this->_mapper = & $mapper; 
	}
	
	function getMapper($required = false) {
		if (!$this->_mapper && strlen($this->_mapperClass)) {
			$this->_mapper = & Ae_Dispatcher::getMapper($this->_mapperClass);
		}
		$res = & $this->_mapper;
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
			$path = Ae_Util::pathToArray($alias);
			$last = $path[count($path) - 1];
			if (count($path) > 1) {
				$baseAlias = Ae_Util::arrayToPath(array_slice($path, 0, count($path) - 1));
				$baseInfo = $this->_searchPath($baseAlias);
			} else {
				$baseInfo = array('mapperClass' => $this->getMapperClass());
			}
			if ($baseInfo) {
				$mapper = & Ae_Dispatcher::getMapper($baseInfo['mapperClass']);
				$proto = & $mapper->getPrototype();
				$pi = & $proto->getPropertyInfo($last, true);
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
		return $this->_searchPath($alias) !== false;
	}
	
	function & _doGetTable($alias) {
		$p = $this->_searchPath($alias);
		if ($p) {
			$m = & Ae_Dispatcher::getMapper($p['mapperClass']);
			if (isset($p['prevAlias'])) {
				$prevPath = $this->_searchPath($p['prevAlias']);
				$prevMapper = & Ae_Dispatcher::getMapper($prevPath['mapperClass']);
				$joinsAlias = $p['prevAlias'];	
			}
			else {
				$sqs = & $this->getSqlSelect(true);
				$joinsAlias = $sqs->getEffectivePrimaryAlias();
				$prevMapper = & $this->getMapper(true);
			}
			$rel = & $prevMapper->getRelation($p['relationId']);
			$protos = array();
			if ($rel->midTableName) {
				$midAlias = 'mid-'.$alias;
				$protos[$midAlias] = array(
					'name' => $rel->midTableName,
					'joinsAlias' => $joinsAlias,
					'joinType' => 'LEFT JOIN',
					'joinsOn' => array_flip($rel->fieldLinks),
				);
				$joinsAlias = $midAlias;
			}
			$protos[$alias] = array(
				'name' => $m->tableName,
				'joinsAlias' => $joinsAlias,
				'joinType' => 'LEFT JOIN',
				'joinsOn' => $rel->midTableName? array_flip($rel->fieldLinks2) : array_flip($rel->fieldLinks)
			);
			foreach ($protos as $alias => $proto) {
				$t = & $this->addTable($proto, $alias);
			}
			$res = & $this->_tables[$alias];
		} else {
			$res = null;
		}
		return $res;
	}
	
    /**
     * @param Ae_Sql_Select $sqlSelect
     */
    function getSqlSelect($required = false) {
    	$res = & $this->getParent();
    	while ($res && !is_a($res, 'Ae_Sql_Select')) {
    		$res = & $res->getParent();
    	}
    	if ($required && !$res) trigger_error("Cannot retrieve an instance of Ae_Sql_Select (it isn't in any of the parents)", E_USER_ERROR);
    	return $res;
    }
	
}
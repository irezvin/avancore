<?php

class Ac_Tree_NestedSetsMapper extends Ac_Model_Mapper implements Ac_I_Tree_Mapper_NestedSets {
	
    var $_nestedSets = false;
    
    var $_nsRelation = false;
    
    var $_rootNodeId = false;
    
    var $_childrenCountRelation = false;
    
    var $_allChildrenCountRelation = false;
    
    var $_childIdsRelation = false;
    
    var $_containersRelation = false;
    
    var $_treeProvider = false;
    
    // Variables to be overridden in concrete class
    
    /**
     * ID of this tree in the nestedSets table
     */
    var $nsTreeId = false; 
    
    /**
     * Name of nestedSets table
     */
    var $nsTableName = '#__nested_sets'; 
    
    /**
     * Prototype of Ac_Sql_NestedSets
     */
    var $nsPrototype = array();

    
    
    function getRootNodeId() {
        if ($this->_rootNodeId === false) {
            $ns = $this->getNestedSets();
            $root = $ns->getRootNode();
            if ($root) $this->_rootNodeId = $root[$ns->idCol];
                else $this->_rootNodeId = $ns->addRootNode(true, array('comment' => $this->recordClass));
        }
        return $this->_rootNodeId;
    }
    
    /**
     * @return Ac_Sql_NestedSets
     */
    function getNestedSets() {
        if ($this->_nestedSets === false) {
            $proto = $this->nsPrototype;
            if (!isset($proto['blocker'])) $proto['blocker'] = new Ac_Sql_Blocker();
            if (!isset($proto['db'])) $proto['db'] = new Ac_Sql_Db_Ae($this->database);
            if (!isset($proto['tableName']) && strlen($this->nsTableName)) $proto['tableName'] = $this->nsTableName;
            if (!isset($proto['treeId']) && strlen($this->nsTreeId)) $proto['treeId'] = $this->nsTreeId;
        	$this->_nestedSets = new Ac_Sql_NestedSets($proto);
        }
        return $this->_nestedSets;
    }
    
    function listTopNodes() {
        $ns = $this->getNestedSets();
        $l = $ns->getChildren($this->getRootNodeId(), false, 1);
        $res = array();
        if (!$l) $l = array();
        foreach ($l as $i) $res[] = $i[$ns->idCol];
        return $res; 
    }
    
    function getNodeClass() {
        return 'Ac_Tree_NestedSetsImpl';
    }
    
    function loadNodes(array $ids) {
        $ns = $this->getNestedSets();
        $this->database->setQuery("SELECT * FROM ".$ns->tableName
            ." WHERE ".$ns->treeCol.' = '.$this->database->Quote($this->nsTreeId)
            ." AND ".$ns->idCol." ".$this->database->sqlEqCriteria($ids)
        );
        $res = array();
        $nodes = $this->database->loadAssocList($ns->idCol);
        foreach ($nodes as $id => $node) {
            $objNode = new Ac_Tree_NestedSetsImpl(array(
                'nodeData' => $node,
                'mapper' => $this, 
            ));
            $res[$id] = $objNode;
        }
        return $res;
    }
    
    /**
     * @return Ac_Model_Relation
     */
    function getNsRelation() {
    	$ns = & $this->getNestedSets();
        if ($this->_nsRelation === false) {
            $this->_nsRelation = new Ac_Model_Relation(array(
                'srcTableName' => $ns->tableName,
                'destMapperClass' => get_class($this),
                'fieldLinks' => array($ns->idCol => $this->pk),
                'srcIsUnique' => true,
                'destIsUnique' => true,
                'srcVarName' => 'modelObject',
                'destVarName' => '_treeNode',
            ));
        }
        return $this->_nsRelation;
    }

    
    function loadNodeChildrenCounts(array $nodes) {
        $rel = $this->getNodeChildrenCountRelation();
        return $rel->loadDest($nodes, false, true);        
    }
    
    function loadNodeAllChildrenCounts(array $nodes) {
        $rel = $this->getAllChildrenCountRelation();
        return $rel->loadDest($nodes, false, true);        
    }
    
    function loadNodeChildIds(array $nodes) {
        $rel = $this->getNodeChildIdsRelation();
        $n = Ac_Util::flattenArray($rel->loadDest($nodes, false, true), 1);
        $idc = $this->getNestedSets()->idCol;
        $ids = array();
        foreach ($n as $i) $ids[] = $i[$idc];
    	return $ids;
    }
    
    function loadNodeContainers(array $nodes) {
        $rel = $this->getNodeContainersRelation();
        return $rel->loadDest($nodes, false, true);
    }
    
    /**
     * @return Ac_Model_Relation
     */
    function getNodeChildrenCountRelation() {
        if ($this->_childrenCountRelation === false) {
            $ns = $this->getNestedSets();
            $this->_childrenCountRelation = new Ac_Model_Relation(array(
                'srcTableName' => $this->tableName,
                'destTableName' => new Ac_Sql_Expression("(
                    SELECT {$ns->parentCol} AS parentId, COUNT(ns.{$ns->idCol}) AS `count` 
                    FROM {$ns->tableName} AS ns 
                    WHERE NOT ISNULL({$ns->parentCol}) AND {$ns->treeCol} = ".$this->database->Quote($this->nsTreeId).' 
                    GROUP BY parentId) AS nsc'
                ),
                'fieldLinks' => array(
                    'nodeId' => 'parentId',
                ),
                'srcVarName' => 'childNodesCount',
                'srcIsUnique' => true,
                'destIsUnique' => true,
                'database' => $this->database,
            ));
        }
        return $this->_childrenCountRelation;
    }
    
    /**
     * @return Ac_Model_Relation
     */
    function getAllChildrenCountRelation() {
        if ($this->_allChildrenCountRelation === false) {
            $ns = $this->getNestedSets();
            $this->_allChildrenCountRelation = new Ac_Model_Relation(array(
                'srcTableName' => $this->tableName,
                'destTableName' => new Ac_Sql_Expression("(
                    SELECT parents.{$ns->idCol} AS parentId, COUNT(children.{$ns->idCol}) AS `count` 
                    FROM {$ns->tableName} AS parents 
                    INNER JOIN {$ns->tableName} AS children
                        ON children.{$ns->treeCol} = parents.{$ns->treeCol} 
                        AND children.{$ns->leftCol} > parents.{$ns->leftCol} 
                        AND children.{$ns->leftCol} < parents.{$ns->rightCol}
                    WHERE parents.{$ns->treeCol} = ".$this->database->Quote($this->nsTreeId).' 
                    GROUP BY parentId) AS nsc'),
                'fieldLinks' => array(
                    'nodeId' => 'parentId',
                ),
                'srcVarName' => 'allChildNodesCount',
                'srcIsUnique' => true,
                'destIsUnique' => true,
                'database' => $this->database,
            ));
        }
        return $this->_allChildrenCountRelation;
    }
    
    /**
     * @return Ac_Model_Relation
     */
    function getNodeChildIdsRelation() {
        if ($this->_childIdsRelation === false) {
            $ns = $this->getNestedSets();
        	$this->_childIdsRelation = new Ac_Model_Relation(array(
                'srcTableName' => $this->tableName,
                'destTableName' => $ns->tableName,
                'fieldLinks' => array(
                    'nodeId' => $ns->parentCol,
                ),
                'srcVarName' => 'childNodeIds',
                'srcIsUnique' => true,
                'destIsUnique' => false,
                'database' => $this->database,
                'destWhere' => $ns->treeCol.' = '.$this->database->Quote($this->nsTreeId),
                'destOrdering' => $ns->leftCol,
            ));
        }
        return $this->_childIdsRelation;
    }
    
    /**
     * @return Ac_Model_Relation
     */
    function getNodeContainersRelation() {
        if ($this->_containersRelation === false) {
            $ns = $this->getNestedSets();
            $this->_containersRelation = new Ac_Model_Relation(array(
                'srcTableName' => $ns->tableName,
                'destMapperClass' => get_class($this),
                'fieldLinks' => array(
                    'nodeId' => $this->pk,
                ),
                'srcVarName' => 'container',
                'srcIsUnique' => true,
                'destIsUnique' => true,
                'database' => $this->database,
                'srcWhere' => $ns->treeCol.' = '.$this->database->Quote($this->nsTreeId)
            ));
        }
        return $this->_containersRelation;
    }
    
    /**
     * @return Ac_I_Tree_Provider
     */
    function getDefaultTreeProvider() {
        if ($this->_treeProvider === false) {
            $this->_treeProvider = $this->createTreeProvider();
        }
        return $this->_treeProvider;
    }

    /**
     * @return Ac_I_Tree_Provider
     */
    function createTreeProvider() {
    	return new Ac_Tree_Provider($this);
    }

    protected function getOrderingValuesColumns() {
        return 't.'.$this->getTitleFieldName();
    }
    
    protected function getOrderingValuesLabel(array $entry) {
        return implode(' - ', $entry);
    }
    
    /** 
     * @return array 
     */
    function getOrderingValues(Ac_Model_Object $modelObject) {
        $res = array();
        $ords = array();
        $pId = $modelObject->getTreeImpl()->getParentNodeId();
        $ns = $this->getNestedSets();
        if (is_null($pId)) $pId = $this->getRootNodeId();
        $foundMyself = false;
        if (strlen($pId)) {
            $this->database->setQuery($sql = "
                SELECT ns.ordering, ".$this->getOrderingValuesColumns().", t.id 
                FROM {$this->tableName} AS t 
                INNER JOIN {$ns->tableName} AS ns ON t.id = ns.{$ns->idCol} 
                WHERE ns.{$ns->treeCol} = {$this->nsTreeId} AND ns.{$ns->parentCol} = ".$this->database->Quote($pId)." 
                ORDER BY ns.ordering ASC
            ");
            $ords = $this->database->loadAssocList();
            foreach ($ords as $ord) {
                $lbl = $this->getOrderingValuesLabel($ord);
                if ($ord['id'] == $modelObject->id) {
                    $lbl .= ' '.(new Ac_Lang_String('model_ordering_current'));
                    $foundMyself = true;
                }
                $res[$ord['ordering']] = $lbl;   
            }
        } else $ords = array();
        if (!count($ords)) $res[' 0'] = new Ac_Lang_String('model_ordering_only');
        elseif (!$foundMyself) {
            $res[' '.($ord['ordering'] + 1)] = new Ac_Lang_String('model_ordering_last');
        }
        return $res;
    }
    
    /**
     * @return Ac_I_Tree_Impl
     */
    function createTreeImpl(Ac_Model_Object $modelObject) {
        return new Ac_Tree_NestedSetsImpl(array(
            'container' => $modelObject,
            'mapper' => $this,
        ));        
    }
    
    function hasOriginalData() {
        return (bool) $this->getTreeNode();
    }
    

    function fixTree($dontApply = false, $extraColumns = '') {
        
        $treeTableName = $this->getNestedSets()->tableName;
        
		$sql = new Ac_Sql_Db_Ae();
		
		$treeArr = $this->buildAnyTreeArr($this->nsTreeId, $extraColumns);
		
		$tree1 = new Ac_Sql_NestedSets(array(
			'treeId' => $this->nsTreeId + 110, 
			'db' => $sql, 
			'tableName' => $treeTableName, 
			'blocker' => new Ac_Sql_Blocker(),
		));
		$tree1->clearTree();
		$tree1->addRootNode(true);
		$root = $tree1->getRootNode();
		$rootId = $root['id'];
		
		$treeArr = array_values($treeArr);
		
		var_dump('Root node is ', $root);
		
		$this->saveTreeArr($treeArr, $tree1, $rootId, $this->getRootNodeId());
			
		if (!$dontApply) {
    		
    		$treeId2 = $this->nsTreeId + 111;
    		$sql->query("DELETE FROM {$treeTableName} WHERE treeId = ".$sql->q($treeId2));
    		$s = new Ac_Sql_Statement("UPDATE {$treeTableName} SET treeId = {{bak}} WHERE treeId = {{curr}}", array(
    			'curr' => $this->nsTreeId, 'bak' => $treeId2
    		));
    		$sql->query($s);
    		$s = new Ac_Sql_Statement("UPDATE {$treeTableName} SET treeId = {{curr}} WHERE treeId = {{temp}}", array(
    			'curr' => $this->nsTreeId, 'temp' => $tree1->treeId
    		));
    		$sql->query($s);
    		return $this->nsTreeId + 110;
		} else return $this->nsTreeId + 110;
		
    }
    
    function buildAnyTreeArr($nsTreeId, $extraColumns = '') {
        
        $treeTableName = $this->getNestedSets()->tableName;
        
		$sql = new Ac_Sql_Db_Ae();
		$query = "
			SELECT ns.id, ns.parentId, ns.ordering {$extraColumns}
			FROM {$treeTableName} ns
				 LEFT JOIN {$this->tableName} c
				 ON c.{$this->pk} = ns.id
			WHERE 
				(NOT ISNULL(c.{$this->pk}) OR ns.depth = 0) 
				AND treeId = ".$sql->q($nsTreeId);
		$arr = $sql->fetchArray($query, 'id');
		$tree = $this->buildTreeArr($arr, null);
		
		return $tree;
        
    }
	
	function buildTreeArr($arr, $parentId = '0', $stack = array()) {
		$res = array();
		foreach ($arr as $k => $item) if ($item['parentId'] === $parentId) {
			$res[$k] = $item;
			if (!in_array($item['id'], $stack)) {
				$stack[] = $item['id'];
				$res[$k]['children'] = $this->buildTreeArr($arr, $item['id'], $stack);
			} else {
				trigger_error("Circular reference detected from item ".$item['id'], E_USER_ERROR);
			}
		}
		uasort($res, array($this, 'srt'));
		return $res;
	}
	
	function saveTreeArr($items, Ac_Sql_NestedSets $ns, $rootId, $oldRootId) {
		foreach ($items as $arr) {
			$pid = $arr['parentId'];
			if ($pid == $oldRootId) $pid = $rootId;
			$res = $ns->addNode($pid, $arr['ordering'], array('id' => $arr['id']));
			if (isset($arr['children'])) $this->saveTreeArr($arr['children'], $ns, $rootId, $oldRootId);
		}
	}
	
	function srt($item1, $item2) {
		return $item1['ordering'] - $item2['ordering'];
	}    	
    
    function findProblems($fix = false, $itemId = false) {
        $db = $this->sqlDb;
        $ns = $this->getNestedSets();
        $treeTableName = $this->nsTableName;
        $myTableName = $this->tableName;
    
        $pk = $this->pk;
        $title = $this->getTitleFieldName();
        if (!strlen($title)) $title = $pkName;
        
        $treeId = $db->q($this->nsTreeId);
        if ($itemId !== false) {
            $andItemId = " AND (ns.id = ".($db->q($this->itemId)).")";
        } else {
            $andItemId = "";
}
        
        $wrongParents = $db->fetchArray(
            "
            SELECT 
                ns.id, ns.parentId, parents.id AS nsParentId, ns.leftCol, ns.rightCol, 
                    bt.{$title} AS `item`, pt.{$title} AS `parentByParentId`, 
                    nspt.{$title} AS `parentByNs`
                    
            FROM nc_nested_sets ns 
                INNER JOIN nc_nested_sets parents ON parents.treeId = ns.treeId 
                INNER JOIN {$myTableName} bt ON bt.{$pk} = ns.id AND ns.treeId = {$treeId}
                LEFT JOIN {$myTableName} pt ON pt.{$pk} = ns.parentId
                LEFT JOIN {$myTableName} nspt ON nspt.{$pk} = parents.id
            WHERE ns.leftCol > parents.leftCol AND ns.rightCol < parents.rightCol AND ns.depth = parents.depth + 1 {$andItemId}
            HAVING nsParentId <> ns.parentId
            "
        );
                
        $wrongDepth = $db->fetchArray(
            "
            SELECT ns.id, ns.parentId, parents.id AS nsParentId, ns.leftCol, ns.rightCol, 
                bt.{$title} AS `item`, ns.depth, COUNT(parents.id) AS nsDepth
            FROM nc_nested_sets ns 
            INNER JOIN nc_nested_sets parents ON parents.treeId = ns.treeId 
            INNER JOIN {$myTableName} bt ON bt.{$pk} = ns.id AND ns.treeId = {$treeId}
            WHERE ns.leftCol > parents.leftCol AND ns.rightCol < parents.rightCol {$andItemId}
            GROUP BY ns.id
            HAVING depth <> nsDepth
            "
        );
            
        $fixed = false;
            
        if ($fix && $wrongParents && !$wrongDepth) {
            
            $db->query("
                UPDATE 

                nc_nested_sets ns 
                INNER JOIN nc_nested_sets parents ON parents.treeId = ns.treeId 

                SET ns.parentId = parents.id

                WHERE 
                    (ns.treeId = {$treeId}) AND (ns.leftCol > parents.leftCol) AND (ns.rightCol < parents.rightCol)  {$andItemId}
                    AND (ns.depth = parents.depth + 1)
            ");
                    
            $fixed = true;
                    
        }
        
        $res = array(
            'wrongParents' => $wrongParents,
            'wrongDepth' => $wrongDepth,
            'fixed' => $fixed
        );
        
        return $res;
                
    }
    
}
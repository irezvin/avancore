<?php

/**
 * 
 */

class Ac_Model_Tree_NestedSetsMapper extends Ac_Mixable {
	
    protected $mixableId = 'treeMapper';
    
    protected $mixinClass = 'Ac_Model_Mapper';
    
    /**
     * @var Ac_Model_Mapper
     */
    protected $mixin = false;
    
    protected $nestedSets = false;
    
    protected $nsRelation = false;
    
    protected $rootNodeId = false;
    
    protected $childrenCountRelation = false;
    
    protected $allChildrenCountRelation = false;
    
    protected $childIdsRelation = false;
    
    protected $containersRelation = false;
    
    protected $treeProvider = false;
    
    protected $isCreatingRootNode = false;
    
    // Variables to be overridden in concrete class
    
    /**
     * ID of this tree in the nestedSets table
     */
    var $nsTreeId = false; 
    
    var $nsIdCol = false;
    
    var $nsTreeCol = false;
    
    /**
     * Name of nestedSets table
     */
    var $nsTableName = false; 
    
    var $addMixableToRecords = true;
    
    /**
     * Prototype of Ac_Sql_NestedSets
     */
    var $nsPrototype = array();
    
    var $rootNodePrototype = array();
    
    function hasPublicVars() {
        return true;
    }
    
    /**
     * @return Ac_Sql_Db
     */
    protected function getDb() {
        return $this->mixin->getDb();
    }
    
    protected function createRootNode() {
        $this->isCreatingRootNode = true;
        if (Ac_Accessor::methodExists($this->mixin, 'createRootNode')) {
            $res = $this->mixin->createRootNode();
        } else {
            $ns = $this->getNestedSets();
            $res = $ns->addRootNode(true, $this->rootNodePrototype);
        }
        $this->isCreatingRootNode = false;
        return $res;
    }
    
    function getRootNodeId() {
        if ($this->rootNodeId === false) {
            if (!$this->isCreatingRootNode) {
                $ns = $this->getNestedSets();
                $root = $ns->getRootNode();
                if ($root) $this->rootNodeId = $root[$ns->idCol];
                    else $this->rootNodeId = $this->createRootNode();
            }
        }
        return $this->rootNodeId;
    }
    
    function getIsSameTable() {
        $res = $this->mixin->tableName == $this->getNestedSets()->tableName;
        return $res;
    }
    
    function getIsCreatingRootNode() {
        return $this->isCreatingRootNode;
    }
    
    /**
     * @return Ac_Sql_NestedSets
     */
    function getNestedSets() {
        if ($this->nestedSets === false) {
            $proto = $this->nsPrototype;
            if (!isset($proto['blocker'])) $proto['blocker'] = new Ac_Sql_Blocker();
            if (!isset($proto['db'])) $proto['db'] = $this->mixin->getDb();
            if (!isset($proto['tableName'])) {
                if (strlen($this->nsTableName)) 
                    $proto['tableName'] = $this->nsTableName;
                else
                    $proto['tableName'] = $this->mixin->tableName;
            }
            if (!isset($proto['idCol'])) {
                if (strlen($this->nsIdCol)) $proto['idCol'] = $this->nsIdCol;
                else $proto['idCol'] = $this->mixin->pk;
            }
            if (!isset($proto['treeId']) && strlen($this->nsTreeId)) $proto['treeId'] = $this->nsTreeId;
            if (!isset($proto['treeCol'])) $proto['treeCol'] = $this->nsTreeCol;
            $aiField = $this->mixin->getAutoincFieldName();
            if (!isset($proto['idIsAutoInc']) && $this->mixin->tableName == $proto['tableName']) {
                if (strlen($aiField) && ($aiField == $proto['idCol'])) {
                    $proto['idIsAutoInc'] = true;
                }
            }
        	$this->nestedSets = new Ac_Sql_NestedSets($proto);
        }
        return $this->nestedSets;
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
        return 'Ac_Model_Tree_NestedSetsImpl';
    }
    
    protected function nsTreeCriterion($alias = false, $expressionIfNone = "1") {
        if (strlen($tc = $this->getNestedSets()->treeCol)) {
            $c = strlen($alias)? array($alias, $tc) : $tc;
            $res = $this->getDb()->nameQuote($c) . $this->getDb()->eqCriterion($this->nsTreeId);
        } else {
            $res = $expressionIfNone;
        }
        return $res;
    }
    
    function loadNodes(array $ids) {
        $ns = $this->getNestedSets();
        if ($this)
        $nodes = $this->getDb()->fetchArray(
            "SELECT * FROM ".$ns->tableName
            ." WHERE ".$this->nsTreeCriterion()
            ." AND ".$ns->idCol." ".$this->getDb()->eqCriterion($ids)
        );
        $res = array();
        foreach ($nodes as $id => $node) {
            $objNode = new Ac_Model_Tree_NestedSetsImpl(array(
                'nodeData' => $node,
                'mapper' => $this->mixable, 
            ));
            $res[$id] = $objNode;
        }
        return $res;
    }
    
    /**
     * @return Ac_Model_Relation
     */
    function getNsRelation() {
    	$ns = $this->getNestedSets();
        if ($this->nsRelation === false && $this->mixin) {
            if (strlen($this->nsTableName)) {
                $this->nsRelation = new Ac_Model_Relation(array(
                    'srcTableName' => $ns->tableName,
                    'destMapper' => $this->mixin,
                    'fieldLinks' => array($ns->idCol => $this->mixin->pk),
                    'srcIsUnique' => true,
                    'destIsUnique' => true,
                    'srcVarName' => 'modelObject',
                    'destVarName' => '_treeNode',
                ));
            } else {
                $this->nsRelation = null;
            }
        }
        return $this->nsRelation;
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
        if ($this->childrenCountRelation === false) {
            $ns = $this->getNestedSets();
            $this->childrenCountRelation = new Ac_Model_Relation(array(
                'srcTableName' => $this->mixin->tableName,
                'destTableName' => new Ac_Sql_Expression("(
                    SELECT {$ns->parentCol} AS parentId, COUNT(ns.{$ns->idCol}) AS `count` 
                    FROM {$ns->tableName} AS ns 
                    WHERE NOT ISNULL({$ns->parentCol}) AND ".$this->nsTreeCriterion()." 
                    GROUP BY parentId) AS nsc"
                ),
                'fieldLinks' => array(
                    'nodeId' => 'parentId',
                ),
                'srcVarName' => 'childNodesCount',
                'srcIsUnique' => true,
                'destIsUnique' => true,
                'database' => $this->getDb(),
            ));
        }
        return $this->childrenCountRelation;
    }
    
    /**
     * @return Ac_Model_Relation
     */
    function getAllChildrenCountRelation() {
        if ($this->allChildrenCountRelation === false) {
            $ns = $this->getNestedSets();
            if (strlen($ns->treeCol))
                $treeJoinCriterion = "children.{$ns->treeCol} = parents.{$ns->treeCol} AND ";
            else 
                $treeJoinCriterion = "";
            $this->allChildrenCountRelation = new Ac_Model_Relation(array(
                'srcTableName' => $this->mixin->tableName,
                'destTableName' => new Ac_Sql_Expression("(
                    SELECT parents.{$ns->idCol} AS parentId, COUNT(children.{$ns->idCol}) AS `count` 
                    FROM {$ns->tableName} AS parents 
                    INNER JOIN {$ns->tableName} AS children
                        ON {$treeJoinCriterion} children.{$ns->leftCol} > parents.{$ns->leftCol} 
                        AND children.{$ns->leftCol} < parents.{$ns->rightCol}
                    WHERE  ".$this->nsTreeCriterion("parents")."
                    GROUP BY parentId) AS nsc"),
                'fieldLinks' => array(
                    'nodeId' => 'parentId',
                ),
                'srcVarName' => 'allChildNodesCount',
                'srcIsUnique' => true,
                'destIsUnique' => true,
                'database' => $this->getDb(),
            ));
        }
        return $this->allChildrenCountRelation;
    }
    
    /**
     * @return Ac_Model_Relation
     */
    function getNodeChildIdsRelation() {
        if ($this->childIdsRelation === false) {
            $ns = $this->getNestedSets();
        	$this->childIdsRelation = new Ac_Model_Relation(array(
                'srcTableName' => $this->mixin->tableName,
                'destTableName' => $ns->tableName,
                'fieldLinks' => array(
                    'nodeId' => $ns->parentCol,
                ),
                'srcVarName' => 'childNodeIds',
                'srcIsUnique' => true,
                'destIsUnique' => false,
                'database' => $this->getDb(),
                'destWhere' => $this->nsTreeCriterion(false, false),
                'destOrdering' => $ns->leftCol,
            ));
        }
        return $this->childIdsRelation;
    }
    
    /**
     * @return Ac_Model_Relation
     */
    function getNodeContainersRelation() {
        if ($this->containersRelation === false) {
            $ns = $this->getNestedSets();
            $this->containersRelation = new Ac_Model_Relation(array(
                'srcTableName' => $ns->tableName,
                'destMapper' => $this->mixin,
                'fieldLinks' => array(
                    'nodeId' => $this->mixin->pk,
                ),
                'srcVarName' => 'container',
                'srcIsUnique' => true,
                'destIsUnique' => true,
                'database' => $this->getDb(),
                'srcWhere' => $this->nsTreeCriterion(false, false)
            ));
        }
        return $this->containersRelation;
    }
    
    /**
     * @return Ac_I_Tree_Provider
     */
    function getDefaultTreeProvider() {
        if ($this->treeProvider === false) {
            $this->treeProvider = $this->createTreeProvider();
        }
        return $this->treeProvider;
    }

    /**
     * @return Ac_I_Tree_Provider
     */
    function createTreeProvider() {
    	return new Ac_Model_Tree_Provider($this->mixin);
    }

    protected function getOrderingValuesColumns() {
        if (Ac_Accessor::methodExists($this->mixin, 'getOrderingValuesColumns'))
            $res = $this->mixin->getOrderingValuesColumns();
        else 
            $res = 't.'.$this->mixin->getTitleFieldName();
        return $res;
    }
    
    protected function getOrderingValuesLabel(array $entry) {
        if (Ac_Accessor::methodExists($this->mixin, 'getOrderingValuesLabel'))
            $res = $this->mixin->getOrderingValuesLabel($entry);
        else 
            $res = implode(' - ', $entry);
        return $res;
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
        $idCol = $this->mixin->pk;
        $nIdCol = $this->getDb()->n($idCol);
        if (strlen($pId)) {
            $ords = $this->getDb()->fetchArray($sql = "
                SELECT ns.ordering, ".$this->getOrderingValuesColumns().", t.{$nIdCol} 
                FROM {$this->mixin->tableName} AS t 
                INNER JOIN {$ns->tableName} AS ns ON t.{$nIdCol} = ns.{$ns->idCol} 
                WHERE ".$this->nsTreeCriterion('ns')." 
                AND ns.{$ns->parentCol} = ".$this->getDb()->q($pId)." 
                ORDER BY ns.ordering ASC
            ");
            foreach ($ords as $ord) {
                $lbl = $this->getOrderingValuesLabel($ord);
                if ($ord[$idCol] == $modelObject->$idCol) {
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
        return new Ac_Model_Tree_NestedSetsImpl(array(
            'container' => $modelObject,
            'mapper' => $this->mixin,
        ));        
    }
    
    function fixTree($dontApply = false, $extraColumns = '') {
        
        $treeTableName = $this->getNestedSets()->tableName;
        
        if (!strlen($this->getNestedSets()->treeCol)) 
            throw new Exception("fixTree() without \$treeCol isn't supported yet");
        
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
				 LEFT JOIN {$this->mixin->tableName} c
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
        $treeTableName = $ns->tableName;
        $myTableName = $this->mixin->tableName;
        
        if ($treeTableName == $myTableName) 
            throw new Exception ("findProblems() isn't supported yet "
                . "when NS table is the same as data table");
    
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
    
    protected function listNonMixedProperties() {
        return array_merge(parent::listNonMixedProperties(), array(
            'nsTreeId', 'nsIdCol', 'nsTableName', 'addMixaleToRecords', 'nsPrototype', 'hasPublicVars'
        ));
    }
        
    function onAfterCreateRecord(Ac_Model_Object $record) {
        if ($this->addMixableToRecords) {
            
        }
        if (!$record->listMixables('Ac_Model_Tree_Object')) {
            $record->addMixable(new Ac_Model_Tree_Object);
        }
    }
    
}
<?php

/**
 * 
 */
class Ac_Model_Tree_ComboMapper extends Ac_Model_Tree_AdjacencyListMapper {   

    var $leftCol = 'leftCol';
    var $rightCol = 'rightCol';
    var $ignoreCol = 'ignore';
    var $levelCol = 'depth';
    
    /**
     * @var Ac_Sql_Nested_Sets
     */
    protected $nestedSets = false;
    
    protected $isCreatingRootNode = false;
    
    protected $allChildrenCountRelation = false;
    
    protected $allChildIdsRelation = false;
    
    /**
     * @return Ac_Sql_Nested_Sets
     */
    function getNestedSets() {
        if ($this->nestedSets === false) {
            $this->nestedSets = new Ac_Sql_NestedSets(array(
                'tableName' => $this->mixin->tableName,
                'leftCol' => $this->leftCol,
                'rightCol' => $this->rightCol,
                'idCol' => $this->mixin->pk,
                'ignoreCol' => $this->ignoreCol,
                'treeCol' => false,
                'parentCol' => $this->nodeParentField,
                'orderingCol' => $this->nodeOrderField,
                'db' => $this->mixin->getDb(),
                'blocker' => new Ac_Sql_Blocker(),
            ));
        }
        return $this->nestedSets;
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
    
    function getIsCreatingRootNode() {
        return $this->isCreatingRootNode;
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
        return 'Ac_Model_Tree_ComboImpl';
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
    
    protected function getStmtCacheDefaults() {
        $res = array_merge(parent::getStmtCacheDefaults(), array(
            'leftCol' => $this->leftCol,
            'rightCol' => $this->rightCol,
            'ignoreCol' => $this->ignoreCol,
            'levelCol' => $this->levelCol,
        ));
        return $res;
    }
    
    protected function getOriginalDataMapForSql() {
        $res = array_merge(parent::getOriginalDataMapForSql(), array(
            'leftCol' => '[[leftCol]]',
            'rightCol' => '[[rightCol]]',
        ));
        return $res;
    }
    
    function loadNodeChildrenCounts(array $nodes) {
        $rel = $this->getNodeChildrenCountRelation();
        return $rel->loadDest($nodes, false, true);        
    }
    
    function loadNodeAllChildrenCounts(array $nodes) {
        $rel = $this->getAllChildrenCountRelation();
        return $rel->loadDest($nodes, false, true);        
    }
    
    /**
     * @return Ac_Model_Relation
     */
    function getAllChildrenCountRelation() {
        if ($this->allChildrenCountRelation === false) {
            $pk = $this->mixin->pk;
            $tableName = $this->mixin->tableName;
            $this->allChildrenCountRelation = new Ac_Model_Relation(array(
                'srcTableName' => $tableName,
                'destTableName' => new Ac_Sql_Expression("
                    (
                        SELECT parents.{$pk} AS parentId, COUNT(children.{$pk}) AS `count` 
                        FROM {$tableName} AS parents 
                        INNER JOIN {$tableName} AS children
                            ON children.{$this->leftCol} > parents.{$this->leftCol} 
                            AND children.{$this->leftCol} < parents.{$this->rightCol}
                        GROUP BY parentId
                    ) AS nsc
                "),
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
    function getAllChildIdsRelation() {
        if ($this->allChildIdsRelation === false) {
            $pk = $this->mixin->pk;
            $tableName = $this->mixin->tableName;
            $this->allChildrenIdsRelation = new Ac_Model_Relation(array(
                'srcTableName' => $tableName,
                'destTableName' => new Ac_Sql_Expression("
                    (
                        SELECT parents.{$pk} AS parentId, children.{$pk} AS allChildIds
                        FROM {$tableName} AS parents 
                        INNER JOIN {$tableName} AS children
                            ON children.{$this->leftCol} > parents.{$this->leftCol} 
                            AND children.{$this->leftCol} < parents.{$this->rightCol}
                        GROUP BY parentId
                    ) AS nsc
                "),
                'fieldLinks' => array(
                    'nodeId' => 'parentId',
                ),
                'srcVarName' => 'allChildIds',
                'srcIsUnique' => true,
                'destIsUnique' => false,
                'database' => $this->getDb(),
            ));
        }
        return $this->allChildIdsRelation;
    }
    
    function loadChildIdsRecursive(array $ids) {
    	$res = array();
        $src = array();
        foreach ($ids as $id) {
            $src[] = array('nodeId' => $id);
        }
    	foreach ($ids as $id) 
    		$nodes2load[] = array('nodeId' => $id, 'allChildIds' => false);
        
    	$this->getNodeChildIdsRelation()->loadDest($nodes2load, true, false);
        
    	foreach ($nodes2load as $node) {
    		$childIds = array();
    		if (isset($node['childNodeIds']) && is_array($node['childNodeIds'])) 
    			foreach($node['childNodeIds'] as $nId) $childIds[] = $n['childId'];
    		if ($childIds) $childrenRecursive = $this->loadChildIdsRecursive($childIds);
    			else $childrenRecursive = array();
    		$res[$node['nodeId']] = $childrenRecursive;
    	}
    	return $res;
    }
    
    
}
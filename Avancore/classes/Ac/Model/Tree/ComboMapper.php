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
    
    var $rootNodePrototype = array();
    
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
                'levelCol' => $this->levelCol,
                'treeCol' => false,
                'parentCol' => $this->nodeParentField,
                'orderingCol' => $this->nodeOrderField,
                'db' => $this->mixin->getDb(),
                'blocker' => new Ac_Sql_Blocker(),
                'idIsAutoInc' => $this->mixin->getAutoincFieldName() == $this->mixin->pk,
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
                'application' => $this->mixin->getApplication(),
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
                'application' => $this->mixin->getApplication(),
                'srcTableName' => $tableName,
                'destTableName' => new Ac_Sql_Expression("
                    (
                        SELECT parents.{$pk} AS parentId, children.{$pk} AS childId
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
    	$items = $this->getNodeChildIdsRelation()->loadDest($src, true, false);
        foreach ($src as $srcK => $item) {
            /* 
             * make child ids' tree for the respective item
             */
            $res[$item['nodeId']] = array();
            if (is_array($item['allChildIds'])) {
                $arr = $item['allChildIds'];
                /*
                 * original structure:
                 * [ ['parentId' => id1, 'childId' => id1.1], ['parentId' => id1.1, 'childId' => id1.1.1]]
                 * target structure:
                 * [ id1 => [ id1.1 => [ id1.1.1 => [] ] ] ]
                 */
                $p2c = array();
                foreach ($arr as $k => $v) {
                    $p2c[$v['childId']] = array();
                }
                foreach ($arr as $k => $v) {
                    if (isset($p2c[$v['parentId']])) {
                        $p2c[$v['parentId']][$v['childId']] = & $p2c[$v['childId']];
                    } elseif ($v['parentId'] == $item['nodeId']) {
                        $res[$item['nodeId']] = & $p2c[$v['id']];
                    } else {
                        throw new Ac_E_Assertion("Invalid logic: child belongs neither to parent nor "
                            . "to the nodes of parent's tree");
                    }
                }
            } else {
                $res[$item['nodeId']] = array();
            }
        }
    	return $res;
    }
    
    function getNodePath($id) {
        $sql = $this->stmtCache->getStatement(
            'SELECT parent.[[pk]] FROM [[table]] parent INNER JOIN [[table]] child
                ON parent.[[leftCol]] < child.[[leftCol]] AND parent.[[rightCol]] > child.[[rightCol]]
                WHERE child.[[pk]] = {{id}}
                ORDER BY parent.[[leftCol]] 
            ', array('id' => $id));
        $res = $this->getDb()->fetchColumn($sql, 0, null);
        return $res;
    }
    
    function onBeforeStoreRecord(Ac_Model_Object $record, $hyData, & $exists, & $error, & $newData) {
        // Nested sets' fields are managed by $this->nestedSets object
        unset($hyData[$this->leftCol]);
        unset($hyData[$this->rightCol]);
    }
    
    function reorderNode($id, $oldParentId, $oldOrdering, $newParentId, $newOrdering, $ignoreTheNode = false) {
        $ns = $this->getNestedSets();
        $res = $ns->moveNode($id, $newParentId, $newOrdering);
        return $res;
    }
    
    function placeNewNode($id, $parentId, $ordering, $ignoreTheNode = false) {
        $ns = $this->getNestedSets();
        $res = $ns->addNode($parentId, $ordering, array($this->mixin->pk => $id), true) !== false;
        return $res;
    }
    
    function removeNode($id, $parentId, $ordering) {
        $res = $this->getNestedSets()->deleteNode($id);
        return $res;
    }
    
}
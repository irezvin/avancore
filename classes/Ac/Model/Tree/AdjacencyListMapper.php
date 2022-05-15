<?php

/**
 * 
 */
class Ac_Model_Tree_AdjacencyListMapper extends Ac_Mixable {
	
    protected $mixableId = 'treeMapper';
    
    protected $mixinClass = 'Ac_Model_Mapper';
    
    /**
     * @var Ac_Model_Mapper
     */
    protected $mixin = false;
	
    protected $rootNodeId = false;
    
    protected $childrenCountRelation = false;
    
    protected $childIdsRelation = false;
    
    protected $containersRelation = false;
    
    protected $treeProvider = false;
    
    /**
     * @var Ac_Sql_Db_Ae
     */
    protected $sqlDb = false;
    
    /**
     * @var Ac_Sql_Statement_Cache
     */
    protected $stmtCache = false;

    protected $defaultParentValue = false;
    
    var $nodeParentField = 'parentId';
    
    var $nodeOrderField = 'ordering';
    
    function registerMixin(Ac_I_Mixin $mixin) {

        parent::registerMixin($mixin);
        
        if ($mixin instanceof Ac_Model_Mapper) {
            $this->stmtCache = new Ac_Sql_Statement_Cache(array(
                'defaults' => $this->getStmtCacheDefaults(),
            ));
            if ($this->defaultParentValue === false) {
                if (in_array($this->nodeParentField, $mixin->listNullableColumns())) {
                    $this->defaultParentValue = null;
                } else {
                    $this->defaultParentValue = 0;
                }
            }
        }
    }

    protected function getStmtCacheDefaults() {
        return array(
            'table' => $this->mixin->tableName,
            'pk' => $this->mixin->pk,
            'nodeParent' => $this->nodeParentField,
            'nodeOrder' => $this->nodeOrderField,
        );
    }
    
    function setDefaultParentValue($defaultParentValue) {
        $this->defaultParentValue = $defaultParentValue;
    }

    function getDefaultParentValue() {
        return $this->defaultParentValue;
    }    
    
    function hasPublicVars() {
        return true;
    }
    
    /**
     * @return Ac_Sql_Db
     */
    protected function getDb() {
        return $this->mixin->getDb();
    }
    
    function listTopNodes() {
        $stmt = $this->stmtCache->getStatement('SELECT [[pk]] FROM [[table]] WHERE '.$this->getDb()->getIfnullFunction().'([[nodeParent]], 0) = 0 ORDER BY [[nodeOrder]]');
        $res = $this->getDb()->fetchColumn($stmt);
        Ac_Debug_Log::l("topNodes are", $res);
        return $res; 
    }
    
    function getNodeClass() {
        return 'Ac_Model_Tree_AdjacencyListImpl';
    }
    
    function loadOriginalDataForNode(Ac_Model_Tree_AdjacencyListImpl $node) {
        $nid = $node->getNodeId();
        Ac_Debug_Log::l("Mapper: loading original data for node $nid", gettype($nid));
        $res = false;
        if ($nid !== false) {
            $od = array_values($this->loadOriginalData(array($nid)));
            if (count($od)) {
                $node->setOriginalData($od[0]);
                $res = true;
            }
        }
        return $res;
    }
    
    protected function getOriginalDataMapForSql() {
        return array(
            'nodeId' => '[[pk]]',
            'parentId' => '[[nodeParent]]',
            'ordering' => '[[nodeOrder]]',
        );
    }
    
    function loadOriginalData(array $nodeIds) {
        $arrColumns = array();
        foreach ($this->getOriginalDataMapForSql() as $alias => $sqlColumn) {
            $arrColumns[] = "$sqlColumn AS $alias";
        }
        
        // [[pk]] AS nodeId, [[nodeParent]] AS parentId, [[nodeOrder]] AS ordering
        $strColumns = implode(", ", $arrColumns);
        
    	$stmt = $this->stmtCache->getStatement("
            SELECT {$strColumns}
            FROM [[table]] WHERE [[pk]] IN ({{ids}})
    	", array('ids' => $nodeIds));
        
    	$res = $this->getDb()->fetchArray($stmt, 'nodeId');
    	return $res;
    }
    
    function loadNodes(array $ids) {
    	
        $res = array();
        $c = $this->getNodeClass();
        foreach ($this->loadOriginalData($ids) as $id => $node) {
        	$prot = array(
        		'mapper' => $this->mixin,
        	    'originalData' => $node
            );
            $objNode = new $c($prot);
            $res[$id] = $objNode;
        }
        return $res;
    }
    
    function loadNodeChildrenCounts(array $nodes) {
        $rel = $this->getNodeChildrenCountRelation();
        return $rel->loadDest($nodes, false, true);        
    }
    
    function loadNodeAllChildrenCounts(array $nodes) {
    	$ids = array();
    	$nodesByIds = array();
    	foreach ($nodes as $node) {
    		$nodeId = $node->getNodeId();
    		$nodesByIds[$nodeId] = $node;
    		$ids[] = $nodeId; 
    	}
    	foreach (($childIdsRecursive = $this->loadChildIdsRecursive($ids)) as $nodeId => $childIds) {
    		$f = Ac_Util::flattenArray($childIds);
    		$nodesByIds[$nodeId]->setAllChildNodesCount(count($f));
    	}
    	return $childIdsRecursive;
    }
    
    function loadChildIdsRecursive(array $ids) {
    	$res = array();
    	$nodes2load = array();
    	foreach ($ids as $id) 
    		$nodes2load[] = array('nodeId' => $id);
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
    
    function loadNodeChildIds(array $nodes) {
        $rel = $this->getNodeChildIdsRelation();
        $n = Ac_Util::flattenArray($rel->loadDest($nodes, false, true), 1);
        $ids = array();
        foreach ($n as $i) $ids[] = $i['id'];
    	return $ids;
    }
    
    function loadNodeContainers(array $nodes) {
        $rel = $this->getNodeContainersRelation();
        return $rel->loadDest($nodes, false, true);
    }
    
    function getNodePath($id) {
        $sql = $this->stmtCache->getStatement('SELECT [[nodeParent]] FROM [[table]] WHERE [[pk]] = {{id}}', array('id' => $id));
        $parentId = $this->getDb()->fetchValue($sql, 0, null);
        if (!is_null($parentId)) $res = array_merge($this->getNodePath($parentId), array($parentId));
            else $res = array();
        return $res;
    }
    
    /**
     * @return Ac_Model_Relation
     */
    function getNodeChildrenCountRelation() {
        if ($this->childrenCountRelation === false) {
            $this->childrenCountRelation = new Ac_Model_Relation(array(
                'app' => $this->mixin->getApp(),
                'srcTableName' => $this->mixin->tableName,
                'destTableName' => new Ac_Sql_Expression("
                (
                    SELECT {$this->nodeParentField} AS parentId, COUNT(children.{$this->mixin->pk}) AS ".$this->getDb()->n('count')." 
                    FROM {$this->mixin->tableName} AS children 
                    WHERE ".$this->getDb()->getIfnullFunction()."({$this->nodeParentField}, 0) <> 0 
                    GROUP BY {$this->nodeParentField}
                )  AS childrenCount"),
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
    function getNodeChildIdsRelation() {
        if ($this->childIdsRelation === false) {
        	$this->childIdsRelation = new Ac_Model_Relation(array(
                'app' => $this->mixin->getApp(),
                'srcTableName' => $this->mixin->tableName,
                'destTableName' => new Ac_Sql_Expression("
                	( SELECT "
        				.$this->getDb()->n($this->nodeParentField)." AS id, "
        				.$this->getDb()->n($this->nodeOrderField)." AS ordering, "
        				.$this->getDb()->n($this->mixin->pk)." AS childId 
        			  FROM ".$this->getDb()->n($this->mixin->tableName)." 
        			  WHERE ".$this->getDb()->getIfnullFunction()."(".$this->getDb()->n($this->nodeParentField).", 0) <> 0)
                "),
                'fieldLinks' => array(
                    'nodeId' => 'id',
                ),
                'srcVarName' => 'childNodeIds',
                'srcIsUnique' => true,
                'destIsUnique' => false,
                'database' => $this->getDb(),
                //'destOrdering' => $this->getDb()->NameQuote($this->nodeOrderField),
                'destOrdering' => 'ordering',
            ));
        }
        return $this->childIdsRelation;
    }
    
    /**
     * @return Ac_Model_Relation
     */
    function getNodeContainersRelation() {
        if ($this->containersRelation === false) {
            $this->containersRelation = new Ac_Model_Relation(array(
                'app' => $this->mixin->getApp(),
                'srcTableName' => $this->mixin->tableName,
                'destMapperClass' => $this->mixin->getId(),
                'fieldLinks' => array(
                    'nodeId' => $this->mixin->pk,
                ),
                'srcVarName' => 'container',
                'srcIsUnique' => true,
                'destIsUnique' => true,
                'database' => $this->getDb(),
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
    
    function getNodeParentField() {
        return $this->nodeParentField;
    }

    function getNodeOrderField() {
        return $this->nodeOrderField;
    }

    protected function getOrderingValuesColumns() {
        return 't.'.$this->mixin->getTitleFieldName();
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
        
        if (!strlen($pId) || ((string) $pId == '0')) {
            $crit = $this->getDb()->getIfnullFunction()."(t.{$this->nodeParentField},0) = 0";
        } else {
            $crit = "t.{$this->nodeParentField} = ".$this->getDb()->q($pId);
        }
            
        $foundMyself = false;
        $ords = $this->getDb()->fetchArray($sql = "
            SELECT t.".$this->getDb()->n($this->nodeOrderField)." AS ordering, ".$this->getOrderingValuesColumns().", t.{$this->mixin->pk} 
            FROM {$this->mixin->tableName} AS t 
            WHERE {$crit} 
            ORDER BY t.".$this->getDb()->n($this->nodeOrderField)." ASC
        ");
        foreach ($ords as $ord) {
            $lbl = $this->getOrderingValuesLabel($ord);
            if ($ord[$this->mixin->pk] == $modelObject->{$this->mixin->pk}) {
                $lbl .= ' '.(new Ac_Lang_String('model_ordering_current', '(Current)'));
                $foundMyself = true;
            }
            $res[$ord['ordering']] = $lbl;   
        }
        if (!count($ords)) $res[' 0'] = new Ac_Lang_String('model_ordering_only', '(N/A)');
        elseif (!$foundMyself) {
            $res[Ac_Model_Tree_AbstractImpl::ORDER_LAST] = new Ac_Lang_String('model_ordering_last', '(Last)');
        }
        return $res;
    }
    
    function getLastOrdering($parentId) {
        if (is_null($parentId)) {
        $res = $this->getDb()->fetchValue($sql = $this->stmtCache->getStatement('
                SELECT MAX([[nodeOrder]])
                FROM [[table]] WHERE [[nodeParent]] IS NULL
        '));
        } else {
            $res = $this->getDb()->fetchValue($sql = $this->stmtCache->getStatement('
                    SELECT MAX([[nodeOrder]])
                    FROM [[table]] WHERE [[nodeParent]] = {{parentId}}
                ', array(
                    'parentId' => $parentId,
                )
            ));
        }
        return $res;
    }
    
    /**
     * @return Ac_I_Tree_Impl
     */
    function createTreeImpl(Ac_Model_Object $modelObject) {
        $nc = $this->getNodeClass();
        return new $nc(array(
            'container' => $modelObject,
            'mapper' => $this->mixin,
        ));
    }
    
    function reorderNode($id, $oldParentId, $oldOrdering, $newParentId, $newOrdering, $ignoreTheNode = false) {
        
        $db = $this->getDb();
        
        if ($oldParentId != $newParentId) {

            $oldCrit = is_null($oldParentId)? '[[nodeParent]] IS NULL' : '[[nodeParent]] = {{oldParentId}}';
            $newCrit = is_null($newParentId)? '[[nodeParent]] IS NULL' : '[[nodeParent]] = {{newParentId}}';
            $crit = '';
            
            if ($ignoreTheNode) $crit = " AND [[pk]] <> {{id}}";
            
            // move with parent change...

            $db->query($this->stmtCache->getStatement('
                    UPDATE [[table]] 
                    SET [[nodeOrder]] = [[nodeOrder]] + 1
                    WHERE '.$newCrit.$crit.' AND [[nodeOrder]] >= {{newOrdering}}
                ', array('newParentId' => $newParentId, 'newOrdering' => $newOrdering, 'id' => $id)
            ));

            $db->query($this->stmtCache->getStatement('
                    UPDATE [[table]] 
                    SET [[nodeOrder]] = [[nodeOrder]] - 1
                    WHERE '.$oldCrit.$crit.' AND [[nodeOrder]] >= {{oldOrdering}}
                ', array('oldParentId' => $oldParentId, 'oldOrdering' => $oldOrdering, 'id' => $id)
            ));

        } else {
            
            if ($newOrdering > $oldOrdering) {
                $rightOrder = $newOrdering;
                $leftOrder = $oldOrdering;
                $delta = '-1';
            } elseif ($newOrdering < $oldOrdering) {
                $rightOrder = $oldOrdering;
                $leftOrder = $newOrdering;
                $delta = '+ 1';
            } else {
                $delta = false;
            }
            
            if ($delta !== false) { 

                $crit = is_null($newParentId)? '[[nodeParent]] IS NULL' : '[[nodeParent]] = {{parentId}}';
                if ($ignoreTheNode) $crit .= " AND  [[pk]] <> {{id}}";

                $db->query($stmt = $this->stmtCache->getStatement('
                        UPDATE [[table]] 
                        SET [[nodeOrder]] = IF ([[pk]] = {{id}}, {{newOrdering}}, [[nodeOrder]] {{delta}})
                        WHERE ([[nodeOrder]] BETWEEN {{leftOrder}} AND {{rightOrder}}) AND '.$crit.'
                    ', array(
                        'parentId' => $newParentId, 
                        'leftOrder' => $leftOrder, 
                        'rightOrder' => $rightOrder, 
                        'newOrdering' => $newOrdering, 
                        'id' => $id, 
                        'delta' => new Ac_Sql_Expression($delta)
                    )
                ));
                
            }

        }
    }
    
    function placeNewNode($id, $parentId, $ordering, $ignoreTheNode = false) {
        $db = $this->getDb();
        $crit = is_null($parentId)? '[[nodeParent]] IS NULL' : '[[nodeParent]] = {{parentId}}';
        if ($ignoreTheNode) $crit .= " AND [[pk]] <> {{id}}";
        $res = $db->query($this->stmtCache->getStatement('
                UPDATE [[table]] 
                SET [[nodeOrder]] = [[nodeOrder]] + 1
                WHERE '.$crit.' AND [[nodeOrder]] >= {{ordering}}
            ', array('parentId' => $parentId, 'ordering' => $ordering, 'id' => $id)
        )) !== false;
        return $res;
    }
    
    function removeNode($id, $parentId, $ordering) {
        $db = $this->getDb();
        $crit = is_null($parentId)? '[[nodeParent]] IS NULL' : '[[nodeParent]] = {{parentId}}';
        $res = $db->query($this->stmtCache->getStatement('
                UPDATE [[table]] 
                SET [[nodeOrder]] = [[nodeOrder]] - 1
                WHERE '.$crit.' AND [[nodeOrder]] >= {{ordering}}
            ', array('parentId' => $parentId, 'ordering' => $ordering)
        )) !== false;
        return $res;
    }
    
    function onAfterCreateRecord(Ac_Model_Object $record) {
        if (!$record->listMixables('Ac_Model_Tree_Object')) {
            $record->addMixable(new Ac_Model_Tree_Object);
        }
    }
    
    protected function listNonMixedProperties() {
        return array_merge(parent::listNonMixedProperties(), array(
            'nodeParentField', 'nodeOrderField'
        ));
    }
    
    function onReset() {
        $this->rootNodeId = false;
    }

}
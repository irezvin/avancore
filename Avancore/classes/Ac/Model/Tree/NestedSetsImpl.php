<?php

class Ac_Model_Tree_NestedSetsImpl extends Ac_Model_Tree_AbstractImpl {
    
    const debugCallback = 'Ac_Tree_NestedSetsImpl::debugCallback';
    const debugBeginStore = 'beginStore';
    const debugBeginSelfStore = 'beginSelfStore';
    const debugEndSelfStore = 'endSelfStore';
    const debugEndStore = 'endStore';
    
    const debugBeginDelete = 'beginDelete';
    const debugBeginSelfDelete = 'beginSelfDelete';
    const debugEndSelfDelete = 'endSelfDelete';
    const debugEndDelete = 'endDelete';
    
    /**
     * @var Ac_Sql_NestedSets
     */
    protected $nestedSets = false;
    
    protected $treeNode = false;
    
    /**
     * @var Ac_Model_Tree_NestedSetsImpl
     */
    protected $tmpParent = false;
    
    function setMapper(Ac_I_Tree_Mapper $mapper) {
        $this->modelIdField = $mapper->pk;
        if ($this->nestedSets === false) $this->setNestedSets($mapper->getNestedSets());
        parent::setMapper($mapper);
    	if (!$mapper instanceof Ac_I_Tree_Mapper_NestedSets)
    		throw new Exception("\$mapper should implement 'Ac_I_Tree_Mapper_NestedSets', but '".get_class($mapper)."' doesn't do that");
    }
    
    protected function setNestedSets(Ac_Sql_NestedSets $nestedSets) {
        $this->nestedSets = $nestedSets;
    } 
    
    /**
     * @return Ac_Sql_NestedSets
     */
    function getNestedSets() {
        return $this->nestedSets;
    }
    
    protected function doGetInternalNodeId() {
        if (($tn = $this->treeNode)) $res = $this->treeNode[$this->nestedSets->idCol];
            else $res = null;
        return $res;        
    }
    
    protected function getTreeNode() {
        if ($this->treeNode === false) {
            if ($this->container && strlen($this->container->{$this->modelIdField})) {
                $this->treeNode = $this->nestedSets->getNode($this->container->{$this->modelIdField});
                if (!$this->treeNode) $this->treeNode = false;
            }
        }
        return $this->treeNode;
    }
    
    protected function getRootNodeId() {
        return $this->mapper->getRootNodeId();        
    }
    
    function setTreeNodeTitleGetter($treeNodeTitleGetter) {
        $this->treeNodeTitleGetter = $treeNodeTitleGetter;
    }
    
    function setParentNode(Ac_I_Tree_Node $parentNode = null) {
        if ($parentNode && !($parentNode instanceof Ac_Model_Tree_NestedSetsImpl))
        	throw new Exception("\$parentNode can be only Ac_Model_Tree_NestedSetsImpl instance, '".get_class($parentNode)."' given");
        parent::setParentNode($parentNode);
    }
    
    /**
     * @param Ac_Model_Tree_NestedSetsImpl $childNode
     */
    function notifyChildNodeAdded(Ac_I_Tree_Node $childNode) {
        if ($childNode && !($childNode instanceof Ac_Model_Tree_NestedSetsImpl))
        	throw new Exception("\$childNode can be only Ac_Model_Tree_NestedSetsImpl instance, '".get_class($childNode)."' given");
        parent::notifyChildNodeAdded($childNode);
    }

    protected function isParentOf($nodeId, $possibleChildId) {
        return $this->nestedSets->isParentOf($nodeId, $possibleChildId);
    }
    
    protected function doGetInternalOrdering() {
        if (($tn = $this->getTreeNode())) $res = $tn[$this->nestedSets->orderingCol];
            else $res = false;
        return $res;        
    }
    
    function store() {
        if ($this->lockStore !== 0) return true;
        $this->lockStore++;
        
        Ac_Callbacks::call(self::debugCallback, self::debugBeginStore, $this);
        
        // Clear tree node before store() since it might have been changed in the database
        $this->treeNode = false;
        
        $res = true;
        if ($this->hasToStoreContainer()) 
            if (!$this->container->store()) $res = false;
        
        Ac_Callbacks::call(self::debugCallback, self::debugBeginSelfStore, $this);
        
        $newParentId = $this->getParentIdIfChanged();
        $newOrdering = $this->getOrderingIfChanged(!!$newParentId);
        if ($res && (($newParentId !== false) || !is_null($newOrdering))) {
            if (($tn = $this->getTreeNode())) {
                $oldParentId = $tn[$this->nestedSets->parentCol];
                if ($newParentId === false) $newParentId = $oldParentId;
                if (is_null($newParentId)) $newParentId = $this->getRootNodeId();
                if (is_null($newOrdering)) $newOrdering = false;
                if ($this->nestedSets->moveNode($this->getNodeId(), $newParentId, $newOrdering, false, $actualNewOrdering)) {
                    if (($node = $this->treeProvider->getNode($oldParentId, false))) $node->notifyChildNodeRemoved($this);
                    $this->parentId = $newParentId;
                    $this->treeNode = false;
                    if (!is_null($actualNewOrdering)) $this->ordering = $actualNewOrdering;
                } else {
                    $res = false;
                }
            } else {
                $this->createNodeIfContainerPersists();
            }
        }
        
        Ac_Callbacks::call(self::debugCallback, self::debugEndSelfStore, $this, $res);
        
        if ($res) {
            foreach ($this->listChildrenToStore() as $i) {
                if (!$this->getChildNode($i)->store()) {
                    $res = false; 
                    break;
                }
            }
        }
        if ($this->tmpParent) $this->tmpParent->notifyChildNodeSaved($this);
        
        Ac_Callbacks::call(self::debugCallback, self::debugEndStore, $this, $res);
        
        $this->lockStore--;
        $this->ordering = null;
        return $res;
    }
    
    function delete() {
        if ($this->lockDelete != 0) return true;
        $res = true;
        $this->lockDelete++;
        
        Ac_Callbacks::call(self::debugCallback, self::debugBeginDelete, $this);

        $cnt = $this->getContainer();
        $tn = $this->getTreeNode();
        $newId = $this->getParentIdIfChanged();
        if ($tn) $oldId = $tn[$this->nestedSets->parentCol]; 
        if ($cnt && !$cnt->_isDeleted && !$cnt->delete()) $res = false;
        
        if ($res) {
            $this->treeProvider->loadNodes($l = $this->listChildNodes());
            $this->treeProvider->loadContainers($l);
            foreach ($l as $i) {
                if (!$this->getChildNode($i)->delete()) {
                    $res = false;
                    break;
                }
            }
        }
        if ($res) {
            Ac_Callbacks::call(self::debugCallback, self::debugBeginSelfDelete, $this);
            $nsId = $this->getNodeId();
            if (strlen($nsId) && !$this->nestedSets->deleteNode($nsId)) $res = false;
            Ac_Callbacks::call(self::debugCallback, self::debugEndSelfDelete, $this, $res);
        }
        if ($res) {
            if (($newId !== false) && ($newParent = $this->treeProvider->getNode($newId, false))) $newParent->notifyChildNodeRemoved($this);
            if (($oldId !== false) && ($oldParent = $this->treeProvider->getNode($oldId, false))) $oldParent->notifyChildNodeRemoved($this);
        }
        Ac_Callbacks::call(self::debugCallback, self::debugEndDelete, $this, $res);
        $this->lockDelete--;
        return $res;
    }
    
    /**
     * @return Ac_Model_Tree_NestedSetsImpl
     */
    function createChildNode(Ac_Model_Object $container = null) {
        $prototype = array(
            'mapper' => $this->mapper,
            'treeProvider' => $this->treeProvider,
            'treeNodeTitleGetter' => $this->treeNodeTitleGetter,
            'containerImplSetter' => $this->containerImplSetter,
            'modelIdField' => $this->modelIdField,
        );
        
        if ($container) $prototype['container'] = $container;
        $this->doOnCreateChildNodePrototype($prototype, $container);
        $c = get_class($this);
        $res = new $c($prototype);
        $res->setParentNode($this);
        return $res;
    }
    
    protected function doOnCreateChildNodePrototype(array $prototype, $container) {
        $prototype['nestedSets'] = $this->nestedSets;
    }
    
    protected function createNodeIfContainerPersists() {
        $res = false;
        if (!$this->getTreeNode() && $this->container && strlen($cntId = $this->container->{$this->modelIdField})) {
            $parentId = $this->getParentIdIfChanged();
            if (is_null($parentId)) $parentId = $this->getRootNodeId();
            if (strlen($parentId)) {
                $res = $this->nestedSets->addNode($parentId, false, array($this->nestedSets->idCol => $cntId));
            }
            $this->treeNode = false;
            $this->treeProvider->registerNodes(array($this));
        }
        return $res;
    }
    
    function notifyParentNodeSaved() {
        if (($nsId = $this->tmpParent->getNodeId())) {
            if (($tn = $this->getTreeNode())) {
                if ((string) $tn[$this->nestedSets->parentCol] !== (string) $nsId) {
                    $this->nestedSets->moveNode($tn[$this->nestedSets->idCol], $nsId);
                    $this->treeNode = false;
                }
            } else {
                $this->createNodeIfContainerPersists();
            }
            $this->tmpParent = false;
            $this->parentId = $nsId;
        }
    }
    
    protected function doGetPathIds($nodeId) {
        $path = $this->nestedSets->getPath($id, true);
        if ($path) {
            $ids = array_keys($path);
            $ids = array_diff($ids, array($this->getRootNodeId())); 
        } else $ids = array();
        return $ids;
    }
    
    function __sleep() {
        return array_diff(array_keys(get_object_vars($this)), array('nestedSets'));
    }
    
    function __wakeup() {
        if ($this->mapper) $this->nestedSets = $this->mapper->getNestedSets();
    }
    
    protected function doOnDestroy() {
        $this->nestedSets = null;
    }

    protected function doExtractChildNodeId($idData) {
        if (!is_array($idData) || !isset($idData[$idc = $this->nestedSets->idCol]))
            throw new Exception("Only arrays with key '{$idc}' are allowed as \$childNodeIds");
        $res = $idData[$idc];
        return $res;
    }
    
    /**
     * @return Ac_Model_Tree_NestedSetsImpl
     */
    function getChildNode($id) {
        return parent::getChildNode($id);
    }
    
    protected function doBeforeContainerSave() {
    }
    
    protected function doAfterContainerSave() {
        return $this->store();
    }
    
    protected function doBeforeContainerDelete() {
        
    }
    
    protected function doAfterContainerDelete() {
        return $this->delete();
    }
    
    protected function getParentIdFromDb() {
        if ($tn = $this->getTreeNode()) {
            $res = $tn[$this->nestedSets->parentCol];
            if ($res == $this->getRootNodeId()) $res = null;
        } else {
            //$res = $this->getRootNodeId();
            $res = null;
        }
        return $res;
    }

    protected function doGetInternalParentId() {
        if (($n = $this->getTreeNode())) {
            $res = $n[$this->nestedSets->parentCol];
            if ($res === $this->getRootNodeId()) $res = null;
        } else {
            $res = false;
        }
        return $res;
    }
    
    function isPersistent() {
        return (bool) $this->getTreeNode();
    }
    
    function notifyMapperUpdated() {
        $this->treeNode = false;
    }
    
    function afterContainerLoad() {
        $this->treeNode = false;
    }
    
    
}
<?php

class Ac_Model_Tree_AdjacencyListImpl extends Ac_Model_Tree_AbstractImpl {

    protected $parentField = 'parentId';
    
    protected $orderingField = 'ordering';
    
    protected $origNodeId = false;
    
    protected $origOrdering = false;
    
    protected $origParentId = false;
    
    /**
     * @var Ac_I_Tree_Mapper_AdjacencyList
     */
    protected $mapper = false;
    
    protected $requiredMapperClass = 'Ac_I_Tree_Mapper_AdjacencyList';
    
    /**
     * @var Ac_Model_Tree_AdjacencyListImpl
     */
    protected $tmpParent = false;
    
    protected $defaultParentValue = false;
    
    protected $defaultOrderValue = self::ORDER_LAST;
    
    protected $lockParentId = 0;
    
    protected $lockOrdering = 0;
    
    /**
     * Is used *only* during store() phase
     */
    protected $newParentId = false;
    
    /**
     * Is used *only* during store() phase
     */
    protected $newOrdering = false;
    
    protected $wasNotPersistent = false;
    
    function isPersistent() {
        if ($this->container) $res = $this->container->isPersistent();
        else $res = $this->origNodeId !== false;
        return $res;
    }

    function getOrigMap() {
        return array(
            'nodeId' => 'origNodeId',
            'ordering' => 'origOrdering',
            'parentId' => 'origParentId',
        );  
    }
    
    function getFieldMapToContainer() {
        return array(
            'nodeId' => $this->nodeIdField,
            'ordering' => $this->orderingField,
            'parentId' => $this->parentField,
        );
    }
    
    function setOriginalData($origData) {
        if (!is_array($origData)) $origData = array();
        foreach ($this->getOrigMap() as $k => $p) {
            $this->$p = array_key_exists($k, $origData)? $origData[$k] : false;
        }
    }
    
    function loadOriginalData($dontReload) {
        if ($dontReload && $this->isPersistent()) return true;
        if ($this->mapper) $res = $this->mapper->loadOriginalDataForNode($this);
            else $res = false;
        return $res;
    }
    
    function getNodeId() {
        if ($this->container) 
            $res = $this->container->{$this->modelIdField};
        else $res = $this->doGetInternalNodeId();
        return $res;
    }
    
    protected function doGetInternalNodeId() {
        if (strlen($this->origNodeId)) $res = $this->origNodeId;
            else $res = false;
        return $res;
    }
    
    protected function doGetInternalParentId() {
        if (!$this->container->isPersistent()) $res = $this->origParentId;
        else {
            if ($this->loadOriginalData(true)) {
                $res = $this->origParentId;
            } else {
                $res = false;
            }
        }
        return $res;
    }
    
    protected function doGetInternalOrdering() {
        if ($this->loadOriginalData(true)) {
            $res = $this->origOrdering;
        } else {
            $res = false;
        }
        return $res;        
    }
    
    protected function isParentOf($nodeId, $possibleChildId) {
        return in_array($nodeId, $this->mapper->getNodePath($possibleChildId));
    }
    
    protected function doGetPathIds($nodeId) {
        $this->mapper->getNodePath($nodeId);
    }

    protected function doExtractChildNodeId($idData) {
        if (!is_array($idData) || !isset($idData['childId']))
            throw new Exception("Only arrays with key 'childId' are allowed as \$childNodeIds");
        $res = $idData['childId'];
        return $res;
    }
    
    function setMapper(Ac_I_Tree_Mapper $mapper) {
        if (!$mapper instanceof $this->requiredMapperClass) 
            throw new Exception("\$mapper must be an instance of '{$this->requiredMapperClass}'");
        $this->modelIdField = $mapper->pk;
        $this->orderingField = $mapper->getNodeOrderField();
        $this->parentField = $mapper->getNodeParentField();
        $this->defaultParentValue = $mapper->getDefaultParentValue();
        parent::setMapper($mapper);
    }
    
    function setContainer(Ac_Model_Object $container = null) {
        parent::setContainer($container);
        $this->updateFromContainer();
        
        // apply defaults to the container
        if (!$this->container->isPersistent()) { 
            $this->setOrdering($this->defaultOrderValue);
            $this->setParentNodeId($this->defaultParentValue);
        }
    }
    
    protected function updateContainer() {
        if ($c = $this->getContainer()) {
            $this->container->{$this->orderingField} = $this->getOrdering();
            $this->container->{$this->parentField} = $this->getParentNodeId();
        }
    }
    
    protected function updateFromContainer() {
        $this->origNodeId = $this->container->{$this->modelIdField};
        $this->origOrdering = $this->container->{$this->orderingField};
        $this->origParentId = $this->container->{$this->parentField};
        $this->parentId = false;
        $this->ordering = null;
        $this->resetTreePositionChange();
        $this->tmpParent = false;
    }
    
    function hasToStoreContainer() {
        $this->updateContainer();
        $res = parent::hasToStoreContainer();
        return $res;
    }

    function hasToStoreParent() {
        return $this->tmpParent && ($this->tmpParent->hasToStoreContainer() || $this->tmpParent->hasToStoreParent());
    }
    
    protected function shouldReorder($parentIdIfChanged, $orderIfChanged) {
        return strlen($parentIdIfChanged) || strlen($orderIfChanged);
    }
    
    function store() {
        if ($this->lockStore !== 0) return true;
        
        $this->lockStore++;
        
        $res = $this->beginStore();
        if ($res && $this->hasToStoreContainer()) 
            if (!$this->container->store()) $res = false;
        $this->endStore($res);
        
        $this->lockStore--;
        
        return $res;
    }
    
    protected function getUpdatedOrdering($parentId, $ordering, $isParentIdChanged) {
        
        $res = $ordering;
        
        $maxO = $this->mapper->getLastOrdering($parentId);
        if (!$maxO) $maxO = 0;
        
        //when our node is introducted to a new parent, 
        //new ordering in new parent is greater than current max ordering by one
        elseif ($maxO && $isParentIdChanged) $maxO += 1; 
        
        if ($res == self::ORDER_LAST || $res > $maxO) $res = $maxO;
        if ($res < 1) $res = 1;

        return $res;
    }
    
    protected function checkAndEnqueueTreePositionChange() {
        
        $newParentId = $this->container->getField($this->parentField);
        $newOrdering = $this->container->getField($this->orderingField);
        
        if (!strlen($newParentId)) $newParentId = $this->defaultParentValue;
        
        $isParentIdChanged = $newParentId != $this->origParentId;
        
        if (!$this->isPersistent()) {
            // always enqueue for non-persistent items
            $isParentIdChanged = true;
            $this->newParentId = $newParentId;
            $this->newOrdering = $this->getUpdatedOrdering($newParentId, $newOrdering, true);
            $this->wasNotPersistent = true;
            
        } else {
            
            // is perisistent: check if parent changed,,,
            if ($isParentIdChanged) {
                $this->newParentId = $newParentId;
                // always update ordering too
                $this->newOrdering = $this->getUpdatedOrdering($newParentId, $newOrdering, true);
            } else {
                // otherwise update ordering only if it was changed
                if ($newOrdering != $this->origOrdering) {
                    // if index of last item was increased, it will put the changes back into bounds
                    $newOrdering = $this->getUpdatedOrdering($newParentId, $newOrdering, false);
                    // still different value?
                    if ($newOrdering != $this->origOrdering) 
                        $this->newOrdering = $newOrdering;
                }
            }
            
        }
        
        // update the model if there were changes
        
        if ($this->newParentId !== false) {
            $this->lockParentId++;
            $this->container->{$this->parentField} = $this->newParentId;
            $this->lockParentId--;
        }
        if ($this->newOrdering !== false) {
            $this->lockOrdering++;
            $this->container->{$this->orderingField} = $this->newOrdering;
            $this->lockOrdering--;
        }
        
    }
    
    protected function isTreePositionChangeEnqueued() {
        return $this->newParentId !== false || $this->newOrdering !== false;
    }
    
    protected function applyTreePositionChange() {
        $res = true;
        $id = $this->getNodeId();
        $ignoreTheNode = true;
        if ($this->getNodeId()) {
            $newParentId = $this->newParentId !== false? $this->newParentId : $this->origParentId;
            $newOrdering = $this->newOrdering !== false? $this->newOrdering : $this->origOrdering;
            if ($this->wasNotPersistent) {
                $this->mapper->placeNewNode($id, $newParentId, $newOrdering, $ignoreTheNode);
            } else {
                $this->mapper->reorderNode(
                    $id, $this->origParentId, $this->origOrdering, 
                    $newParentId, $newOrdering, $ignoreTheNode);
            }
        }
        return $res;
    }
    
    protected function resetTreePositionChange() {
        $this->newParentId = false;
        $this->newOrdering = false;
        $this->wasNotPersistent = false;
    }
    
    protected function beginStore() {
        
        $res = true;
        
        $oldParentId = $this->origParentId;
        
        if ($this->hasToStoreParent() && !$this->tmpParent->store()) $res = false;
        else {
            if ($this->hasToStoreContainer()) 
                if (!$this->container->store()) $res = false;
        }
        
        if ($oldParentId != $this->origParentId) {
            if ($oldParent = $this->getTreeProvider()->getNode()) 
                $oldParent->notifyChildNodeRemoved($this);
        }
        
        $this->resetTreePositionChange();
        
        if ($res) $this->checkAndEnqueueTreePositionChange();
        
        return $res;

    }
    
    protected function endStore($wasOk) {
        $res = $wasOk;
        
        if ($this->isTreePositionChangeEnqueued()) {
            if (!$this->applyTreePositionChange()) $res = false;
        }
        
        $this->resetTreePositionChange();
        
        foreach ($this->listChildrenToStore() as $i) {
            if (!$this->getChildNode($i)->store()) {
                $res = false; 
                break;
            }
        }
        
        if ($res && $this->tmpParent) $this->tmpParent->notifyChildNodeSaved($this);
        
        return $res;
        
    }
        
    function notifyParentNodeSaved() {
        if ($nsId = $this->tmpParent->getNodeId()) {
            $this->parentId = $nsId;
        }
    }
    
    function delete() {
        $res = true;
        if ($this->lockDelete != 0) return true;
        $res = true;
        $this->lockDelete++;
        $cnt = $this->getContainer();
        $newId = $this->getParentIdIfChanged();
        $oldId = $this->origParentId;
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
        
        if ($res) $this->coreDelete();
        
        if (($newId !== false) && ($newParent = $this->treeProvider->getNode($newId, false))) $newParent->notifyChildNodeRemoved($this);
        if (($oldId !== false) && ($oldParent = $this->treeProvider->getNode($oldId, false))) $oldParent->notifyChildNodeRemoved($this);
        $this->lockDelete--;
        return $res;
    }
    
    protected function doBeforeContainerSave() {
        if (!$this->lockStore) $this->beginStore();
    }
    
    protected function doAfterContainerSave() {
        // update PK if non-persistent container was saved
        if (!$this->isPersistent()) $this->origNodeId = $this->container->getPrimaryKey();
        
        if (!$this->lockStore) $this->endStore(true);
        
        $this->updateFromContainer();
    }
    
    protected function doOnContainerSaveFailed() {
        if (!$this->lockStore) $this->endStore(false);
    }
    
    protected function doBeforeContainerDelete() {
    }
    
    protected function doAfterContainerDelete() {
        return $this->delete();
    }
    
    function afterContainerLoad() {
        $this->updateFromContainer();
    }

    function setDefaultParentValue($defaultParentValue) {
        $this->defaultParentValue = $defaultParentValue;
    }

    function getDefaultParentValue() {
        return $this->defaultParentValue;
    }

    protected function coreDelete() {
        $this->mapper->removeNode($this->getNodeId(), $this->origParentId, $this->origOrdering);
    }
    
    function setParentNodeId($parentId) {
        if (!$this->lockParentId) {
            $this->lockParentId++;
            $c = $this->getContainer();
            parent::setParentNodeId($parentId);
            $c->setField($this->parentField, $parentId);
            $this->lockParentId--;
        }
    }
    
    function setOrdering($ordering) {
        if (!$this->lockOrdering) {
            $this->lockOrdering++;
            $c = $this->getContainer();
            parent::setOrdering($ordering);
            $c->setField($this->orderingField, $ordering);
            $this->lockOrdering--;
        }
    }
    
}
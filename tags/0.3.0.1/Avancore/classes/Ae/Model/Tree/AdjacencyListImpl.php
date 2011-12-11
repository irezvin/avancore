<?php

class Ae_Tree_AdjacencyListImpl extends Ae_Tree_AbstractImpl {

    protected $parentField = 'parentId';
    
    protected $orderField = 'ordering';
    
    protected $origNodeId = false;
    
    protected $origOrdering = false;
    
    protected $origParentId = false;
    
    /**
     * @var Ae_Tree_AdjacencyListMapper
     */
    protected $mapper = false;
    
    /**
     * @var Ae_Tree_AdjacencyListImpl
     */
    protected $tmpParent = false;
    
    function hasOriginalData() {
        return $this->origNodeId !== false;
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
            'ordering' => $this->orderField,
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
        if ($dontReload && $this->hasOriginalData()) return true;
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
        if ($this->loadOriginalData(true)) {
            $res = $this->origParentId;
        } else {
            $res = false;
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
    
    function setMapper(Ae_I_Tree_Mapper $mapper) {
        if (!$mapper instanceof Ae_I_Tree_Mapper_AdjacencyList) throw new Exception("\$mapper must be an instance of Ae_I_Tree_Mapper");
        $this->modelIdField = $mapper->pk;
        $this->orderField = $mapper->getNodeOrderField();
        $this->parentField = $mapper->getNodeParentField();
        parent::setMapper($mapper);
    }
    
    protected function updateContainer() {
        if ($c = $this->getContainer()) {
            $this->container->{$this->orderField} = $this->getOrdering();
            $this->container->{$this->parentField} = $this->getParentNodeId();
        }
    }
    
    protected function updateFromContainer() {
        $this->origNodeId = $this->container->{$this->modelIdField};
        $this->origOrdering = $this->container->{$this->orderField};
        $this->origParentId = $this->container->{$this->parentField};
        $this->origNodeId = false;
        $this->parentId = false;
        $this->ordering = false;
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
    
    function store() {
        $res = false;
        if ($this->lockStore !== 0) return true;
        $this->lockStore++;
        $res = true;
        $oldParentId = $this->origParentId;
        if ($this->hasToStoreParent() && !$this->tmpParent->store()) $res = false;
        else {
            if ($this->hasToStoreContainer()) 
                if (!$this->container->store()) $res = false;
        }
        if ($oldParentId != $this->origParentId) {
            if ($oldParent = $this->getTreeProvider()->getNode()) $oldParent->notifyChildNodeRemoved($this);
        }
        
        $pid = $this->getParentIdIfChanged(); 
        $ord = $this->getOrderingIfChanged($pid !== false);
        if (strlen($pid) || strlen($ord)) {
            $origPid = $this->origParentId;
            if ($origPid === false) $origPid = $this->parentId;
            
            $origOrd = $this->origOrdering;
            if ($origOrd === false) $origOrd = $this->ordering;
            $this->mapper->reorderNode($origPid, $origOrd, $this->parentId, $this->ordering);
        }
        
        if ($res) {
            foreach ($this->listChildrenToStore() as $i) {
                if (!$this->getChildNode($i)->store()) {
                    $res = false; 
                    break;
                }
            }
        }
        if ($this->tmpParent) $this->tmpParent->notifyChildNodeSaved($this);
        $this->lockStore--;
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
        if (($newId !== false) && ($newParent = $this->treeProvider->getNode($newId, false))) $newParent->notifyChildNodeRemoved($this);
        if (($oldId !== false) && ($oldParent = $this->treeProvider->getNode($oldId, false))) $oldParent->notifyChildNodeRemoved($this);
        $this->lockDelete--;
        return $res;
    }
    
    
    protected function doBeforeContainerSave() {
        $this->store();
    }
    
    protected function doAfterContainerSave() {
        $this->updateFromContainer();
    }
    
    protected function doBeforeContainerDelete() {
        
    }
    
    protected function doAfterContainerDelete() {
        return $this->delete();
    }
    
}

<?php

class Ac_Model_Tree_ComboImpl extends Ac_Model_Tree_AdjacencyListImpl {

    protected $origLeftCol = false;
    protected $origRightCol = false;
    protected $origLevelCol = false;
    
    protected $requiredMapperClass = 'Ac_I_Tree_Mapper_Combo';
    
    protected function coreDelete() {
        $ns = $this->getContainer()->getMapper()->getNestedSets();
        $ns->deleteNode($this->getContainer()->getPrimaryKey());
    }
    
    protected function shouldReorder($parentIdIfChanged, $orderIfChanged) {
        return $parentIdIfChanged !== false || !is_null($orderIfChanged);
    }
    
    protected function updateFromContainer() {
        parent::updateFromContainer();
        if ($this->container->isPersistent()) {
            $this->origLeftCol = $this->container->{$this->mapper->leftCol};
            $this->origRightCol = $this->container->{$this->mapper->rightCol};
            if (strlen($lc = $this->mapper->levelCol)) {
                $this->origLevelCol = $this->container->$lc;
            }
        } else {
            $this->origLeftCol = false;
            $this->origRightCol = false;
            $this->origLevelCol = false;
        }
    }
    
    protected function beginStore() {
        $res = parent::beginStore();
        if ($res && !$this->isPersistent() && $this->container) {
            
            // restore left, right, level values if they were altered
            $this->container->{$this->mapper->leftCol} = $this->origLeftCol;
            $this->container->{$this->mapper->rightCol} = $this->origRightCol;
            if (strlen($lc = $this->mapper->levelCol)) {
                $this->container->$lc = $this->origLevelCol;
            }
        }
        return $res;
    }
    
    protected function getUpdatedOrdering($parentId, $ordering, $isParentIdChanged) {
        
        if ($parentId === null) {
            if (!$this->mapper->getIsCreatingRootNode()) {
                $parentId = $this->mapper->getRootNodeId();
            }
        }
        
        $res = parent::getUpdatedOrdering($parentId, $ordering, $isParentIdChanged);
        return $res;
    }
    
    
    protected function applyTreePositionChange() {
        if ($this->newParentId === null) {
            if (!$this->mapper->getIsCreatingRootNode()) {
                $this->newParentId = $this->mapper->getRootNodeId();
            }
        }
        $res = parent::applyTreePositionChange();
        if ($res && $this->isPersistent() && $this->container) { 
            // reload data from DB and update container
            // non-effective, but should work
            $node = $this->mapper->getNestedSets()->getNode($this->getNodeId());
            if ($node) {
                $this->container->{$this->mapper->leftCol} = $node[$this->mapper->leftCol];
                $this->container->{$this->mapper->rightCol} = $node[$this->mapper->rightCol];
                $this->container->{$this->orderingField} = $node[$this->orderingField];
                $this->container->{$this->parentField} = $node[$this->parentField];
                if (strlen($lc = $this->mapper->levelCol)) {
                    $this->container->$lc = $node[$lc];
                }
                $this->updateFromContainer();
            }
        }
        return $res;
    }
    
}
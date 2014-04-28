<?php

class Ac_Model_Tree_ComboImpl extends Ac_Model_Tree_AdjacencyListImpl {

    protected $requiredMapperClass = 'Ac_I_Tree_Mapper_Combo';
    
    protected function coreDelete() {
        $ns = $this->getContainer()->getMapper()->getNestedSets();
        $ns->deleteNode($this->getContainer()->getPrimaryKey());
    }
    
    protected function shouldReorder($parentIdIfChanged, $orderIfChanged) {
        return $parentIdIfChanged !== false || !is_null($orderIfChanged);
    }
    
    protected function doAfterContainerSave() {
        $this->store();
        $this->updateFromContainer();
    }
    
}
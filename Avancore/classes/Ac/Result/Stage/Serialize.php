<?php

class Ac_Result_Stage_Serialize extends Ac_Result_Stage_Morph {

    protected $defaultTraverseClasses = array ('Ac_Result', 'Ac_I_Deferred', 'Ac_I_StringObject_WithRender');
    
    protected $render = false;
    
    protected $mustWriteAll = false;
    
    // will be passed to $this->render during instantiation
    protected $isBeforeStore = true;

    
    
    function beginItem($item) {
        parent::beginItem($item);
        if ($item instanceof Ac_Result) {
            if ($this->mustWriteAll || $item->getWriteOnStore()) {
                if ($this->getIsChangeable()) {
                    $this->replaceCurrentObject($s = $item->writeAndReturn());
                }
            }
        }
    }
    
    function endItem($item) {
        parent::endItem($item);
        if ($item instanceof Ac_Result) {
            $item->notifyIsStored();
        } else $this->renderIfNecessary ($item);
    }
    
    protected function traverse($classes = null) {    
        if ($this->root && $this->root->getWriteOnStore()) {
            $tmp = true;
            $this->mustWriteAll = true;
        }
        $res = parent::traverse($classes);
        if (isset($tmp)) {
            $this->mustWriteAll = false;
        }
        return $res;
    }
    
    function getIsBeforeStore() {
        // if root has getWriteOnStore() === true, we will render all deferreds during this stage
        if ($this->mustWriteAll) return false;
            else return $this->isBeforeStore;
    }
    
}
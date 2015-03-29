<?php

class Ac_Result_Stage_Unserialize extends Ac_Result_Stage {
    
    // is used to call special methods of the handlers
    protected $stageName = 'Unserialize';    
    
    function setCurrentResultObsolete() {
        if (($r = $this->getCurrentResult())) $r->markObsolete();
        
        // Currently parent result will be marked as obsolete too
        if ($p = $this->getParentResult()) $p->markObsolete();
    }
    
    protected function endItem($item) {    
        parent::endItem($item);
        if ($item instanceof Ac_Result && $item->getIsObsolete())
            $this->setCurrentResultObsolete();
    }
    
}
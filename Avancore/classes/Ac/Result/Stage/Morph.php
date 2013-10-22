<?php

class Ac_Result_Stage_Morph extends Ac_Result_Stage {
    
    /**
     * @var bool
     */
    protected $isBeforeStore = false;

    /**
     * @return bool
     */
    function getIsBeforeStore() {
        return $this->isBeforeStore;
    }    
    
}
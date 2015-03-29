<?php

abstract class Ac_Param_Filter extends Ac_Prototyped implements Ac_I_Param_Filter, Ac_I_Decorator {
    
    function hasPublicVars() {
        return true;
    }
    
    protected $isFilterFinal = false;
    
    function setIsFilterFinal($isFilterFinal) {
        $this->isFilterFinal = (bool) $isFilterFinal;
    }
    
    function getIsFilterFinal() {
        return $this->isFilterFinal;
    }
    
    /**
     * (non-PHPdoc)
     * @see Ac_I_Decorator::apply()
     */
    function apply($value) {
        return $this->filter($value, null);
    }
    
}
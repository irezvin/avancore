<?php

abstract class Ae_Param_Filter extends Ae_Autoparams implements Ae_I_Param_Filter, Ae_I_Decorator {
    
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
     * @see Ae_I_Decorator::apply()
     */
    function apply($value) {
        return $this->filter($value, null);
    }
    
}
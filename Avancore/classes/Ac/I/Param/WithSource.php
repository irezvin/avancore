<?php

interface Ac_I_Param_WithSource extends Ac_I_Param {
    
    /**
     * @return Ac_I_Param_Source
     */
    function getSource();
    
    function setSource(Ac_I_Param_Source $source = null);
    
}
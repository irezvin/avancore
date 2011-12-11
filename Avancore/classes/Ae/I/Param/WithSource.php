<?php

interface Ae_I_Param_WithSource extends Ae_I_Param {
    
    /**
     * @return Ae_I_Param_Source
     */
    function getSource();
    
    function setSource(Ae_I_Param_Source $source = null);
    
}
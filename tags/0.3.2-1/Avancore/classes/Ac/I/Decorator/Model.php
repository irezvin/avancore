<?php

interface Ac_I_Decorator_Model extends Ac_I_Decorator {
    
    function setModel(Ac_Model_Data $model = null);

    /**
     * @return Ac_Model_Data
     */
    function getModel();
    
}
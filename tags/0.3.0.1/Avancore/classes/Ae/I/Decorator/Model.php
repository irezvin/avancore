<?php

interface Ae_I_Decorator_Model extends Ae_I_Decorator {
    
    function setModel(Ae_Model_Data $model = null);

    /**
     * @return Ae_Model_Data
     */
    function getModel();
    
}
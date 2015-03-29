<?php

interface Ac_I_Decorator_Model extends Ac_I_Decorator {
    
    function setModel($model);

    /**
     * @return Ac_Model_Data
     */
    function getModel();
    
}
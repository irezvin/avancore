<?php

class Ac_Etl_Detector_String extends Ac_Etl_Detector_Abstract {

    protected $nullifyEmpty = false;
    
    protected $dataPrototype = array('class' => 'Ac_Etl_Detector_Data_String');
    
    function doCheck($value, &$problems = false) {
        $problems = false;
        return true;
    }
    
}

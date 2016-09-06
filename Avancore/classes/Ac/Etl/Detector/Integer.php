<?php

class Ac_Etl_Detector_Integer extends Ac_Etl_Detector_Abstract {
    
    protected $dataPrototype = array('class' => 'Ac_Etl_Detector_Data_Number');

    function doCheck($value, & $problems = false) {
        if (strlen($value)) {
            $ok = is_numeric($value) && (''.intval($value) === ''.$value);
            if (!$ok) $problems = 'is not an integer';
        }
    }
    
}

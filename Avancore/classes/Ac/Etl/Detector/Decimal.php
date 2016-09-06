<?php

class Ac_Etl_Detector_Decimal extends Ac_Etl_Detector_Abstract {
    
    protected $fixComma = false;
    
    protected $dataPrototype = array('class' => 'Ac_Etl_Detector_Data_Number');

    function setFixComma($fixComma) {
        $this->fixComma = $fixComma;
    }

    function getFixComma() {
        return $this->fixComma;
    }    
    
    function process($value) {
        $value = parent::process($value);
        if ($this->fixComma && strlen($value)) $value = str_replace(',', '.', $value);
        return $value;
    }
    
    function doCheck($value, &$problems = false) {
        $ok = is_numeric($value) && !(''.intval($value) === ''.$value);
        if (!$ok) $problems = 'is not a decimal number';
        static $i;
        if (!$i) $i = 0;
        if ($ok) {
            //var_dump($value);
            //if ($i++ > 100) die();
        }
    }
    
}

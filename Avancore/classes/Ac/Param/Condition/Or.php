<?php

class Ac_Param_Condition_Or extends Ac_Param_Condition_Composite {

    var $evaluateAll = false;

    function match($value, & $errors = array(), Ac_I_Param $param = null) {
        if (!count($l = $this->listConditions()))
            throw new Exception("Ac_Param_Condition_Or: no child conditions provided - use setConditions() first");

        $res = false;

        $allErrors = array();

        foreach ($l as $i) {
            if ($this->getCondition($i)->match($value, $allErrors, $param)) {
                $res = true;
                break;
            }
        }
        
        if (!$res) Ac_Util::ms($errors, $allErrors);

        return $res;
    }    

}

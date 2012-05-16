<?php

class Ac_Param_Array extends Ac_Param_Parent {

    const keyId = 'key';
    const valueId = 'value';
    
    function addParam(Ac_Param $param) {
        $id = $param->getId();
        if (!in_array($id, $a = array(self::keyId, self::valueId)))
            throw new Exception("Only ids '".implode("', '", $a)."' allowed in ".get_class($this).", '{$id} provided");
        return parent::addParam($param);
    }
    
}
<?php

class Ae_Param_Array extends Ae_Param_Parent {

    const keyId = 'key';
    const valueId = 'value';
    
    function addParam(Ae_Param $param) {
        $id = $param->getId();
        if (!in_array($id, $a = array(self::keyId, self::valueId)))
            throw new Exception("Only ids '".implode("', '", $a)."' allowed in ".get_class($this).", '{$id} provided");
        return parent::addParam($param);
    }
    
}
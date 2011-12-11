<?php

abstract class Ae_Param_Condition extends Ae_Autoparams implements Ae_I_Param_Condition {

    protected $propName = false;
    
    function setPropName($propName) {
        $this->propName = $propName;
    }

    function getPropName() {
        return $this->propName;
    }    
    
    function getTranslations() {
        return array();
    }
    
    function hasPublicVars() {
        return true;
    }
    
    function regError(array & $errors = array(), $id, $prefix, $default = '', $param = null, array $extraTrans = array()) {
        $t = $this->getTranslations();
        if ($extraTrans) $t = array_merge($t, $extraTrans);
        $tt = array();
        foreach ($t as $k => $v) $tt["{".$k."}"] = $v;
        $res = new Ae_Param_Condition_Error($id, $prefix, $default, $param? $param->getId() : '', $tt);
        $errors[$id] = $res;
        return $res;
    }
    
}
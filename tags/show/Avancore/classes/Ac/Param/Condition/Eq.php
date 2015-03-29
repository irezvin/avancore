<?php

class Ac_Param_Condition_Eq extends Ac_Param_Condition {    
    
    protected $eq = false;

    protected $strict = false;
    
    protected $hasEq = false;

    function setEq($eq) {
        $this->eq = $eq;
        $this->hasEq = true;
    }

    function getEq() {
        return $this->eq;
    }
    
    function removeEq() {
        $this->hasEq = false;
    }

    function setStrict($strict) {
        $this->strict = $strict;
    }

    function getStrict() {
        return $this->strict;
    }

    function getTranslations() {
        return array_merge(parent::getTranslations(), Ac_Accessor::getObjectProperty($this, array('eq')));
    }
    
    function match($value, & $errors = array(), Ac_I_Param $param = null) {
        if ($this->hasEq) {
            $ok = $this->strict? $value === $this->eq : $value == $this->eq;
            if (!$ok) $this->regError($errors, 'eq', 'ae_param_condition_eq', '{param} must match {eq}');
        }
                
        return !$errors;        
    }
    
}
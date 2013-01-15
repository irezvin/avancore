<?php

class Ac_Param_Condition_Enum extends Ac_Param_Condition {
    
    /**
     * @var array | Ac_Model_Values | Ac_I_EnumProvider
     */
    var $values = array();
    
    var $failIfNoValues = false;

    var $strictCompare = false;
    
    var $valuesDescr = false;

    function getTranslations() {
        return array_merge(parent::getTranslations(), Ac_Accessor::getObjectProperty($this, array('valuesDescr')));
    }
    
    function match($value, & $errors = array(), Ac_I_Param $param = null) {
        $px = 'ae_param_condition_enum';
        $ok = false;
        if ($this->values instanceof Ac_I_EnumProvider && ($this->values->check($value))) {
            $ok = true;
        } elseif ($this->values instanceof Ac_Model_Values && ($this->values->check($value))) {
            $ok = true;
        } elseif (is_array($this->values)) {
            if (count($this->values)) {
                if (in_array($value, $this->values, $this->strictCompare)) {
                    $ok = true;
                }
            } else ($ok = !$this->failIfNoValues);
        } else {
            throw new Exception("Invalid \$values format; it should be an array, an Ac_Model_Values or an Ac_I_EnumProvider instance");
        }
        if (!$ok) {
            if ($this->valuesDescr !== false) $this->regError($errors, 'bad_enum_with_descr', $px, '{param} must be a valid {valuesDescr}', $param);
                else $this->regError($errors, 'bad_enum', $px, 'invalid {param} value', $param);
        }
        return $ok;
    }
    
    
}
<?php

class Ae_Param_Condition_Enum extends Ae_Param_Condition {
    
    /**
     * @var array | Ae_Model_Values | Ae_I_EnumProvider
     */
    var $values = array();
    
    var $failIfNoValues = false;

    var $strictCompare = false;
    
    var $valuesDescr = false;

    function getTranslations() {
        return array_merge(parent::getTranslations(), Ae_Autoparams::getObjectProperty($this, array('valuesDescr')));
    }
    
    function match($value, & $errors = array(), Ae_I_Param $param = null) {
        $px = 'ae_param_condition_enum';
        $ok = false;
        if ($this->values instanceof Ae_I_EnumProvider && ($this->values->check($value))) {
            $ok = true;
        } elseif ($this->values instanceof Ae_Model_Values && ($this->values->check($value))) {
            $ok = true;
        } elseif (is_array($this->values)) {
            if (count($this->values)) {
                if (in_array($value, $this->values, $this->strictCompare)) {
                    $ok = true;
                }
            } else ($ok = !$this->failIfNoValues);
        } else {
            throw new Exception("Invalid \$values format; it should be an array, an Ae_Model_Values or an Ae_I_EnumProvider instance");
        }
        if (!$ok) {
            if ($this->valuesDescr !== false) $this->regError($errors, 'bad_enum_with_descr', $px, '{param} must be a valid {valuesDescr}', $param);
                else $this->regError($errors, 'bad_enum', $px, 'invalid {param} value', $param);
        }
        return $ok;
    }
    
    
}
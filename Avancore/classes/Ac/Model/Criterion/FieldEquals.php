<?php

class Ac_Model_Criterion_FieldEquals extends Ac_Prototyped implements Ac_I_Search_Criterion_Search {
    
    protected $field = false;
    
    protected $value = false;
    
    /**
     * @var bool
     */
    protected $valueIsSet = false;
    
    /**
     * @var bool
     */
    protected $strict = false;
    
    function test($record, $name, $value, $adHoc) {
        if ($this->valueIsSet) {
            $value = $this->value;
        } else {
            if ($adHoc) throw new Ac_E_InvalidUsage("Please setValue() before using test() when applying ".get_class($this)." \$adHoc");
        }
        if ($this->field === false) $field = $name;
            else $field = $this->field;
        $res = $strict? $record->$field === $value : $record->$field == $value;
        return $res;
    }

    function setField($field) {
        $this->field = $field;
    }

    function getField() {
        return $this->field;
    }

    function setValue($value) {
        $this->value = $value;
        $this->valueIsSet = true;
    }

    function getValue() {
        return $this->value;
    }

    function deleteValue() {
        $this->valueIsSet = false;
        $this->value = false;
    }
    
    function setValueIsSet($valueIsSet) {
        $this->valueIsSet = (bool) $valueIsSet;
    }
    
    function getValueIsSet() {
        return $this->valueIsSet;
    }    
    
    function hasPublicVars() {
        return true;
    }

    /**
     * @param bool $strict
     */
    function setStrict($strict) {
        $this->strict = $strict;
    }

    /**
     * @return bool
     */
    function getStrict() {
        return $this->strict;
    }    
    
}
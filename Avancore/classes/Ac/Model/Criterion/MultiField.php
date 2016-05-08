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
    
    /**
     * @var bool Whether use $this->getFieldValue() (change to TRUE in descendant classes if field-retrieval logic complicates)
     */
    protected $useGFV = false;
    
    protected function getFieldValue($record, $field) {
        return $record->$field;
    }
    
    function test($record, $name, $value, $adHoc) {
        if ($this->valueIsSet) {
            $value = $this->value;
        } else {
            if ($adHoc) throw new Ac_E_InvalidUsage("Please setValue() before using test() when applying ".get_class($this)." \$adHoc");
        }
        $strict = $this->strict;
        if ($this->field === false) $field = $name;
            else $field = $this->field;
        $recordValue = $this->useGFV? $this->getFieldValue($record, $field) : $record->$field;
        if (is_array($value)) $res = in_array($recordValue, $value, $strict);
            else $res = $strict? $recordValue === $value : $recordValue == $value;
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

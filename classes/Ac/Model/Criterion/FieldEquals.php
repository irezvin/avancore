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
     * require strict equality for NULL values (makes no sense if $strict === true)
     * @var boolean
     */
    protected $strictNulls = true;
    
    /**
     * Provided $values array had null (used only when $strict === false && $strictNulls === true)
     * @var boolean
     */
    protected $hasNull = false;
    
    /**
     * @var bool Whether use $this->getFieldValue() (change to TRUE in descendant classes if field-retrieval logic complicates)
     */
    protected $useGFV = false;
    
    protected function getFieldValue($record, $field) {
        return $record->$field;
    }
    
    function test($record, $name, $value, $adHoc) {
        $hasNull = false;
        if ($this->valueIsSet) {
            $value = $this->value;
            $hasNull = $this->hasNull;
        } else {
            if ($adHoc) throw new Ac_E_InvalidUsage("Please setValue() before using test() when applying ".get_class($this)." ad-hoc");
            if ($this->strictNulls && !$this->strict && is_array($value)) {
                $hasNull = in_array(null, $value, true);
                if ($hasNull) $value = array_diff($value, array(null));
            }
        }
        if ($this->field === false) $field = $name;
            else $field = $this->field;
        $recordValue = $this->useGFV? $this->getFieldValue($record, $field) : $record->$field;
        if (!$this->strict && $this->strictNulls)  {
            if (is_array($value)) {
                if ($recordValue === null) $res = $hasNull;
                else $res = in_array($recordValue, $value, false);
            } else {
                if ($recordValue === null) $res = ($value === null);
                    else $res = $recordValue == $value;
            }
        } else {
            if (is_array($value)) $res = in_array($recordValue, $value, $this->strict);
                else $res = $this->strict? $recordValue === $value : $recordValue == $value;
        }
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
        $this->hasNull = false;
        if (is_array($this->value) && !$this->strict && $this->strictNulls) 
            $this->setStrictNulls($this->strictNulls);
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
        if (!$this->valueIsSet) {
            $this->value = false;
            $this->hasNull = false;
        }
    }
    
    function getValueIsSet() {
        return $this->valueIsSet;
    }    
    
    /**
     * @param bool $strict
     */
    function setStrict($strict) {
        $this->strict = $strict;
        if (is_array($this->value)) {
            $this->setStrictNulls($this->strictNulls);
        }
    }

    /**
     * @return bool
     */
    function getStrict() {
        return $this->strict;
    }    

    /**
     * Sets whether to require strict equality for NULL values
     * @param boolean $strictNulls
     */
    function setStrictNulls($strictNulls) {
        // when $strictNulls is enabled and we have array $value, we need move NULL element (if any) out of it
        // to do strict checking. When $strictNulls is disabled, we put NULL element (if any)
        
        $this->strictNulls = $strictNulls;
        if (is_array($this->value)) {
            if ($this->strictNulls && !$this->strict && !$this->hasNull) {
                $d = array_diff($this->value, array(null));
                if (count($d) != count($this->value)) {
                    $this->hasNull = true;
                    $this->value = $d;
                }
            } elseif (!$this->strictNulls && $this->strict && $this->hasNull) {
                $this->hasNull = false;
                $this->value[] = null;
            }
        }
    }

    /**
     * Returns whether to require strict equality for NULL values
     * @return boolean
     */
    function getStrictNulls() {
        return $this->strictNulls;
    }    
    
}

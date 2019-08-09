<?php

class Ac_Model_Criterion_MultiField extends Ac_Prototyped implements Ac_I_Search_Criterion_Search, Ac_I_Search_Criterion_Bulk {
    
    protected $fields = false;
    
    protected $values = false;
    
    /**
     * @var bool
     */
    protected $valuesAreSet = false;
    
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
     * @var bool Whether use $this->getFieldValues() (change to TRUE in descendant classes if field-retrieval logic complicates)
     */
    protected $useGFV = false;
    
    protected $checked = false;
    
    protected function getFieldValue($record, $field) {
        return $record->$field;
    }
    
    function filter (array $records, $name, $value, $adHoc) {
        $externalFieldsOrValues = false;
        if ($adHoc) {
            if (!$this->valuesAreSet) throw new Ac_E_InvalidUsage("Please setValues() before using test() ".get_class($this)." ad-hoc");
            $value = $this->values;
        } else {
            if ($this->valuesAreSet) {
                $value = $this->values;
                $externalFieldsOrValues = true;
            }
        }
        if (!$this->fields) {
            $fields = array($name);
            $externalFieldsOrValues = true;
        } else {
            $fields = $this->fields;
        }
        if ($externalFieldsOrValues) {
            list ($fieldNames, $fieldValues) = Ac_Model_Mapper::checkMultiFieldCriterion($fields, $value);
        } else {
            if (!$this->checked) $this->checked = Ac_Model_Mapper::checkMultiFieldCriterion($fields, $value);
            list ($fieldNames, $fieldValues) = $this->checked;
        }
        
        $res = array();
        foreach ($records as $k => $record) if ($this->doTest($record, $fieldNames, $fieldValues)) 
            $res[$k] = $record;
        
        return $res;
    }
    
    protected function doTest($record, $fieldNames, $fieldValues) {
        $res = false;
        $pat = array();
        foreach ($fieldNames as $k => $f) $pat[$k] = $this->useGFV? $this->getFieldValue($record, $f) : $record->$f;
        if (count($fieldNames) === 1) {
            if ($this->strict || !$this->strictNulls) $res = in_array($pat[0], $fieldValues, $this->strict);
            else { // strictNulls && !strict
                foreach ($fieldValues as $v) {
                    if ( ($pat[0] === null || $v === null)? $pat[0] === $v : $pat == $v) { 
                        $res = true; break; 
                    }
                }
            }
        } else {
            foreach ($fieldValues as $v) {
                if ($this->strict) {
                    if ($pat === $v) { $res = true; break; }
                } else {
                    $match = true;
                    foreach ($pat as $i => $t) {
                        if (!($this->strictNulls && ($t === null || $v[$i] === null)? $t === $v[$i] : $t == $v[$i])) {
                            $match = false;
                            break;
                        }
                    }
                    if ($match) {
                        $res = true;
                        break;
                    }
                }
            }
        }
        return $res;
    }
    
    function test($record, $name, $value, $adHoc) {
        $res = (bool) count($this->filer(array($record), $name, $value, $adHoc));
        return $res;
    }

    function setFields($fields) {
        $this->fields = $fields;
        $this->checked = false;
    }

    function getFields() {
        return $this->fields;
    }

    function setValues($values) {
        $this->values = $values;
        $this->valuesAreSet = true;
        $this->checked = false;
    }

    function getValues() {
        return $this->values;
    }

    function deleteValues() {
        $this->valuesAreSet = false;
        $this->values = false;
    }
    
    function setValuesAreSet($valueIsSet) {
        $this->valuesAreSet = (bool) $valueIsSet;
        if (!$this->valuesAreSet) {
            $this->values = false;
        }
    }
    
    function getValuesAreSet() {
        return $this->valuesAreSet;
    }    
    
    /**
     * @param bool $strict
     */
    function setStrict($strict) {
        $this->strict = $strict;
        if (is_array($this->values)) {
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
    }

    /**
     * Returns whether to require strict equality for NULL values
     * @return boolean
     */
    function getStrictNulls() {
        return $this->strictNulls;
    }    
    
}

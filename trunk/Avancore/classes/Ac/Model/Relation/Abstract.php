<?php

/**
 * Defines members and methods that are common to Ac_Model_Relation and Ac_Model_Relation_Tree objects
 * (that are used mostly for data retrieval)
 */
class Ac_Model_Relation_Abstract extends Ac_Prototyped {
    
    protected $debug = false;

    function __get($var) {
        if (method_exists($this, $m = 'get'.$var)) return $this->$m();
        else Ac_E_InvalidCall::noSuchProperty ($this, $var);
    }

    function __set($var, $value) {
        if (method_exists($this, $m = 'set'.$var)) $this->$m($value);
        else throw Ac_E_InvalidCall::noSuchProperty ($this, $var);
    }
    
    function setDebug($debug) {
        $this->debug = (bool) $debug;
    }

    function getDebug() {
        return $this->debug;
    }


    function hasPublicVars() {
        return false;
    }
    
    protected function setVal(& $dest, $varName, $val, $qualifier = false) {
        if (!$varName) return;
        if ($qualifier !== false && $qualifier !== null && is_array($val)) {
            $val = Ac_Util::indexArray($val, $qualifier, true);
        }
        if (is_array($dest)) {
            $dest[$varName] = $val;
        }
        elseif (method_exists($dest, $setter = 'set'.$varName)) $dest->$setter($val);
        else $dest->$varName = $val;
    }
    
    /**
     * $linkTo->$varName = $linked or $linkTo[$varName] = $linked
     *
     * @param object|array $linkTo - object or array to which we are linking $linked object 
     * @param object|array $linked - object that we are adding
     * @param string $varName - name of $linkTo property or key (if it's an array)
     * @param bool $toIsArray - if $linkTo is an array of an objects or an arrays
     * @param bool $linkedIsUnique - whether we should replace $linkTo->varName or add to it ($linkTo->varName[]) 
     */
    protected function linkBack(& $linkTo, & $linked, $varName, $toIsArray, $linkedIsUnique, $qualifier = false, $qKey = null) {
        if (!$varName) return;
        if (is_null($qualifier)) $qualifier = false;
        elseif ($qualifier !== false) {
            if ($qKey === null) $qKey = Ac_Accessor::getObjectProperty ($linked, $qualifier, true);
        }
        if ($toIsArray) {
            foreach (array_keys($linkTo) as $k) {
                $this->linkBack($linkTo[$k], $linked, $varName, false, $linkedIsUnique, $qualifier, $qKey);
            }
        } else {
            $lt = & $linkTo;
            if (is_object($lt)) {
                if ($linkedIsUnique) {
                    $lt->$varName = $linked;
                } else {
                    $skip = false;
                    if (isset($lt->$varName) && is_array($v = $lt->$varName)) {
                        if ($lt instanceof Ac_Model_Object && $lt->hasFullPrimaryKey()) {
                            $pk = $lt->getPrimaryKey();
                            foreach ($v as $item) {
                                if (is_object($item) && $item instanceof Ac_Model_Object 
                                    && $item->hasFullPrimaryKey() && $item->getPrimaryKey() == $pk) {
                                    $skip = true;
                                    break;
                                }
                            }
                        }
                    } else {
                        $lt->$varName = array();
                    }
                    if (!$skip) {
                        if ($qKey !== null) {
                            $lt->{$varName}[$qKey] = $linked;
                        } else {
                            $lt->{$varName}[] = $linked;
                        }
                    }
                }
            } elseif (is_array($lt)) {
                if ($linkedIsUnique) {
                    $lt[$varName] = $linked;
                } else {
                    if (isset($lt[$varName]) && is_array($lt[$varName])) {
                        // TODO: check that record with same key not already there
                        // PKs of array records is not supported yet
                    } else {
                        $lt[$varName] = array();
                    }
                    if ($qKey !== null) {
                        $lt[$varName][$qKey] = $linked;
                    } else {
                        $lt[$varName][] = $linked;
                    }
                        
                }
            }
        }
    }
    
    protected function isFull($v) {
        if (is_array($v)) foreach ($v as $vv) {
            if (is_null($vv) || $vv === false) return false; 
        } else {
            if (is_null($v) || $v === false) return false;
        }
        return true;
    }
    
    protected function putRowToArray(& $row, & $instance, & $array, $keys, $unique) {
        foreach ($keys as $key) $path[] = $row[$key];
        Ac_Util::simpleSetArrayByPathNoRef($array, $path, $instance, $unique);
    }
    
    protected function putInstanceToArray(& $instance, & $array, $keys, $isDest, $unique) {
        $path = $this->getValues($instance, $keys);
        Ac_Util::simpleSetArrayByPathNoRef($array, $path, $instance, $unique);
    }
    
    protected function getFromArray($src, $fieldName) {
        return $src[$fieldName];
    }
    
    protected function getFromMember($src, $fieldName) {
        return $src->$fieldName;
    }
    
    protected function getFromGetter($src, $fieldName) {
        $m = 'get'.ucfirst($fieldName);
        return $src->$m();
    }
    
    protected function getFromAeData($src, $fieldName) {
        return $src->$fieldName;
    }
    
    /**
     * Retrieves field value from source object or array. Caches retrieval strategy for different classes in static variable (as in Ac_Table_Column).
     * Triggers error if retrieval is not possible.
     */
    protected function getValue($src, $fieldName) {
        static $g = array();
        if (is_array($src)) {
            if (!array_key_exists($fieldName, $src)) trigger_error('Cannot extract field \''.$fieldName.'\' from an array', E_USER_ERROR);
            $res = $src[$fieldName];
        } else {
            $cls = get_class($src);
            if (isset($g[$cls]) && isset($g[$cls][$fieldName])) $getter = $g[$cls][$fieldName];
            else {
                switch(true) {
                    case in_array($fieldName, array_keys(get_class_vars($cls))): $getter = 'getFromMember'; break;
                    case method_exists($src, 'get'.$fieldName): $getter = 'getFromGetter'; break;
                    case is_a($src, 'Ac_Model_Data'): $getter = 'getFromAeData'; break;
                    default:
                        trigger_error('Cannot extract field \''.$fieldName.'\' from an object', E_USER_ERROR);
                }
                $g[$cls][$fieldName] = $getter;
            }
            $res = $this->$getter($src, $fieldName);
        }
        return $res;
    }
    
    protected function mapValues($values, $fieldNames) {
        $i = 0;
        $res = array();
        foreach ($values as $value) {
            $res[$fieldNames[$i]] = $value;
        }
        return $res;
    }
    
    /**
     * Retrieves all values of given fields from source object or array. 
     * 
     * @param Ac_Model_Data|object|array $src Information source
     * @param array|string $fieldNames Names of fields to retrieve (if $single is true, it should be single string)
     * @param $originalKeys Whether keys of result fields should be taken from $fieldNames
     * @param bool $single Whether $fieldNames is single string (single value will be returned) 
     * @return array Field values
     * @access private 
     */
    protected function getValues($src, $fieldNames, $originalKeys = false, $single = false) {
        $res = array();
        if ($single) {
            $res = $this->getValue($src, $fieldNames);
        } else {
            $c = count($fieldNames);
            if ($originalKeys)
                for ($i = 0; $i < $c; $i++) {
                    $res[$fieldNames[$i]] = $this->getValue($src, $fieldName);
                }
            else
               foreach ($fieldNames as $fieldName) {
                    $res[] = $this->getValue($src, $fieldName);
                }
        }
        return $res;
    }
    
    protected function isVarEmpty($srcItem, $var, & $value = false) {
        if (!$var) return true;
        $res = true;
        $value = false;
        if (is_array($srcItem)) {
            if (array_key_exists($var, $srcItem)) {
                if ($srcItem[$var] !== false) {
                    $value = $srcItem[$var];
                    $res = false;
                }
            }
        } else {
            if (Ac_Accessor::objectPropertyExists($srcItem, $var) 
                && ($value = $this->getValue($srcItem, $var)) !== false) {
                $res = false;
            }
        }
        return $res;
    }
    
}
    

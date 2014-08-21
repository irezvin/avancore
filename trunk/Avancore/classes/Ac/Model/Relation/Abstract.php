<?php

/**
 * Defines members and methods that are common to Ac_Model_Relation and Ac_Model_Relation_Tree objects
 * (that are used mostly for data retrieval)
 */
class Ac_Model_Relation_Abstract implements Ac_I_Prototyped {
    
    var $debug = false;

    function hasPublicVars() {
        return true;
    }
    
    function _setVal(& $dest, $varName, $val, $qualifier = false) {
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
    function _linkBack(& $linkTo, & $linked, $varName, $toIsArray, $linkedIsUnique, $qualifier = false, $qKey = null) {
        if (is_null($qualifier)) $qualifier = false;
        elseif ($qualifier !== false) {
            if ($qKey === null) $qKey = Ac_Accessor::getObjectProperty ($linked, $qualifier, true);
        }
        if ($toIsArray) {
            foreach (array_keys($linkTo) as $k) {
                $this->_linkBack($linkTo[$k], $linked, $varName, false, $linkedIsUnique, $qualifier, $qKey);
            }
        } else {
            $lt = & $linkTo;
            if (is_object($lt)) {
                if ($linkedIsUnique) {
                    $lt->$varName = $linked;
                } else {
                    if (Ac_Accessor::methodExists($lt, $m = 'add'.ucfirst($varName))) {
                        if ($qKey !== null) {
                            $lt->$m($linked, $qKey);
                        } else {
                            $lt->$m($linked);
                        }
                    } else {
                        if (!isset($lt->$varName) || !is_array($lt->$varName)) $lt->$varName = array();
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
                    if (!isset($lt[$varName]) || !is_array($lt[$varName])) $lt[$varName] = array();
                    if ($qKey !== null) {
                        $lt[$varName][$qKey] = $linked;
                    } else {
                        $lt[$varName][] = $linked;
                    }
                        
                }
            }
        }
    }
    
    function _isFull($v) {
        if (is_array($v)) foreach ($v as $vv) {
            if (is_null($vv) || $vv === false) return false; 
        } else {
            if (is_null($v) || $v === false) return false;
        }
        return true;
    }
    
    function _putRowToArray(& $row, & $instance, & $array, $keys, $unique) {
        foreach ($keys as $key) $path[] = $row[$key];
        Ac_Util::simpleSetArrayByPathNoRef($array, $path, $instance, $unique);
    }
    
    function _putInstanceToArray(& $instance, & $array, $keys, $isDest, $unique) {
        $path = $this->_getValues($instance, $keys);
        Ac_Util::simpleSetArrayByPathNoRef($array, $path, $instance, $unique);
    }
    
    function _getFromArray($src, $fieldName) {
        return $src[$fieldName];
    }
    
    function _getFromMember($src, $fieldName) {
        return $src->$fieldName;
    }
    
    function _getFromGetter($src, $fieldName) {
        $m = 'get'.ucfirst($fieldName);
        return $src->$m();
    }
    
    function _getFromAeData($src, $fieldName) {
        return $src->getField($fieldName);
    }
    
    /**
     * Retrieves field value from source object or array. Caches retrieval strategy for different classes in static variable (as in Ac_Table_Column).
     * Triggers error if retrieval is not possible.
     */
    function _getValue($src, $fieldName) {
        static $g = array();
        if (is_array($src)) {
            if (!isset($src[$fieldName])) trigger_error('Cannot extract field \''.$fieldName.'\' from an array', E_USER_ERROR);
            $res = $src[$fieldName];
        } else {
            $cls = get_class($src);
            if (isset($g[$cls]) && isset($g[$cls][$fieldName])) $getter = $g[$cls][$fieldName];
            else {
                switch(true) {
                    case in_array($fieldName, array_keys(get_class_vars($cls))): $getter = '_getFromMember'; break;
                    case method_exists($src, 'get'.$fieldName): $getter = '_getFromGetter'; break;
                    case is_a($src, 'Ac_Model_Data'): $getter = '_getFromAeData'; break;
                    default:
                        trigger_error('Cannot extract field \''.$fieldName.'\' from an object', E_USER_ERROR);
                }
                $g[$cls][$fieldName] = $getter;
            }
            $res = $this->$getter($src, $fieldName);
        }
        return $res;
    }
    
    function _mapValues($values, $fieldNames) {
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
    function _getValues($src, $fieldNames, $originalKeys = false, $single = false) {
        $res = array();
        if ($single) {
            $res = $this->_getValue($src, $fieldNames);
        } else {
            $c = count($fieldNames);
            if ($originalKeys)
                for ($i = 0; $i < $c; $i++) {
                    $res[$fieldNames[$i]] = $this->_getValue($src, $fieldName);
                }
            else
               foreach ($fieldNames as $fieldName) {
                    $res[] = $this->_getValue($src, $fieldName);
                }
        }
        return $res;
    }
    
    function _isVarEmpty($srcItem, $var) {
        $res = true;
        if (is_array($srcItem)) {
            if (array_key_exists($var, $srcItem) && $srcItem[$var] !== false) $res = false;
        } else {
            if (Ac_Accessor::objectPropertyExists($srcItem, $var) 
                && $this->_getValue($srcItem, $var) !== false) {
                $res = false;
            }
        }
        return $res;
    }
    
}
    

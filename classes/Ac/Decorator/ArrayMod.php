<?php

class Ac_Decorator_ArrayMod extends Ac_Decorator {

    var $subKeys = false;
    
    var $keyDecorator = array();
    
    var $deleteKeys = array();
    
    var $map = array();
    
    var $mapOnly = false;
    
    var $arrayValues = false;
    
    var $deleteElements = array();
    
    var $deleteKeysRegexp = false;
    
    var $decorators = array();
    
    var $valueDecorator = array();
    
    var $sliceOffset = null;
    
    var $sliceLength = null;
    
    var $rejectIf = array();
    
    // use this member of result array to produce result keys
    var $keyKey = false;
    
    // remove "key" member from result array
    var $stripKeyKey = false;
    
    // use this member of result array to product result values
    var $valueKey = false;
    
    // convert numeric values to numbers
    var $autoNumbers = false;
    
    var $maxPrecision = false;
    
    var $implode = false;
    
    function apply($value) {
        if (!is_array($value)) return $value;
        if ($this->keyDecorator) {
            $newValue = array();
            foreach ($value as $k => $v) {
                $k = Ac_Decorator::decorate($this->keyDecorator, $k, $this->keyDecorator);
                if ($k === null) continue;
                $newValue[$k] = $v;
            }
            $value = $newValue;
        }
        if ($this->deleteKeys) {
            $value = array_diff_key($value, array_flip($this->deleteKeys));
        }
        if ($this->deleteKeysRegexp) {
            $value = array_diff_key($value, array_flip(preg_grep($this->deleteKeysRegexp, array_keys($value))));
        }
        if ($this->subKeys !== false) {
            $subKeys = array_flip(array_unique(Ac_Util::toArray($this->subKeys)));
            $value = array_intersect_key($value, $subKeys);
        }
        if ($this->map && is_array($this->map)) {
            $newValue = array();
            foreach ($value as $k => $v) {
                if (array_key_exists($k, $this->map)) {
                    $k = $this->map[$k];
                } elseif ($this->mapOnly) {
                    continue;
                }
                if ($k === null) continue;
                if (is_array($k)) {
                    Ac_Util::setArrayByPath($newValue, $k, $v);
                } else {
                    $newValue[$k] = $v;
                }
            }
            $value = $newValue;
        }
        if ($this->decorators || $this->valueDecorator) {
            Ac_Decorator::pushModel($value);
            foreach ($value as $k => $v) {
                $p = false;
                if (is_array($v)) {
                    $p = true;
                    Ac_Decorator::pushModel($v);
                }
                if ($this->valueDecorator) {
                    $v = Ac_Decorator::decorate($this->valueDecorator, $v, $this->valueDecorator);
                }
                if (isset($this->decorators[$k])) {
                    $v = Ac_Decorator::decorate($this->decorators[$k], $v, $this->decorators[$k]);
                }
                $value[$k] = $v;
                if ($p) Ac_Decorator::popModel();
            }
            Ac_Decorator::popModel();
        }
        if ($this->deleteElements) {
            $value = array_diff($value, Ac_Util::toArray($this->deleteElements));
        }
        if ($this->arrayValues) $value = array_values($value);
        if ($this->sliceOffset !== null || $this->sliceLength !== null) {
            $value = array_slice($value, $this->sliceOffset, $this->sliceLength);
        }
        if ($this->subKeys !== false && !is_array($this->subKeys)) {
            // !is_array($this->subKeys) means we had only one sub-key which we should return
            $value = $value? array_shift($value) : null;
        }
        if ($this->autoNumbers) {
            foreach ($value as $k => $v) {
                if (!is_numeric($v)) continue;
                if ((int) ($v) == (string) $v) $v = (int) $v;
                else {
                    $v = (float) $v;
                    if ($this->maxPrecision !== false) {
                        $v = round($v, $this->maxPrecision);
                    }
                }
                $value[$k] = $v;
            }
        }
        if ($this->keyKey) {
            $resultArray = array();
            foreach ($value as $key => $item) {
                if (!is_array($item)) continue;
                if (!array_key_exists($this->keyKey, $item) || !is_scalar($item[$this->keyKey])) continue;
                $key = $item[$this->keyKey];
                if ($this->stripKeyKey) unset($item[$this->keyKey]);
                if ($this->valueKey) {
                    if (is_array($this->valueKey)) {
                        $item = Ac_Util::getArrayByPath($item, $this->valueKey, null, $found);
                        if (!$found) continue;
                    } else {
                        if (!array_key_exists($this->valueKey, $item)) continue;
                        $item = $item[$this->valueKey];
                    }
                }
                $resultArray[$key] = $item;
            }
            $value = $resultArray;
        }
        
        if (is_array($value) && $this->rejectIf) {
            foreach ($value as $k => $v) {
                if (!is_array($this->rejectIf) || !is_array($v)) continue;
                if (Ac_Accessor::itemMatchesPattern($v, $this->rejectIf, false, false, true)) {
                    unset($value[$k]);
                    continue;
                }
            }
            if ($this->arrayValues) $value = array_values($value);
        }
        
        if (is_array($value) && $this->implode !== false) $value = implode($this->implode, $value);
        
        return $value;
    }
    
}

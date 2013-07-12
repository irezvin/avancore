<?php

class Ac_Registry implements Ac_I_Registry/*, Ac_I_WithClone*/ /*, Ac_I_Prototyped*/ /*, Iterator, ArrayAccess*/ {

    protected $registry = array();
    
    protected $position = null;
    
    protected $keys = null;
    
    protected $strictTypes = false;
    
    function isMergeableWith($value) {
        return true;
    }
    
    function mergeWith($value, $preserveExistingValues = false) {
        return $this->mergeRegistry($value, $preserveExistingValues);
    }
    
    function getMergedWith($value, $preserveExistingValues = false) {
        $tmp = clone $this;
        $tmp->mergeWith($value, $preserveExistingValues);
        return $tmp;
    }
    
    function isRightMergeableWith($value) {
        return is_array($value);
    }
    
    function rightMergeWith($value, $preserveExistingValues = false) {
        return self::getMerged($value, $this->exportRegistry(), $preserveExistingValues);
    }
    
    /*protected function checkOp(array $path, $opType, $value) {
        
    }*/
    
    static function arrayDive(& $start, array & $path, & $ptr, $create = false) {
        $ptr['ptr'] = & $start;
        while (count($path) && is_array($ptr['ptr'])) {
            $curr = array_shift($path);
            if (array_key_exists($curr, $ptr['ptr'])) $ptr['ptr'] = & $ptr['ptr'][$curr];
                elseif($create) {
                    $ptr['ptr'][$curr] = array();
                    $ptr['ptr'] = & $ptr['ptr'][$curr];
                }
                else {
                    array_unshift ($path, $curr);
                    break;
                }
        }
        return !count($path);
    }
    
    static function flattenOnce($path) {
        $res = array();
        foreach ($path as $v) {
            if (is_array($v)) $res = array_merge($res, $v);
                else $res[] = $v;
        }
        return $res;
    }
    
    function listRegistry($keyOrPath = null, $_ = null) {
        
        $path = func_get_args();
        $path = self::flattenOnce($path);
        
        $found = self::arrayDive($this->registry, $path, $ptr);
        if (is_object($ptr['ptr']) && $ptr['ptr'] instanceof Ac_I_Registry) 
            $res = $ptr['ptr']->listRegistry($path);
        elseif ($found && is_array($ptr['ptr'])) {
            $res = array_keys($ptr['ptr']);
        } else {
            $res = null;
        }
        
        return $res;
    }
    
    function getRegistry($keyOrPath = null, $_ = null) {
        
        $path = func_get_args();
        $path = self::flattenOnce($path);
        $found = self::arrayDive($this->registry, $path, $ptr);
        if (is_object($ptr['ptr']) && $ptr['ptr'] instanceof Ac_I_Registry) 
            $res = $ptr['ptr']->getRegistry($path);
        elseif ($found) {
            $res = $ptr['ptr'];
        } else {
            $res = null;
        }
        
        return $res;
    }
    
    protected function hasOrDelete(array $path, $delete) {
        
        $last = array_pop($path);
        
        $found = self::arrayDive($this->registry, $path, $ptr);
        if (is_object($ptr['ptr']) && $ptr['ptr'] instanceof Ac_I_Registry) {
            array_push($path, $last);
            $res = $delete? $ptr['ptr']->deleteRegistry($path) : $ptr['ptr']->hasRegistry($path);
        } elseif ($found && is_array($ptr['ptr']) && array_key_exists($last, $ptr['ptr'])) {
            if ($delete) unset($ptr['ptr'][$last]);
            $res = true;            
        } else {
            $res = false;
        }
        return $res;
        
    }
    
    function hasRegistry($keyOrPath, $_ = null) {
        $path = func_get_args();
        $path = self::flattenOnce($path);
        return self::hasOrDelete($path, false);
    }
    
    function deleteRegistry($keyOrPath, $_ = null) {
        $path = func_get_args();
        $path = self::flattenOnce($path);
        $res = self::hasOrDelete($path, true);
        return $res;
    }
    
    function setRegistry($value, $keyOrPath = null, $_ = null) {
        
        $path = func_get_args();
        array_shift($path);
        $origPath = $path = self::flattenOnce($path);
        
        $found = self::arrayDive($this->registry, $path, $ptr);
        if (is_object($ptr['ptr']) && $ptr['ptr'] instanceof Ac_I_Registry) {
            if ($found && is_object($value) && $value instanceof Ac_I_Registry) {
                $ptr['ptr'] = $value;
                $res = true;
            } else {
                $res = $ptr['ptr']->setRegistry($value, $path);
            }
        } /*elseif ($found) {
            $ptr['ptr'] = $value;
            $res = true;
        }*/ else {
            if (is_array($ptr['ptr'])) {
                while(count($path)) {
                    $first = array_shift($path);
                    $ptr['ptr'][$first] = array();
                    $ptr['ptr'] = & $ptr['ptr'][$first];
                }
                // TODO: improve
                if (isset($ptr['ptr']) && is_array($ptr['ptr'])) {
                    $this->clearRegistryRecursive($ptr['ptr']);
                    $ptr['ptr'] = $ptr['ptr']? 
                        $this->getMerged($ptr['ptr'], $value, false):
                        $value;
                }
                $res = true;
            } else {
                throw new Ac_E_Registry(
                    $this, $origPath, count($origPath) - count($path), Ac_E_Registry::opSetRegistry, 
                    Ac_E_Registry::detailsWrongSegment($ptr['ptr'])
                );
            }
        }
        
        return $res;
    }
    
    function addRegistry($value, $keyOrPath = null, $_ = null) {
        $path = func_get_args();
        array_shift($path);
        $origPath = $path = self::flattenOnce($path);
        
        $found = self::arrayDive($this->registry, $path, $ptr);
        if (is_object($ptr['ptr']) && $ptr['ptr'] instanceof Ac_I_Registry) {
            $res = $ptr['ptr']->addRegistry($value, $path);
        } elseif (is_array($ptr['ptr'])) {
            while (count($path)) {
                $first = array_shift($path);
                $ptr['ptr'][$first] = array();
                $ptr['ptr'] = & $ptr['ptr'][$first];
            }
            $ptr['ptr'][] = $value;
            $kk = array_keys($ptr['ptr']);
            $res = array_pop($kk);
        } else {
            throw new Ac_E_Registry(
                $this, $origPath, count($origPath) - count($path), Ac_E_Registry::opAddRegistry, 
                Ac_E_Registry::detailsWrongSegment($ptr['ptr'])
            );
        }
        
        return $res;
    }
    
    function clearRegistry($keyOrPath = null, $_ = null) {
        $path = func_get_args();
        $path = self::flattenOnce($path);
        
        if (count($path)) {
            $last = array_pop($path);
            $found = self::arrayDive($this->registry, $path, $ptr);
            if (is_object($ptr['ptr']) && $ptr['ptr'] instanceof Ac_I_Registry) {
                array_push($path, $last);
                $ptr['ptr']->clearRegitry(array_merge($path, $last));
            } elseif ($found && is_array($ptr['ptr'])) {
                if (array_key_exists($ptr['ptr'][$last])) {
                    if (is_object($ptr['ptr'][$last]) && $ptr['ptr'][$last] instanceof Ac_I_Registry)
                        $ptr['ptr'][$last]->clearRegistry();
                    elseif (is_array($ptr['ptr'][$last])) {
                        $this->clearRegistryRecursive($ptr['ptr'][$last]);
                    }
                    else
                        unset($ptr['ptr'][$last]);
                }
            }
        } else {
            if (!is_array($this->registry)) $this->registry = array();
            else $this->clearRegistryRecursive($this->registry);
        }
    }
    
    protected function clearRegistryRecursive(array & $reg) {
        foreach ($reg as $k => & $v) {
            if (is_object($v) && $v instanceof Ac_I_Registry) 
                $v->clearRegistry();
            elseif (is_array($v)) {
                $this->clearRegistryRecursive($reg[$k]);
                if (!count($v)) unset($reg[$k]);
            } else {
                unset($reg[$k]);
            }
        }
    }
    
    function exportRegistry($recursive = false, $keyOrPath = null, $_ = null) {
        
        $path = func_get_args();
        array_shift($path);
        
        //$reg = $this->getRegistry($path);
        $found = $this->arrayDive($this->registry, $path, $ptr);
        
        if (!$found) {
            if (is_object($ptr['ptr']) && $ptr['ptr'] instanceof Ac_I_Registry && $this->checkRecurse($ptr['ptr'], $recursive)) {
                $res = $ptr['ptr']->exportRegistry($recursive, $path);
            }
        } else {
            
            $res = $ptr['ptr'];
            
            if ($recursive) {
                if (is_object($res) && $res instanceof Ac_I_Registry && $this->checkRecurse($res, $recursive)) {
                    $res = $res->exportRegistry($recursive);
                } elseif (is_array($res)) {
                    $res = $this->exportRegistryRecursive($res, $recursive);
                }
            }
            
        }
        
        return $res;
        
    }
    
    protected function exportRegistryRecursive(array $reg, $recurseOptions) {
        $res = array();
        foreach ($reg as $k => $v) {
            if (is_array($v)) $v = $this->exportRegistryRecursive ($v, $recurseOptions);
            elseif (is_object($v) && $v instanceof Ac_I_Registry && $this->checkRecurse($v, $recurseOptions))
                $v = $v->exportRegistry($recurseOptions);
            $res[$k] = $v;
        }
        return $res;
    }
    
    protected function checkRecurse($item, $recurseOptions) {
        $res = true;
        if (is_array($recurseOptions)){
            if (isset($recurseOptions[0]) && strlen($recurseOptions[0])) {
                $res = $item instanceof $recurseOptions[0];
            }
            if ($res && isset($recurseOptions[1]) && strlen($recurseOptions[1])) {
                $res = !($item instanceof $recurseOptions[1]);
            }
        }
        return $res;
    }

    function mergeRegistry($value, $preserveExistingValues = false, $keyOrPath = null, $_ = null) {
        
        $path = func_get_args();
        array_shift($path);
        array_shift($path);
        $origPath = $path = self::flattenOnce($path);
        
        $found = self::arrayDive($this->registry, $path, $ptr);
        if ($found) {
            $ptr['ptr'] = self::getMerged($ptr['ptr'], $value, $preserveExistingValues);
        } else {
            if (is_object($ptr['ptr']) && $ptr['ptr'] instanceof Ac_I_Registry) {
                $res = $ptr['ptr']->mergeRegistry($value, $preserveExistingValues, $path);
            } elseif (is_array($ptr['ptr'])) {
                while(count($path)) {
                    $first = array_shift($path);
                    $ptr['ptr'][$first] = array();
                    $ptr['ptr'] = & $ptr['ptr'][$first];
                }
                $ptr['ptr'] = $value;
                $res = true;
            } else {
                throw new Ac_E_Registry(
                    $this, $origPath, count($origPath) - count($path), Ac_E_Registry::opMergeRegistry, 
                    Ac_E_Registry::detailsWrongSegment($ptr['ptr'])
                );
            }
        }
        $res = true;
        return $res;
    }

    static function getMerged($value1, $value2, $preserveExistingValues, $clone = false) {

        if (is_object($value1) && $value1 instanceof Ac_I_Mergeable && $value1->isMergeableWith($value2)) {
            
            if ($clone) $res = $value1->getMergedWith($value2, $preserveExistingValues);
            else {
                $value1->mergeWith($value2, $preserveExistingValues);
                $res = $value1;
            }
            
        } elseif (is_object($value2) && $value2 instanceof Ac_I_RightMergeable && $value2->isRightMergeableWith($value1)) {

            $res = $value2->rightMergeWith($value1, $preserveExistingValues);
            
        } elseif (is_array($value1) && is_array($value2)) {

            /*if ($value1) {
                $res = self::getMerged(array(), $value1, $preserveExistingValues);
            } else {
                $res = array();
            }*/
            
            $res = $value1;
            
            foreach (array_keys($value2) as $i) {
                if (is_int($i)) array_push($res, $value2[$i]);
                elseif (array_key_exists($i, $res)) {
                    $res[$i] = self::getMerged($res[$i], $value2[$i], $preserveExistingValues, $clone);
                } else {
                    $res[$i] = $value2[$i];
                }
            }
            
        } else {
            
            $res = $preserveExistingValues? $value1 : $value2;
            
        }

        return $res;
    }
    
    /*
    public function current () {
        if (!is_null($this->position) && isset($this->registry[$this->position])) 
            return $this->registry[$this->position];
        else return null;
    }
    
    public function key () {
        return $this->position;
    }
    
    public function next () {
    }
    
    public function rewind () {
    }
    
    public function valid () {
    }
     */
    
}
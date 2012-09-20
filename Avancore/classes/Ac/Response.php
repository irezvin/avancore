<?php

class Ac_Response extends Ac_Registry implements Ac_I_WithOutput, Ac_I_Consolidated, Ac_I_EvaluatedObject, Ac_I_EvaluationContext {

    var $cacheConsolidated = true;
    
    function setCacheConsolidated($cacheConsolidated) {
        $this->cacheConsolidated = (bool) $cacheConsolidated;
    }
    
    function getCacheConsolidated() {
        return $this->cacheConsolidated;
    }
    
    // TODO: allow response to be partially cached
    function setCacheOptions($cacheOptions) {
        
    }
    
    function setContent($content) {
        $args = func_get_args();
        array_splice($args, 1, 0, array('content'));
        return call_user_func(array($this, 'setRegistry'), $args);
    }

    function getContent() {
        $args = func_get_args();
        array_splice($args, 0, 0, array('content'));
        return call_user_func(array($this, 'getRegistry'), $args);
    }    
    
    function setDebug($debug) {
        $args = func_get_args();
        array_splice($args, 1, 0, array('debug'));
        return call_user_func(array($this, 'setRegistry'), $args);
    }

    function getDebug() {
        $args = func_get_args();
        array_splice($args, 0, 0, array('debug'));
        return call_user_func(array($this, 'getRegistry'), $args);
    }    
    
    function output($callback = null) {
        $c = $this->getContent();
        if (is_object($c) && $c instanceof Ac_I_Withoutput) return $c->output($callback);
        elseif ($callback !== null) call_user_func($callback, $c);
        else {
            echo $c;
        }
    }
 
    /**
     * @return array(array($arrPath, $object), array($arrPath, $object), ...)
     */
    protected function findObjectsRecursive($class, array $haystack, array $path = array()) {
        $res = array();
        foreach ($haystack as $k => $v) {
            if (is_array($v)) 
                $res = array_merge($res, $this->findObjectsRecursive ($class, $v, array_merge($path, array($k))));
            elseif (is_object($v)) {
                if ($v instanceof $class) {
                    $res[] = array($v, array_merge($path, array($k)));
                }
                if ($v instanceof Ac_I_Registry && !($v instanceof Ac_I_Response)) {
                    $xp = $v->exportRegistry();
                    $res = array_merge($res, $this->findObjectsRecursive ($class, $xp, array_merge($path, array($k))));
                }
            }
        }
        return $res;
    }

    function getEvaluatedObjects() {
        $res = array();
        foreach ($this->findObjectsRecursive('Ac_I_Evaluated', $this->registry) as $v) {
            $res[Ac_Util::arrayToPath($v[0])] = $v[1];
        }
        return $res;
    }
    
    function getSubResponses() {
        $res = array();
        foreach ($this->findObjectsRecursive('Ac_Response', $this->registry) as $v) {
            $res[Ac_Util::arrayToPath($v[0])] = $v[1];
        }
        return $res;
    }
 
    /**
     * Supplementary function; is PUBLIC for testing purposes -- don't use
     * 
     * Deeply traverses $src while splitting it into chunks 
     * (uses a stack instead  of recursion)
     * 
     * Chunk is either
     * a) array part wthout any Ac_I_Consolidated
     * b) result array of Ac_I_Consolidated
     * 
     * See test case for the example.
     * 
     * 
     */
    static function sliceWithConsolidatedObjects(array & $src, $forCaching = false, array $extraArgs = array()) {
        $chunks = array(array());
        $dest = & $chunks[0];
        $stack = array();
        $path = array();
        $curr = & $src;
        reset($curr);
        $kv = each($curr);
        while ($kv !== false) {
            list ($key, $val) = $kv;
            $value = & $curr[$key];
            if (is_array($value)) {
                $stack[] = array('curr' => & $curr, 'dest' => & $dest);
                $path[] = $key;
                $curr = & $curr[$key];
                $dest[$key] = array();
                $dest = & $dest[$key];
                reset($curr);
            } elseif (is_object($value) && $value instanceof Ac_I_Consolidated) {
                $fullPath = array_merge($path, array($key));
                $gcArgs = array_merge(array($fullPath, $forCaching), $extraArgs);
                $consolidated = call_user_func_array(array($value, 'getConsolidated'), $gcArgs);
                if (is_array($consolidated)) {
                    $chunks[] = $consolidated;
                    $chunks[$c = count($chunks)] = array();
                    $currChunk = & $chunks[$c];
                    $tmp = null;
                    $dest = & $chunks[$c];
                    foreach ($path as $i => $seg) {
                        
                        //
                        // A BIG question: shall we put next line here or not?
                        // 
                        // -- behaviour must not be changed since all tests
                        // and referring code will be broken ---
                        // 
                        // If YES, upper-level items of source array
                        // will appear BEFORE deeper Consolidated chunks even those
                        // chunks are located if earlier keys
                        // 
                        // My current answer is 'NO', because it will let us
                        // to define control registries in outermost Consoludated
                        // that will be used to control merger of sub-items
                        // items.
                        //
                        
                        
                        //$stack[$i]['dest'] = & $dest;
                        
                        $dest[$seg] = array();
                        $dest = & $dest[$seg];
                    }
                } else {
                    $dest[$key] = $consolidated;
                }
            } else {
                $dest[$key] = $value;
            }
            while ((($kv = each($curr)) === false) && count($stack)) {
                $seg = array_pop($stack);
                array_pop($path);
                
                $curr = & $seg['curr'];
                $dest = & $seg['dest'];
            }
        }
        return $chunks;
    }
    
    function getConsolidated(array $path = array(), $forCaching = false, $_ = null) {
        
        if ($forCaching && !$this->getCacheConsolidated()) return $this;
        
        $exp = $this->exportRegistry();
        if (is_array($exp)) {
            $res = array();
            foreach(self::sliceWithConsolidatedObjects($exp, $forCaching) as $chunk) {
                $res = Ac_Registry::getMerged($res, $chunk, false);
            }
            return $res;
        } else {
            $res = $exp;
        }
        return $res;
    }
    
}
<?php

class Ac_Response extends Ac_Registry 
    implements Ac_I_WithOutput, Ac_I_Consolidated, 
        Ac_I_EvaluatedObject, Ac_I_EvaluationContext,
        Ac_I_StringObject, Ac_I_Prototyped {

    var $cacheConsolidated = true;
    
    protected $stringObjectMark = false;
    
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
        return call_user_func_array(array($this, 'setRegistry'), $args);
    }

    function getContent() {
        $args = func_get_args();
        array_splice($args, 0, 0, array('content'));
        return call_user_func_array(array($this, 'getRegistry'), $args);
    }    
    
    function setDebug($debug) {
        $args = func_get_args();
        array_splice($args, 1, 0, array('debug'));
        return call_user_func_array(array($this, 'setRegistry'), $args);
    }

    function getDebug() {
        $args = func_get_args();
        array_splice($args, 0, 0, array('debug'));
        return call_user_func_array(array($this, 'getRegistry'), $args);
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
    static function sliceWithConsolidatedObjects(array & $src, $forCaching = false, array $extraArgs = array(), array $targetPath = array(), $consolidate = true) {
        $chunks = array(array());
        $dest = & $chunks[0];
        if ($targetPath) {
            $ptr = array('ptr' => & $dest);
            self::arrayDive($dest, $targetPath, $ptr, true);
            $dest = & $ptr['ptr'];
        }
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
                
                if ($consolidate) {
                    
                    $fullPath = array_merge($path, array($key));
                    $gcArgs = array_merge(array($fullPath, $forCaching), $extraArgs);
                
                    $consolidated = call_user_func_array(array($value, 'getConsolidated'), $gcArgs);
                    
                    $put = is_array($consolidated);
                    
                } else {
                 
                    $consolidated = array();
                    
                    $fullPath = array_merge($path, array($key));
                    
                    $ptr = array('ptr' => & $consolidated);
                    
                    self::arrayDive($consolidated, $fullPath, $ptr, true);
                    $ptr['ptr'] = $value;
                    
                    $consolidated['__fullPath'] = array_merge($path, array($key));
                    $put = true;
                    
                }
                
                
                if ($put) {
                    $chunks[] = $consolidated;
                    $chunks[$c = count($chunks)] = array();
                    $currChunk = & $chunks[$c];
                    $tmp = null;
                    $dest = & $chunks[$c];
                    if ($targetPath) {
                        $ptr = array('ptr' => & $dest);
                        self::arrayDive($dest, $targetPath, $ptr, true);
                        $dest = & $ptr['ptr'];
                    }
                    foreach ($path as $i => $seg) {

                        /*
                         A BIG question: shall we put next line here or not?

                         -- behaviour must not be changed since all tests
                         and referring code will be broken ---

                         If YES, upper-level items of source array
                         will appear BEFORE deeper Consolidated chunks even those
                         chunks are located if earlier keys

                         My current answer is 'NO', because it will let us
                         to define control registries in outermost Consoludated
                         that will be used to control merger of sub-items
                         items.
                        */

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

        $args = func_get_args();
        
        //$exp = $this->exportRegistry();
        
        $exp = $this->exportRegistry(array(1 => 'Ac_I_Consolidated'));
        
        if (is_array($exp)) {
            /*
            $res = array();
            foreach(self::sliceWithConsolidatedObjects($exp, $forCaching) as $chunk) {
                $res = Ac_Registry::getMerged($res, $chunk, false);
            }*/
            
            $slices = self::sliceWithConsolidatedObjects($exp, $forCaching, array(), array(), false);
            
            $many = count($slices) > 1;
            
            $last = array_pop($slices);
            
            
            do {
                if (isset($this->deb)) echo "<table><tr>";
                if (!is_null($curr = array_pop($slices))) {
                    
                    if (isset($this->deb)) {
                        echo "<td>";
                        var_dump($curr);
                        echo "</td><td>";
                        var_dump($last);
                        echo "</td>";
                    }
                    $curr = Ac_Registry::getMerged($curr, $last, false);
                    $last = $curr;
                }
                if (isset($this->deb)) {
                    echo "<td>";
                    var_dump($last);
                    echo "</td>";
                }
                if (is_array($last) && isset($last['__fullPath'])) {
                    $ptr = array('ptr' => $last);
                    $fp = $last['__fullPath'];
                    $key = array_pop($fp);
                    self::arrayDive($last, $fp, $ptr);
                    $xp = $ptr['ptr'][$key];
                    if (is_object($xp) && $xp instanceof Ac_I_Consolidated) {
                        unset($ptr['ptr'][$key]);
                        $a = $args;
                        $a[0] = $last['__fullPath'];
                        $cons = call_user_func_array(array($xp, 'getConsolidated'), $a);
                        unset($last['__fullPath']);
                        $last = self::getMerged($cons, $last, false);
                        if (isset($this->deb)) {
                            echo "<td>";
                            var_dump($last);
                            echo "</td>";
                        }
                    }
                }
                if (isset($this->deb)) {
                    echo "</tr></table>";
                }
                //if (is_object($last) && $last instanceof Ac_I_Consolidated) $last = call_user_func_array(array($last, 'getConsolidated'), $args);
                //if ($many) var_dump($last);
                
            } while($slices);
            
            $res = $last;
            
            return $res;
        } else {
            $res = $exp;
        }
        return $res;
    }
    
    function __construct(array $options = array()) {
        parent::__construct();
        Ac_Accessor::setObjectProperty($this, $options);
        Ac_StringObject::onConstruct($this);
    }
    
    function hasPublicVars() {
        return false;
    }
    
    function __clone() {
        Ac_StringObject::onClone($this);
    }
    
    function __wakeup() {
        Ac_StringObject::onWakeup($this);
    }
 
    function setStringObjectMark($stringObjectMark) {
        $this->stringObjectMark = $stringObjectMark;
    }

    function getStringObjectMark() {
        return $this->stringObjectMark;
    }    
    
    function __toString() {
        return $this->stringObjectMark;
    }
    
}
<?php

class Ac_Registry_Consolidated extends Ac_Registry implements Ac_I_Consolidated, Ac_I_Prototyped {
    
    const svNone = 'svNone';
    const svFirst = 'svFirst';
    const svLast = 'svLast';

    protected $flatten = false;

    protected $keysort = false;

    protected $unique = false;

    protected $singleValue = self::svNone;

    protected $decorator = false;
    
    protected $implode = false;
    
    protected $reverse = false;
    
    protected $default = null;
    
    protected $toArray = true;
    
    function __construct(array $options = array()) {
        Ac_Accessor::setObjectProperty($this, $options);
    }
    
    function hasPublicVars() {
        return false;
    }

    function setToArray($toArray) {
        $this->toArray = (bool) $toArray;
    }

    function getToArray() {
        return $this->toArray;
    }    
    
    function setFlatten($flatten) {
        $this->flatten = (bool) $flatten;
    }

    function getFlatten() {
        return $this->flatten;
    }

    function setReverse($reverse) {
        $this->reverse = (bool) $reverse;
    }

    function getReverse() {
        return $this->reverse;
    }    

    function setKeysort($keysort) {
        $this->keysort = (bool) $keysort;
    }

    function getKeysort() {
        return $this->keysort;
    }

    function setUnique($unique) {
        $this->unique = (bool) $unique;
    }

    function getUnique() {
        return $this->unique;
    }
    
    function setDefault($default) {
        $this->default = $default;
    }

    function getDefault() {
        return $this->default;
    }    

    function setSingleValue($singleValue) {
        if (!in_array($singleValue, $a = array(self::svNone, self::svFirst, self::svLast)))
            throw Ac_E_InvalidCall::outOfSet ('singleValue', $singleValue, $a);
        $this->singleValue = $singleValue;
    }

    function getSingleValue() {
        return $this->singleValue;
    }

    function setDecorator($decorator) {
        $this->decorator = $decorator;
    }

    function getDecorator() {
        return $this->decorator;
    }
    
    function setImplode($implode) {
        $this->implode = $implode;
    }

    function getImplode() {
        return $this->implode;
    }    
    
    function getConsolidated(array $path = array(), $forCaching = false, $_ = null) {
        
        
        $reg = $this->exportRegistry(array(1 => 'Ac_I_Consolidated'));
        while (is_object($reg) && !($reg instanceof Ac_I_Consolidated) && ($reg instanceof Ac_Registry)) {
            $reg = $reg->exportRegistry();
        }
        
        if (is_array($reg)) {
            $cons = Ac_Response_Consolidated::sliceWithConsolidatedObjects($reg, $forCaching, array(), $path);
            $full = array();
            foreach ($cons as $con) {
                $full = Ac_Registry::getMerged($full, $con, false);
            }
            $ptr = array('ptr' => & $full);
            $newPath = $path;
            self::arrayDive($full, $newPath, $ptr, true);
            $res = & $ptr['ptr'];
        } elseif (is_object($reg) && $reg instanceof Ac_I_Consolidated) {
            
            $args = func_get_args();
            $full = call_user_func_array(array($reg, 'getConsolidated'), $args);
            
            $ptr = array('ptr' => & $full);
            
            $newPath = $path;
            self::arrayDive($full, $newPath, $ptr, true);
            $res = & $ptr['ptr'];
            
        } else {
            $full = array();
            $ptr = array('ptr' => & $full);
            $newPath = $path;
            self::arrayDive($full, $newPath, $ptr, true);
            $res = & $ptr['ptr'];
            $res = $reg;
        }
        
        //$res = $this->exportRegistry();
        
        if ($this->flatten) $res = Ac_Util::flattenArray ($res);
        if ($this->keysort) $res = ksort($res);
        if ($this->unique) {
            $res = array_unique($res);
        }
        if (is_array($res) && $this->reverse) $res = array_reverse($res);
        if ($this->singleValue !== self::svNone) {
            if (!count($res)) $res = null;
            elseif ($this->singleValue == self::svFirst) $res = array_shift($res);
            elseif ($this->singleValue == self::svLast) $res = array_pop($res);
        }
        if (is_array($res) && $this->implode !== false) 
            $res = Ac_Util::implodeRecursive($this->implode, $res);

        if ($this->toArray && !is_array($res)) $res = Ac_Util::toArray($res);
        
        if ($this->decorator)
            $res = Ac_Decorator::decorate ($this->decorator, $res, $this->decorator);
        
        if ($this->toArray) {
            if (is_array($this->default) && is_array($res) && !count($res)) $res = $this->default;
        } else {
            if (is_null($res)) $res = $this->default;
        }
        
        /*if (is_array($res) || !$this->toArray) {
            $tmp = array();
            Ac_Util::setArrayByPath($tmp, $path, $res);
            $res = $tmp;
        }*/
        
        /*if ($path === array('assetLibs')) {
            var_dump('***', $full, '***');
            var_dump(Ac_Debug_Log::getInstance()->getTrace());
        }*/
        
        return $full;
    }
    
}

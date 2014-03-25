<?php

class Ac_Mixin extends Ac_Prototyped implements Ac_I_Mixin {
    
    protected $mixables = array();
    
    /**
     * @param array $methodName => mixableId
     */
    protected $mixMethodMap = false;
    
    /**
     * @param array $propertyName => mixableId
     */
    protected $mixPropertyMap = false;
    
    protected function clearMixMaps() {
        $this->mixMethodMap = false;
        $this->mixPropertyMap = false;
    }
    
    public function addMixable(Ac_I_Mixable $mixable, $id = false, $canReplace = false) {
        $id = $mixable->getMixableId();
        if (is_numeric($id) || !strlen($id)) {
            $this->mixables[] = $mixable;
            end($this->mixables);
            $res = key($this->mixables);
        } else {
            if (isset($this->mixables[$id])) {
                if ($canReplace) $this->deleteMixable($id);
                else throw Ac_E_InvalidCall::alreadySuchItem('mixable', $id, 'deleteMixable');
            }
            $this->mixables[$id] = $mixable;
            $res = $id;
        }
        $mixable->registerMixin($this);
        $this->clearMixMaps();
        return $res;
    }

    public function deleteMixable($id, $dontThrow = false) {
        if (isset($this->mixables[$id])) {
            $tmp = $this->mixables[$id];
            unset($this->mixables[$id]);
            $tmp->unregisterMixin($this);
            $res = true;
            $this->clearMixMaps();
        } else {
            if ($dontThrow) $res = false;
                else throw Ac_E_InvalidCall::noSuchItem ('mixable', $id, 'listMixables');
        }
        return $res;
    }

    public function getMixable($id, $dontThrow = false) {
        if (isset($this->mixables[$id])) $res = $this->mixables;
        elseif ($dontThrow) $res = null;
        else throw Ac_E_InvalidCall::noSuchItem ('mixable', $id, 'listMixables');
    }

    public function getMixables($className = false) {
        if ($className === false) $res = $this->mixables;
        else {
            $res = array();
            foreach ($this->mixables as $id => $mix) 
                if ($mix instanceof $className) $res[$id] = $mix;
        }
        return $res;
    }

    public function listMixables($className = false) {
        if ($className === false) $res = array_keys($this->mixables);
        else {
            $res = array();
            foreach ($this->mixables as $id => $mix) 
                if ($mix instanceof $className) $res[] = $id;
        }
        return $res;
    }

    public function setMixables(array $mixables, $addToExisting = false) {
        $this->clearMixMaps();
        if (!$addToExisting && count($this->mixables)) {
            $tmp = $this->mixables;
            $this->mixables = array();
            foreach ($tmp as $mix) {
                $mix->unregisterMixin($this);
            }
        }
        foreach ($mixables as $k => $v) {
            if (!is_object($v)) $mix = Ac_Prototyped::factory($v, 'Ac_I_Mixable', $v);
                else $mix = $v;
            if (!$mix instanceof Ac_I_Mixable) throw Ac_E_InvalidCall("\$mixables['{$k}'] must implement "
            . "Ac_I_Mixable,  ".Ac_Util::typeClass ($mix)." provided instead");
            if (is_numeric($k)) $k = $mix->getMixableId();
            if (is_numeric($k) || !strlen($k)) array_push($this->mixables, $mix);
            else {
                if (isset($this->mixables[$k])) {
                    $tmp = $this->mixables[$k];
                    unset($this->mixables[$k]);
                    $tmp->unregisterMixin($this);
                }
                $this->mixables[$k] = $mix;
            }
            $mix->registerMixin($this);
        }
    }

    protected function fillMixMaps() {
        $mm = Ac_Util::getPublicMethods($this);
        foreach ($mm as $m) $this->mixMethodMap[$m] = false;
        foreach ($this->mixables as $id => $mix) {
            $nm = array_diff($mix->listMixinMethods(), $mm);
            foreach ($nm as $m) $this->mixMethodMap[$m] = $id;
            $mm = array_merge($mm, $nm);
        }
    }
    
    public function hasMethod($methodName) {
        if ($this->mixMethodMap === false) $this->fillMixMaps();
        
    }
    
    public function __get($name) {
        
    }
    
    public function __set($name, $value) {
        
    }
    
    public function __isset($name) {
        
    }
    
    public function __unset($name) {
        
    }
    
    public function __call($name, $arguments) {
        
    }
    
}
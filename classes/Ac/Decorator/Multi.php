<?php

class Ac_Decorator_Multi extends Ac_Decorator {
    
    protected $decorators = array();
    
    protected $data = array();
    
    protected $globalProps = array();
    
    /**
     * @return index of decorator (if it's already in the collection, it won't be added, but index will be returned)
     */
    function addDecorator(Ac_I_Decorator $decorator) {
        if (($index = $this->findDecorator($decorator)) === false) {
            $index = max(array_keys(($this->decorators)));
            $this->decorators[$index] = $decorator;
            if ($decorator instanceof Ac_I_Decorator_Model) 
                $decorator->setModel($this->model);
        }
        return $index;
    }
    
    function listDecorators() {
        return array_keys($this->decorators);
    }
    
    function getDecorators() {
        return $this->decorators;
    }
    
    /**
     * @return Ac_Model_Decorator
     */
    function getDecorator($index) {
        if (isset($this->decorators[$index])) $res = $this->decorators[$index];
            else throw new Exception("Decorator with index '{$index}' doesn't exist in decorators collection");
        return $res;
    }
    
    function removeDecorator($index) {
        if (isset($this->decorators[$index])) {
            unset($this->decorators[$index]);
            $res = true;
        } else $res = false;
        return $res;
    }
    
    function findDecorator(Ac_I_Decorator $decorator) {
        $res = array_search($decorator, $this->decorators);
        return $res;
    }
    
    function setDecorators(array $decorators) {
        $this->decorators = Ac_Prototyped::factoryCollection($decorators, 'Ac_I_Decorator', $this->globalProps);
        if ($this->model) {
            foreach ($this->decorators as $d) if ($d instanceof Ac_I_Decorator_Model) $d->setModel($this->model);
        }
        return $this->decorators;
    }
    
    protected function doOnSetModel() {
        foreach ($this->decorators as $d) {
            $d->setModel($this->model);
        }
    }
    
    function apply($value) {
        $res = $value;
        foreach ($this->decorators as $d) {
            $res = $d->apply($res);
        }
        return $res;
    }
    
    function setGlobalProps(array $globalProps) {
        $this->globalProps = $globalProps;
        $res = Ac_Accessor::setObjectProperty($this->decorators, $globalProps);
        return $res;
    }
    
    function getGlobalProps() {
        return $this->globalProps;
    }
    
}
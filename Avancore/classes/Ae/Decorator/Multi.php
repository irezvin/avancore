<?php

class Ae_Decorator_Multi extends Ae_Decorator {
    
    protected $decorators = array();
    
    protected $data = array();
    
    protected $globalProps = array();
    
    /**
     * @return index of decorator (if it's already in the collection, it won't be added, but index will be returned)
     */
    function addDecorator(Ae_I_Decorator $decorator) {
        if (($index = $this->findDecorator($decorator)) === false) {
            $index = max(array_keys(($this->decorators)));
            $this->decorators[$index] = $decorator;
            if ($decorator instanceof Ae_I_Decorator_Model) 
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
     * @return Ae_Model_Decorator
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
    
    function findDecorator(Ae_I_Decorator $decorator) {
        $res = array_search($decorator, $this->decorators);
        return $res;
    }
    
    function setDecorators(array $decorators) {
        $this->decorators = Ae_Autoparams::factoryCollection($decorators, 'Ae_I_Decorator', array_merge(array('model' => $this->model), $this->globalProps));
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
            $res = $d->apply($value);
        }
        return $res;
    }
    
    function setGlobalProps(array $globalProps) {
        $this->globalProps = $globalProps;
        $res = Ae_Autoparams::setObjectProperty($this->decorators, $globalProps);
        return $res;
    }
    
    function getGlobalProps() {
        return $this->globalProps;
    }
    
}
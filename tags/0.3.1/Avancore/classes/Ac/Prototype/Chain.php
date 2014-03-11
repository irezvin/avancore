<?php

class Ac_Prototype_Chain {

    protected $result = array();
    
    protected $builder = false;
    
    protected $depleted = false;
    
    function __construct(Ac_Prototype_Builder $builder = null) {
        $this->builder = $builder;
    }
    
    /**
     * @param type $chain
     * @return Ac_Prototype_Chain
     */
    static function getBuilder(Ac_Prototype_Chain $chain) {
        return $chain->builder;
    }
    
    /**
     * @return array
     */
    static function getResult(Ac_Prototype_Chain $chain) {
        if ($chain->rc) {
            throw new Ac_E_InvalidUsage("Ac_Prototype_Chain::getResult(\$chain) for depleted upstream chain is not allowed");
        }
        return $chain->result;
    }
    
    static function isDepleted(Ac_Prototype_Chain $chain) {
        return $chain->depleted;
    }
    
    /**
     * @return Ac_Prototype_Chain 
     */
    function __call($method, $args) {
        if (count($args) > 1) 
            $this->result[$method] = $args;
        else $this->result[$method] = $args[0];
        
        return $this;
    }
    
    function __set($key, $prototype) {
        if (!$this->builder) throw new Ac_E_InvalidUsage('Cannot assign prototype to Ac_Prototype_Chain that is not bound to \$builder');
        if ($this->result) {
            $this->builder->addDefault($this->result);
            $this->result = array();
            $this->depleted = true;
        }
        $this->builder->addPrototype($key, $prototype);
    }
}
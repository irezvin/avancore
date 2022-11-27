<?php

abstract class Ac_Model_Condition_AbstractCondition extends Ac_Prototyped implements Ac_I_Decorator {

    /**
     * @var Ac_Model_Condition_AbstractCondition
     */
    protected $parent = null;
    
    function hasPublicVars() {
        return true;
    }
    
    function setParent(Ac_Model_Condition_AbstractCondition $parent = null) {
        $this->parent = $parent;
    }
    
    /**
     * @return Ac_Model_Condition_AbstractCondition
     */
    function getParent() {
        return $this->parent;
    }
    
    /**
     * @return Ac_Model_Condition_AbstractCondition
     */
    function findParent($classOrInterface, $orSelf = false) {
        $curr = $orSelf? $this : $this->getParent();
        while ($curr) {
            if ($curr instanceof $classOrInterface) break;
            $curr = $curr->parent;
        }
        return $curr;
    }
    
    function apply($value) {
        return $this->test($value);
    }
    
    function __invoke($value) {
        return $this->test($value);
    }
    
    abstract function test($value);
    
}
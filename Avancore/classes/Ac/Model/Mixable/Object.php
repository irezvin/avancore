<?php

abstract class Ac_Model_Mixable_Object extends Ac_Mixable {

    /**
     * @var Ac_Model_Object
     */
    protected $mixin = false;
    
    protected $mixinClass = 'Ac_Model_Object';
    
    protected function listOwnProperties() {
        return array();
    }
    
    protected function listOwnFields() {
        return array();
    }
    
    protected function listOwnAssociations() {
        return array();
    }
    
    protected function listOwnAggregates() {
        return array();
    }
    
    

}
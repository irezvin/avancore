<?php

class ObjectMixable extends Ac_Model_Mixable_Object {

    var $events = array();
    
    var $name = false;
    
    protected function listOwnProperties() {
        return array('name');
    }
    
    protected function doAfterLoad() {
        $this->events[] = __FUNCTION__;
    }
    
    protected function doBeforeSave() {
        $this->events[] = __FUNCTION__;
        return false;
    }
    
    protected function doOnCanDelete() {
        $this->events[] = __FUNCTION__;
        return false;
    }
    
}
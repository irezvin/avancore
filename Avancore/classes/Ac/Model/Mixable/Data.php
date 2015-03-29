<?php

/**
 * Has same template methods as Ac_Model_Data
 */
class Ac_Model_Mixable_Data extends Ac_Mixable {
    
    /**
     * @var Ac_Model_Data
     */
    protected $mixin = false;
    
    protected $mixinClass = 'Ac_Model_Data';
    
    protected $errors = array();
    
    // --- to-be-overridden methods ---
    
    protected function getOwnPropertiesInfo() {
        return array();
    }
    
    protected function listOwnProperties() {
        return array();
    }
    
    protected function listOwnAssociations() {
        return array();
    }
    
    protected function listOwnAggregates() {
        return array();
    }
    
    protected function listOwnLists() {
        return array();
    }
    
    protected function listOwnFields() {
        return array();
    }
    
    protected function doOnBind(& $src, $ignore = false) {
    }
    
    protected function doOnCheck() {
    }
    
    // --- public handlers ---
    
    function onBind(& $src, $ignore) {
        $this->doOnBind($src, $ignore);
    }
    
    function onCheck(& $errors) {
        $this->errors = array();
        $this->doOnCheck();
        Ac_Util::ms($errors, $this->errors);
    }
    
    function onListLists(& $lists) {
        $lists = array_unique(array_merge($lists, $this->listOwnLists()));
    }
    
    function onListProperties(& $properties) {
        $properties = array_unique(array_merge($properties, $this->listOwnProperties()));
    }
    
    function onListAggregates(& $aggregates) {
        $aggregates = array_unique(array_merge($aggregates, $this->listOwnAggregates()));
    }
    
    function onGetPropertiesInfo(& $propertiesInfo) {
        Ac_Util::ms($propertiesInfo, $this->getOwnPropertiesInfo());
    }
    
    /**
     * this implementation assigns events "smart" way 
     * the events are applied only if an event handler method or method that
     * is called by the handler are overriden in a sub-class.
     * 
     * This is done to avoid excessive calls to a default (empty) implementations.
     */
    
    function listEventHandlerMethods() {
        $res = parent::listEventHandlerMethods();
        $over = array_flip(Ac_Util::isMethodOverridden(get_class($this), __CLASS__));
        foreach (array(
            'doOnBind' => 'onBind',
            'doOnCheck' => 'onCheck',
            'listOwnLists' => 'onListLists',
            'listOwnProperties' => 'onListProperties',
            'listOwnAggregates' => 'onListAggregates',
            'getOwnPropertiesInfo' => 'onGetPropertiesInfo',
        ) as $myMethod => $eventHandler) {
            if (!isset($over[$myMethod]) && !isset($over[$eventHandler])) {
                unset($res[$eventHandler]);
            }
        }
        return $res;
    }
    
    function registerMixin(Ac_I_Mixin $mixin) {
        parent::registerMixin($mixin);
        if ($this->mixin instanceof Ac_Model_Data) {
            $this->mixin->setMetaCacheMode(Ac_Model_Data::META_CACHE_NONE);
        }
    }
    
    
}
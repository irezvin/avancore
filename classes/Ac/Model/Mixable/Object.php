<?php

class Ac_Model_Mixable_Object extends Ac_Model_Mixable_Data {
    
    /**
     * @var Ac_Model_Object
     */
    protected $mixin = false;
    
    protected $mixinClass = 'Ac_Model_Object';
    
    /**
     * @retrun Ac_Application
     */
    function getApplication() {
        if ($this->mixin) $res = $this->mixin->getApplication();
            else $res = null;
        return $res;
    }
    
    function listNonMixedMethods() {
        return array_merge(parent::listNonMixedMethods(), array('getApplication'));
    }
    
    protected function doOnCreate() {
    }
        
    protected function listOwnDataProperties() {
        return array();
    }
    
    protected function doOnSetDefaults(array & $defaults, $full = false) {
    }

    protected function doAfterLoad() {
    }
 
    protected function doOnActual($reason = Ac_Model_Object::ACTUAL_REASON_LOAD) {
    }
    
    protected function doBeforeSave() {
    }
    
    protected function doAfterSave() {
    }
    
    protected function doOnSaveFailed() {
    }
    
    protected function doOnCanDelete() {
    }
    
    protected function doBeforeDelete() {
    }
    
    protected function doAfterDelete() {
    }
    
    protected function doOnDeleteFailed() {
    }
    
    protected function doOnCopy(Ac_Model_Object $copy) {
    }
    
    protected function doListNonCopiedFields() {
        return array();
    }
    
    protected function doOnExtraCompare(Ac_Model_Object $otherObject) {
    }
    
    protected function doListNonComparedFields() {
        return array();
    }
    
    protected function doListDefaultComparedAssociations() {
        return array();
    }
    
    protected function doOnGetAssociations(array & $associations) {
    }
    
    protected function doOnCleanup() {
    }
    
    // --- public functions ---
    
    function onCreate() {
        $this->doOnCreate();
    }
    
    function onListDataProperties(array & $dataProperties) {
        $dataProperties = array_unique(array_merge($dataProperties, $this->listOwnDataProperties()));
    }
    
    function onSetDefaults(array & $defaults, $full) {
        $this->doOnSetDefaults($defaults, $full);
    }
    
    function onAfterLoad() {
        $this->doAfterLoad();
    }
    
    function onActual($reason = Ac_Model_Object::ACTUAL_REASON_LOAD) {
        $this->doOnActual($reason);
    }
    
    function onBeforeSave(& $result) {
        if ($this->doBeforeSave() === false) $result = false;
    }
    
    function onAfterSave(& $result) {
        if ($this->doAfterSave() === false) $result = false;
    }
    
    function onSaveFailed() {
        $this->doOnSaveFailed();
    }
    
    function onCanDelete(& $result) {
        if ($this->doOnCanDelete() === false) $result = false;
    }
    
    function onBeforeDelete(& $result) {
        if ($this->doBeforeDelete() === false) $result = false;
    }
    
    function onAfterDelete() {
        $this->doAfterDelete();
    }
    
    function onDeleteFailed() {
        $this->doOnDeleteFailed();
    }
    
    function onCopy(Ac_Model_Object $copy) {
        $this->doOnCopy($copy);
    }
    
    function onListNonCopiedFields(array & $fields) {
        $fields = array_unique(array_merge($fields, $this->doListNonCopiedFields()));
    }
    
    function onCompare(Ac_Model_Object $other, & $compareResult) {
        if ($this->doOnExtraCompare($other) === false) $compareResult = false;
    }
    
    function onListNonComparedFields(array & $fields) {
        $fields = array_unique(array_merge($fields, $this->doListNonComparedFields()));
    }
    
    function onListDefaultComparedAssociations(array & $associations) {
        $fields = array_unique(array_merge($fields, $this->doListDefaultComparedAssociations()));
    }
    
    function onGetAssociations(array & $associations) {
        $this->doOnGetAssociations($associations);
    }
    
    function onCleanup() {
        $this->doOnCleanup();
    }
    
    // --- event handling ---
    
   function listEventHandlerMethods() {
        $res = parent::listEventHandlerMethods();
        $over = array_flip(Ac_Util::isMethodOverridden(get_class($this), __CLASS__));
        foreach (array(
            'doOnCreate' => 'onCreate',
            'listOwnDataProperties' => 'onListDataProperties',
            'doOnSetDefaults' => 'onSetDefaults',
            'doAfterLoad' => 'onAfterLoad',
            'doOnActual' => 'onActual',
            'doBeforeSave' => 'onBeforeSave',
            'doAfterSave' => 'onAfterSave',
            'doOnSaveFailed' => 'onSaveFailed',
            'doOnCanDelete' => 'onCanDelete',
            'doBeforeDelete' => 'onBeforeDelete',
            'doAfterDelete' => 'onAfterDelete',
            'doOnDeleteFailed' => 'onDeleteFailed',
            'doOnCopy' => 'onCopy',
            'doListNonCopiedFields' => 'onListNonCopiedFields',
            'doOnExtraCompare' => 'onCompare',
            'doListNonComparedFields' => 'onListNonComparedFields',
            'doListDefaultComparedAssociations' => 'onListDefaultComparedAssociations',
            'doOnGetAssociations' => 'onGetAssociations',
            'doOnCleanup' => 'onCleanup',
        ) as $myMethod => $eventHandler) {
            if (!isset($over[$myMethod]) && !isset($over[$eventHandler])) {
                unset($res[$eventHandler]);
            }
        }
        return $res;
    }
     
    
}
<?php

class Ac_Admin_Feature extends Ac_Prototyped {

    /**
     * Priority that is used to sort features in the manager 
     * @var int
     */
    var $order = 50;
    
    /**
     * @var Ac_Admin_Manager 
     */
    var $manager = false;
    
    /**
     * Whether this feature should NOT be applied event if it could
     * @var bool
     */
    var $disabled = false;
    
    var $usesSql = null;
    
    function hasPublicVars() {
        return true;
    }    

    
    /**
     * Should return true if this feature can be applied to current manager
     */
    function canBeApplied() {
        if ($this->disabled) $res = false;
            else $res = $this->doCanBeApplied() !== false;
        return $res;
    }
    
    /**
     * Template method to check if feature can be applied to current manager.
     */
    function doCanBeApplied() {
    }
    
    /**
     * Should return prototypes of columns that will be added to current manager
     */
    function getColumnSettings() {
        return array();
    }
    
    /**
     * This function is called by the manager when form settings are computed. 
     * The feature can modify current settings or add controls to the form configuration.
     *  
     * @param array $formSettings
     */
    function applyToFormSettings(& $formSettings) {
    }
    
    /**
     * This function is called by the manager when filter form settings are computed. 
     * The feature can modify current settings or add controls to the filter form configuration.
     *  
     * @param array $formSettings
     */
    function applyToFilterFormSettings(& $filterFormSettings) {
    }
    
    /**
     * This function is called by the manager immediately after the form is created.
     * The feature can perform necessary tweaks with the form.
     *  
     * @param Ac_Form $form
     */
    function applyToForm($form) {
    }
    
    /**
     * This function is called by the manager immediately after the filter form is created.
     * The feature can perform necessary tweaks with the filter form.
     *  
     * @param Ac_Form $filterForm
     */
    function applyToFilterForm($filterForm) {
    }
    
    /**
     * Should return array with either Ac_Admin_Action objects or with prototypes 
     *
     * @return unknown
     */
    function getActions() {
        return array();
    }
    
    function getProcessings() {
        return array();
    }
    
    function getPreloadRelations() {
        return array();
    }
    
    function getSubManagersConfig() {
        return array();
    }
    
    /**
     * @param string $id
     * @param Ac_Admin_Manager $subManager 
     * @param array $smConfig Configuration that was used to create subManager
     */
    function onSubManagerCreated($id, $subManager, $smConfig = array()) {
    }
    
    function onGetQueryAndSort(array &$query, & $sort, $filterFormData, array $searchCrit, array $sortCrit) {
    }
    
    function onBeforeCreateCollection(array & $collectionPrototype = array()) {
    }
    
    function onCollectionCreated(Ac_Model_Collection_Abstract $collection) {
    }
    
    function onLoad($record) {
    }
    
    function onCreate($record) {
    }
     
    function onBind($record) {
    }
    
    function getSearchSettings() {
        return array();
    }
    
    // ------ sql-based features which may not be supported by all storages  ------
    
    function usesSqlSelect() {
        if ($this->usesSql === null) {
            $this->usesSql = $this->getSqlSelectSettings() || $this->getOrderPrototypes() || $this->getFilterPrototypes();
        }
        return $this->usesSql;
    }
    
    function getSqlSelectSettings() {
        return array();
    }
    
    function onCreateSqlSelect(Ac_Sql_Select $select) {
    }
    
    function getOrderPrototypes() {
        return array();
    }
    
    function getFilterPrototypes() {
        return array();
    }
    
}


<?php

class Ae_Admin_Feature {

    /**
     * Priority that is used to sort features in the manager 
     * @var int
     */
    var $priority = 50;
    
    /**
     * @var Ae_Admin_Manager 
     */
    var $manager = false;
    
    /**
     * Whether this feature should NOT be applied event if it could
     * @var bool
     */
    var $disabled = false;
    
    /**
     * @param Ae_Admin_Manager $manager
     * @param array $options extra settings of the feature
     * @return Ae_Admin_Feature
     */
    function Ae_Admin_Feature (& $manager, $options = array()) {
        if (!is_a($manager, 'Ae_Admin_Manager'))
            trigger_error ('$manager must be instance of Ae_Admin_Manager', E_USER_ERROR);
        Ae_Util::simpleBind($options, $this);
        $this->manager = & $manager;
    }
    
    /**
     * Should return true if this feature can be applied to current manager
     */
    function canBeApplied() {
        if ($this->disabled) $res = false;
            else $res = $this->doCanBeApplied();
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
     * @param Ae_Form $form
     */
    function applyToForm(& $form) {
    }
    
    /**
     * This function is called by the manager immediately after the filter form is created.
     * The feature can perform necessary tweaks with the filter form.
     *  
     * @param Ae_Form $filterForm
     */
    function applyToFilterForm(& $filterForm) {
    }
    
    function getOrderPrototypes() {
        return array();
    }
    
    function getFilterPrototypes() {
        return array();
    }
    
    function getSqlSelectSettings() {
        return array();
    }
    
    /**
     * Should return array with either Ae_Admin_Action objects or with prototypes 
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
     * @param Ae_Admin_Manager $subManager 
     * @param array $smConfig Configuration that was used to create subManager
     */
    function onSubManagerCreated($id, & $subManager, $smConfig = array()) {
    }
    
    function onCollectionCreated(Ae_Model_Collection $collection) {
    }
     
}

?>
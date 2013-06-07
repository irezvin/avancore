<?php

// Replaces some functions of Ac_Legacy_Adapter since it does not exist anymore

class Ac_Admin_ManagerConfigService {

    protected $toolbarImagesMap = false;
    
    /**
     * @var Ac_Application
     */
    protected $application = false;
    
    protected function getDefaultToolbarImagesMap() {
        return array(
            'new' => array(
                'image' => 'new_f2.png', 
                'disabledImage' => 'new.png',
            ), 
            'edit' => array(
                'image' => 'edit_f2.png', 
                'disabledImage' => 'edit.png',
            ), 
            'delete' => array(
                'image' => 'delete_f2.png', 
                'disabledImage' => 'delete.png',
            ), 
            'apply' => array(
                'image' => 'apply_f2.png', 
                'disabledImage' => 'apply.png',
            ), 
            'save' => array(
                'image' => 'save_f2.png', 
                'disabledImage' => 'save.png',
            ), 
            'saveAndAdd' => array(
                'image' => 'save_f2.png', 
                'disabledImage' => 'save.png',
            ), 
            'cancel' => array(
                'image' => 'cancel_f2.png', 
                'disabledImage' => 'cancel.png',
            ), 
        );
    }
    
    function __construct(Ac_Application $application = null) {
        if ($application) $this->setApplication($application);
    }
    
    function getToolbarImagesMap($forKind = false) {
    
        if ($this->toolbarImagesMap === false) {
            $this->toolbarImagesMap = $this->getDefaultToolbarImagesMap();
            if (isset($this->config->toolbarImagesMap) && is_array($this->config->toolbarImagesMap)) {
                Ac_Util::ms($this->toolbarImagesMap, $this->config->toolbarImagesMap);
            }
        }
        if ($forKind !== false) return isset($this->toolbarImagesMap[$forKind])? $this->toolbarImagesMap[$forKind] : array();
        return $this->toolbarImagesMap;
    }
  
    function getDefaultImagePrefix() {
        return 'images';
    }
    
    
    
    function getImagePrefix() {
        $imagePrefix = $this->getDefaultImagePrefix();
        $conf = Ac_Dispatcher::getInstance()->config;
        if (isset($conf->managerImagesUrl) && strlen($u = $conf->managerImagesUrl)) {
            $imagePrefix = $conf->managerImagesUrl;
        }
        $imagePrefix = rtrim($imagePrefix, '/').'/';
        return $imagePrefix;
    }
    
    function getManagerJsAssets() {
        return array(
            '{AC}/util.js',
            '{AC}/avanManager.js',
            '{AC}/managerRenderer.js',
        );
    }

    function setApplication(Ac_Application $application) {
        $this->application = $application;
    }

    /**
     * @return Ac_Application
     */
    function getApplication() {
        return $this->application;
    }    
    
    function showToolbarHeader($toolbarHeader) {
        $res = false;
        if (class_exists('JToolBarHelper')) {
            if (strlen($toolbarHeader)) JToolBarHelper::title($toolbarHeader);
            $res = true;
        }
        return $res;
    }
    
}
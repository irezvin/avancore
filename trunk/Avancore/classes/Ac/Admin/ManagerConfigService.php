<?php

// Replaces some functions of Ac_Legacy_Adapter since it does not exist anymore

class Ac_Admin_ManagerConfigService {

    protected $toolbarImagesMap = false;
    
    /**
     * @var Ac_Application
     */
    protected $application = false;
    
    protected $toolbarImagePlaceholder = '{TOOLBAR}';
    
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
            'publish' => array(
                'image' => 'publish_f2.png', 
                'disabledImage' => 'publish.png',
            ), 
            'unpublish' => array(
                'image' => 'unpublish_f2.png', 
                'disabledImage' => 'unpublish.png',
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
        if ($application !== ($oldApplication = $this->application)) {
            $this->application = $application;
            $this->setToolbarImagePlaceholder($this->toolbarImagePlaceholder, true);
        }
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
    
    function setToolbarImagePlaceholder($toolbarImagePlaceholder, $force = false) {
        
        if ($toolbarImagePlaceholder !== ($oldToolbarImagePlaceholder = $this->toolbarImagePlaceholder) || $force) {
            $this->toolbarImagePlaceholder = $toolbarImagePlaceholder;
            if ($a = $this->application) {
                $p = $a->getExtraAssetPlaceholders();
                if (isset($p[$oldToolbarImagePlaceholder])) unset($p[$oldToolbarImagePlaceholder]);
                if ($toolbarImagePlaceholder !== false) {
                    $p[$h = $this->getToolbarImagePlaceholder(true)] = $this->getImagePrefix();
                    $a->setExtraAssetPlaceholders($p);
                }
            }
        }
    }

    function getToolbarImagePlaceholder($expandAppId = false) {
        $res = $this->toolbarImagePlaceholder;
        if ($expandAppId) {
            $a = $this->application;
            if ($a) {
                $res = sprintf($res, strtoupper(get_class($a)));
            }
        }
        return $res;
    }    
    
   
}
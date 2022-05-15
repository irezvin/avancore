<?php

// Replaces some functions of Ac_Legacy_Adapter since it does not exist anymore

class Ac_Admin_ManagerConfigService implements Ac_I_ApplicationComponent {

    use Ac_Compat_Overloader;
    
    protected static $_compat_application = 'app';
    protected static $_compat_setApplication = 'setApp';
    protected static $_compat_getApplication = 'getApp';
    
    protected $toolbarImagesMap = false;
    
    /**
     * @var Ac_Application
     */
    protected $app = false;
    
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
    
    function __construct(Ac_Application $app = null) {
        if ($app) $this->setApp($app);
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
        $managerImagesUrl = $this->getApp()->getAdapter()->getConfigValue('managerImagesUrl');
        if (!is_null($managerImagesUrl)) {
            $imagePrefix = $managerImagesUrl;
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

    function setApp(Ac_Application $app) {
        if ($app !== ($oldApp = $this->app)) {
            $this->app = $app;
            $this->setToolbarImagePlaceholder($this->toolbarImagePlaceholder, true);
        }
    }

    /**
     * @return Ac_Application
     */
    function getApp() {
        return $this->app;
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
            if ($a = $this->app) {
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
            $a = $this->app;
            if ($a) {
                $res = sprintf($res, strtoupper(get_class($a)));
            }
        }
        return $res;
    }    
    
   
}
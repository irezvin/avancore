<?php

class Ac_Cg_Layout_Joomla extends Ac_Cg_Layout {
    
    var $pathAdminComponent = 'administrator/components/com_{app}';
    var $pathComponent = 'components/com_{app}';
    
    var $pathApp = '{pathComponent}';

    var $pathClasses = '{pathApp}/classes';
    var $pathGen = '{pathApp}/gen';
    var $pathVarTmp = 'tmp/avancore/tmp';
    var $pathVarCode = 'tmp/avancore/code';
    var $pathVarLog = 'logs/avancore';
    var $pathVarCache = 'cache/avancore';
    var $pathVarFlags = 'tmp/avancore/flags';
    var $pathConfig = '{pathApp}/config';
    var $pathAssets = 'media/com_{app}';
    var $pathVendor = '{pathApp}/vendor';
    var $pathAvancore = 'libraries/avancore';
    var $pathAvancoreAssets = '../../media/avancore';
    var $pathCodegenWeb = '{pathApp}/codegen';
    var $pathBootstrap = '{pathApp}';
    
    protected $surePathApp = false;
    
    function isJoomla($dir) {
        $res = $this->detectDirsOrFiles($dir, array(
            'configuration.php',
            'administrator/components',
            'components',
            'libraries/joomla'
        ));
        return $res;
    }
    
    protected function detectWithAppName($dir, $setToFound = false, array &$foundItems = array()) {
        // locate layout
        $tmp = clone $this;
        $tmp->surePathApp = true;
        $tmp->pathApp = '{pathComponent}';
        if ($tmp->detect($dir)) {
            $this->pathApp = $tmp->pathApp;
            return parent::detect($dir, $setToFound, $foundItems);
        }
        $tmp = clone $this;
        $tmp->surePathApp = true;
        $tmp->pathApp = '{pathAdminComponent}';
        if ($tmp->detect($dir)) {
            $this->pathApp = '{pathAdminComponent}';
            $res = parent::detect($dir, $setToFound, $foundItems);
        }
        return $res;
    }
    
    protected function scanJoomlaComponentDirs($dir, $setToFound = false, array &$foundItems = array()) {
        // list components
        $compDirs = array_merge(
            glob($dir.'/components/com_*'),
            glob($dir.'/administrator/components/com_*')
        );
        $possibleApps = array();
        foreach ($compDirs as $compDir) if (is_dir($compDir)) {
            if (is_dir($compDir.'/classes') && is_dir($compDir.'/gen')) {
                $possibleApps[] = preg_replace('/^com_/', '', basename($compDir));
            }
        }
        $possibleApps = array_unique($possibleApps);
        foreach ($possibleApps as $name) {
            $tmp = clone $this;
            $tmp->appName = $name;
            if ($tmp->detect($dir)) {
                if (!strlen($this->appName)) {
                    $this->appName = $name;
                    $res = $this->detect($dir, $setToFound, $foundItems);
                }
                $this->foundApps[] = $name;
            }
        }
        return $res;
    }
    
    function detect($dir, $setToFound = false, array &$foundItems = array()) {
        
        // we run detection from Joomla root only
        if (!$this->isJoomla($dir)) return false;
        
        // if we already know app path, parent detection method should work
        if ($this->surePathApp) {
            return parent::detect($dir, $setToFound, $foundItems);
        }
        
        $foundItems = array();
        
        if (strlen($this->appName)) { // appName provided
            return $this->detectWithAppName($dir, $setToFound, $foundItems);
        }
        
        return $this->scanJoomlaComponentDirs($dir, $setToFound, $foundItems);
    }
    
    function isRecommended($dir) {
        $res = $this->isJoomla($dir);
        return $res;
    }
    
    function getAppType() {
        return 'joomla';
    }
    
    protected function doGetSkelPrototype() {
        return array('class' => 'Ac_Cg_Template_Skel_Joomla');
    }

    protected function detectAppName($dir) {
        return false;
    }
    
    function hasDefaultCopyTarget() {
        return true;
    }

}
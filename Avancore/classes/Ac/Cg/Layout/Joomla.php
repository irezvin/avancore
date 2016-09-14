<?php

class Ac_Cg_Layout_Joomla extends Ac_Cg_Layout {
    
    var $pathAdminComponent = 'administrator/components/com_{app}';
    var $pathComponent = 'components/com_{app}';
    
    var $pathApp = '{pathComponent}';

    var $pathClasses = '{pathApp}/classes';
    var $pathGen = '{pathApp}/gen';
    var $pathVarTmp = 'tmp/avancore/tmp';
    var $pathVarCode = 'tmp/avancore/code';
    var $pathVarLog = 'log/avancore';
    var $pathVarCache = 'cache/avancore';
    var $pathVarFlags = 'tmp/avancore/flags';
    var $pathConfig = '{pathApp}/config';
    var $pathAssets = 'media/com_{app}';
    var $pathVendor = '{pathApp}/vendor';
    var $pathAvancore = 'libraries/avancore';
    var $pathAvancoreAssets = '../../media/avancore';
    var $pathCodegenWeb = 'administrator/components/com_{app}/codegen';
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
    
    function detect($dir, $setToFound = false, array &$foundItems = array()) {
        $res = false;
        $foundItems = array();
        if ($this->isJoomla($dir)) {
            if ($this->surePathApp) {
                $res = parent::detect($dir, $setToFound, $foundItems);
            } elseif (strlen($this->appName)) { // appName provided
                // locate layout
                $tmp = clone $this;
                $tmp->surePathApp = true;
                $tmp->pathApp = '{pathComponent}';
                if ($tmp->detect($dir)) {
                    $this->pathApp = $tmp->pathApp;
                    $res = parent::detect($dir, $setToFound, $foundItems);
                } else {
                    $tmp = clone $this;
                    $tmp->surePathApp = true;
                    $tmp->pathApp = '{pathAdminComponent}';
                    if ($tmp->detect($dir)) {
                        $this->pathApp = '{pathAdminComponent}';
                        $res = parent::detect($dir, $setToFound, $foundItems);
                    }
                }
            } else {
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
            }
        }
        return $res;
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
    
    function getCliInfo() {
        return array('foundApps' => $this->foundApps);
    }
    
    function hasDefaultCopyTarget() {
        return true;
    }

}
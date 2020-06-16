<?php

abstract class Ac_Cg_Layout extends Ac_Prototyped {

    var $foundApps = array();

    var $appName = false;
    
    var $detectedAppName = false;
    
    var $pathRoot = '';
    
    var $pathClasses = 'classes';
    var $pathGen = 'gen';
    var $pathVarTmp = 'var/tmp';
    var $pathVarCode = 'var/code';
    var $pathVarLog = 'var/log';
    var $pathVarCache = 'var/cache';
    var $pathVarFlags = 'var/flags';
    var $pathConfig = 'config';
    var $pathAssets = 'web/assets';
    var $pathVendor = 'vendor';
    var $pathAvancore = 'vendor/Avancore';
    var $pathAvancoreAssets = '../../web/avancore';
    var $pathCodegenWeb = 'web/codegen';
    var $pathBootstrap = '';

    function reset() {
        $this->appName = false;
        $cv = get_class_vars(get_class($this));
        foreach ($cv as $k => $v) {
            if (!strncmp($k, 'path', 4)) {
                $this->$k = $v;
            }
        }
    }
    
    function findCodegenConfig() {
        $res = false;
        $a = $this->getPathVar('pathConfig', true, true);
        if ($a !== false && is_file($f = $a.'/codegen.config.php')) {
            $res = $f;
        }
        if ($res === false) {
            $a = $this->getPathVar('pathCodegenWeb', true, true);
            if ($a !== false && is_file($f = $a.'/codegen.config.php')) {
                $res = $f;
            }
        }
        return $res;
    }
    
    function getPathVar($name, $relativeToDir = false, $shouldExist = false, $expandPlaceholders = true) {
        $pv = $this->getPathVars($relativeToDir, $expandPlaceholders);
        if (isset($pv[$name])) {
            $res = $pv[$name];
            if ($shouldExist && !is_dir($res)) $res = false;
        } else {
            throw new Exception("Unknown path var '{$name}'");
        }
        return $res;
    }
    
    function getMapTr() {
        $res = array();
        foreach ($this->getPathVars(false, false) as $k => $v) {
            $res['{'.$k.'}/'] = strlen($v)? $v.'/' : '';
            $res['{'.$k.'}'] = $v;
        }
        if (strlen($this->appName)) {
            $res['{APP}'] = strtoupper($this->appName);
            $res['{app}'] = strtolower($this->appName);
            $res['{App}'] = ucfirst($this->appName);
        }
        return $res;
    }
    
    function expandPlaceholders($subject, $tr) {
        if (is_array($subject)) {
            $new = array();
            foreach ($subject as $k => $v) $new[$k] = $this->expandPlaceholders($v, $tr);
            $subject = $new;
        } else {
            $max = 5;
            for ($i = 0; $i < $max; $i++) {
                $old = $subject;
                $subject = strtr($subject, $tr);
                if ($subject === $old) break;
            }
            if ($i == 5) throw new Exception("Cannot expand placeholders in '{$max}' iterations");
        }
        return $subject;
    }
    
    function getPathVars($relativeToDir = false, $expandPlaceholders = true) {
        if ($relativeToDir === true) $relativeToDir = $this->pathRoot;
        $res = array();
        foreach (get_object_vars($this) as $k => $v) {
            if (!strncmp($k, 'path', 4)) {
                if ($relativeToDir !== false) $v = rtrim($relativeToDir, '/').'/'.ltrim($v, '/');
                $res[$k] = $v;
            }
        }
        if ($expandPlaceholders) {
            $res = $this->expandPlaceholders($res, $this->getMapTr());
        }
        return $res;
    }

    function listDetectPaths() {
        return array('pathClasses', 'pathGen');
    }
    
    /**
     * @return array
     * Array members are alternatives (i.e. array('config', 'deploy'))
     * 
     */
    function getDetectDirs($dir = false) {
        $res = array_intersect_key($this->getPathVars($dir), array_flip($this->listDetectPaths()));
        $res['pathConfig'] = $this->expandPlaceholders(array($this->pathConfig, str_replace('config', 'deploy', $this->pathConfig)), $this->getMapTr());
        return $res;
    }
    
    /**
     * Removes $dir + "/" prefix from each item of $paths, returns resulting array
     */
    function deprefixize($dir, array $paths) {
        if ($dir === true) $dir = $this->pathRoot;
        $res = array();
        $dir = rtrim($dir, '/').'/';
        $l = strlen($dir);
        foreach ($paths as $k => $path) {
            if (!strncmp($dir, $path, $l)) $path = substr($path, $l);
            $res[$k] = $path;
        }
        return $res;
    }
    
    function isRecommended($dir) {
        return false;
    }
    
    function detectDirsOrFiles($dir, array $detectItems, array & $foundItems = array()) {
        $res = true;
        $foundItems = array();
        if (!strlen($dir)) $dir = '.';
        foreach ($detectItems as $k => $v) {
            $found = false;
            $v = Ac_Util::toArray($v);
            foreach ($v as $item) {
                $item = rtrim($dir, '/').'/'.ltrim($item, '/');
                $found = is_dir($item);
                if (!$found) {
                    // assume item with the dot is the file
                    if (strpos(basename($item), '.') !== false) {
                        $found = is_file($item);
                    }
                }
                if ($found) {
                    $foundItems[$k] = $item;
                    break;
                }
            }
            if (!$found) {
                $res = false;
                // We're not exiting the loop to populate $foundItems
            }
        }
        return $res;
    }
    
    protected function detectManifests($dir, $setToFound = false, array & $foundItems = array()) {
        $jsonFiles = glob($dir."/*.avancore.json");
        foreach ($jsonFiles as $file) {
            $json = json_decode(file_get_contents($file), true);
            if (!$json || !is_array($json)) {
                trigger_error ("Cannot parse JSON in '{$file}'`", E_USER_WARNING);
                continue;
            }
            if (isset($json['pathRoot'])) {
                $appName = preg_replace("/\.avancore\.json$/", "", ltrim(basename($file)));
                if (isset($json['name'])) $appName = $json['name'];
                $appRoot = rtrim($dir, "/")."/".ltrim($json['pathRoot'], "/");
                $layout = clone $this; // @TODO: ability to specify layout class/options in JSON
                $layout->appName = $appName;
                if ($layout->detect($appRoot, $setToFound, $foundItems)) {
                    //Ac_Debug::ddd($appRoot, $setToFound, $foundItems);
                    $this->appName = $appName;
                    return $this->detect($appRoot, $setToFound, $foundItems);
                }
            }
        }
    }
    
    /**
     * Items with dots in the names may be the files
     * @param bool $setToFound In case of positive result, set local $path{Name} vars to found alternatives
     * @return bool
     */
    function detect($dir, $setToFound = false, array & $foundItems = array()) {
        if ($res = $this->detectManifests($dir, $setToFound, $foundItems)) {
            return $res;
        }
        if (!$res) {
            $detectDirs = $this->getDetectDirs();
            $res = $this->detectDirsOrFiles($dir, $detectDirs, $foundItems) || $res;
            if ($res) {
                $this->detectedAppName = $this->detectAppName($dir);
            }
        }
        if ($res && $setToFound) {
            if (!strlen($this->appName)) $this->appName = $this->detectedAppName;
            $dp = $this->deprefixize($dir, $foundItems);
            foreach (array_intersect_key($dp, $this->getPathVars()) as $k => $v) {
                $this->$k = $v;
            }
            $this->pathRoot = $dir;
        }
        return $res;
    }
    
    /**
     * In case when different applications are located in $dir, 
     * returns a list with an alternative layouts
     * 
     * @return Ac_Cg_Layout[]
     */
    function getAltLayouts($dir) {
        return array();
    }
    
    function getSkelPrototype() {
        $res = $this->doGetSkelPrototype();
        if (!isset($res['appName'])) $res['appName'] = $this->appName;
        if (!isset($res['layout'])) $res['layout'] = $this;
        return $res;
    }
    
    abstract function getAppType();
    
    abstract protected function doGetSkelPrototype();
    
    abstract protected function detectAppName($dir);
    
    function getCliInfo() {
        if ($this->foundApps) return ['foundApps' => $this->foundApps];
        else return [];
    }
    
    function hasDefaultCopyTarget() {
        return false;
    }
    
    
    
}

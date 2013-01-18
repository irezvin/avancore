<?php

class Ac_Legacy_Adapter_Joomla15 extends Ac_Legacy_Adapter_Joomla {
    
    var $configClass = 'Ac_Legacy_Config_Joomla15';
    
    function Ac_Legacy_Adapter_Joomla15($extraSettings = array()) {
        
        parent::Ac_Legacy_Adapter($extraSettings);
        
        $dbSettings = $this->dbSettings === false? array(
                'db' => $this->config->getNative('db'),
                'host' => $this->config->getNative('host'),
                'user' => $this->config->getNative('user'),
                'password' => $this->config->getNative('password'),
                'prefix' => $this->config->getNative('dbprefix'),
        ) : $this->dbSettings;
        
        $dbSettings['config'] = $this->config;
        
        if ($this->useNativeDatabase) {
            $this->database = new Ac_Legacy_Database_Native($dbSettings);
        } else {
            if ($this->dbClass) {
                $dbc = $this->dbClass;
                $this->database = new $dbc($dbSettings); 
            } else {
                $this->database = new Ac_Legacy_Database_Joomla15(array('config' => & $this->config));
            }
        }
        
        $session = JFactory::getSession();
        $josUser = $session->get('user');
        $this->_user = new Ac_Legacy_User_Joomla($josUser);
        
        $this->userstate = new Ac_Joomla_Userstate();

    }
    
    function registerClassesForJoomlaLoader($directories, $useCache = true, $returnListOnly = false) {
        if (!is_array($directories)) $directories = array($directories);
        $classes = array();
        if ($useCache) {
            $disp = Ac_Dispatcher::getInstance();
            $classes = $disp->cacheGet($cacheKey = md5(implode(PATH_SEPARATOR, $directories)), __FUNCTION__);
            if (strlen($classes)) $classes = unserialize($classes);
        }
        if (!is_array($classes) || !count($classes)) {
            $classes = array();
            foreach ($directories as $dir) {
                $dir = rtrim($dir, DIRECTORY_SEPARATOR);
                if (!strcmp($foo = substr($dir, strlen($dir) - 7), 'classes')) {
                    $base = $dir;
                } else {
                    $base = $dir.DIRECTORY_SEPARATOR.'classes';
                }
                $files = Ac_Util::listDirContents($base, true, array(), '/\\.php$/');
                foreach ($files as $c) {
                    $c = substr($c, strlen($base) + 1);
                    $className = str_replace('/', '_', str_replace(".php", "", $c));
                    $className = str_replace('\\', '_', $className);
                    $classes[$className] = $base.DIRECTORY_SEPARATOR.$c;
                }
            }
        }
        if ($useCache) {
            $disp->cacheSet($cacheKey, serialize($classes), __FUNCTION__);
        }
        if (!$returnListOnly) {
            if (class_exists('JLoader')) {
                foreach($classes as $class => $file) JLoader::register($class, $file);
            }
        }
        return $classes;
    }
    
}
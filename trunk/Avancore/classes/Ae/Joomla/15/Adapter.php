<?php

Ae_Dispatcher::loadClass('Ae_Joomla_Adapter');

class Ae_Joomla_15_Adapter extends Ae_Joomla_Adapter {
    
    var $configClass = 'Ae_Joomla_15_Config';
    
    function Ae_Joomla_15_Adapter($extraSettings = array()) {
        
        parent::Ae_Adapter($extraSettings);
        
        $dbSettings = $this->dbSettings === false? array(
                'db' => $this->config->getNative('db'),
                'host' => $this->config->getNative('host'),
                'user' => $this->config->getNative('user'),
                'password' => $this->config->getNative('password'),
                'prefix' => $this->config->getNative('dbprefix'),
        ) : $this->dbSettings;
        
        $dbSettings['config'] = & $this->config;
        
        if ($this->useNativeDatabase) {
            Ae_Dispatcher::loadClass('Ae_Native_Database');
            $this->database = new Ae_Native_Database($dbSettings);
        } else {
            if ($this->dbClass) {
                Ae_Dispatcher::loadClass($this->dbClass);
                $dbc = $this->dbClass;
                $this->database = new $dbc($dbSettings); 
            } else {
                Ae_Dispatcher::loadClass('Ae_Joomla_15_Database');
                $this->database = new Ae_Joomla_15_Database(array('config' => & $this->config));
            }
        }
        
        Ae_Dispatcher::loadClass('Ae_Joomla_User');
        $session = & JFactory::getSession();
        $josUser = & $session->get('user');
        $this->_user = new Ae_Joomla_User($josUser);
        
        Ae_Dispatcher::loadClass('Ae_Joomla_Userstate');
        $this->userstate = new Ae_Joomla_Userstate();

    }
    
    function registerClassesForJoomlaLoader($directories, $useCache = true, $returnListOnly = false) {
        if (!is_array($directories)) $directories = array($directories);
        $classes = array();
        if ($useCache) {
            $disp = & Ae_Dispatcher::getInstance();
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
                $files = Ae_Util::listDirContents($base, true, array(), '/\\.php$/');
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
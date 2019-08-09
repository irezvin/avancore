<?php

/**
 * Big singleton from Avancore 0.2 (sort of an Application instance)
 * Returns legacy Mappers, Database, Config, Adapter, Session, Cache
 * Registers class loaders and loads languages
 * 
 * @deprecated
 * Use Ac_Application and it's descendants instead
 */
 
trigger_error("Ac_Dispatcher class is deprecated and will be removed", E_USER_DEPRECATED);

if (!class_exists('Ac_Util')) require (dirname(__FILE__).'/Util.php');

class Ac_Dispatcher {

    /**
     * Application name (same as component name) - used to find the component directories 
     * @access private
     */
    var $_appName = null;
    
    /**
     * @var boolean Whether dispatcher is called from the backend or not
     * @access private
     */
    var $_isBackend = false;
    
    /**
     * @var array Misc vars holder
     * @access private
     */
    var $_etc = array();
    
    /**
     * @var array Ac_Page_Abstract instances that were created using $this->getPageInstance() calls.
     * @access private
     */
    var $_pageInstances = array();
    
    var $_config = false;

    /**
     * @var Ac_Legacy_Database
     */
    var $database = false;
    
    /**
     * @var Ac_Sql_Db_Ae
     */
    protected $sqlDb = false;

    /**
     * @var Ac_Legacy_Config
     */
    var $config = false;
    
    var $_init = false;
    
    var $_adapterClass = false;
    
    var $_adapterExtraSettings = false;
    
    /**
     * @var Ac_Legacy_Adapter
     */
    var $adapter = false;
    
    var $language = false;
    
    var $_initLanguage = false;
    
    /**
     * @var Cache_Lite
     */
    var $_cache = false;
    
    /**
     * Sets misc variable value.
     * @param string name Variable name
     * @param string value Variable value
     */
    function setEtc($name, $value) {
        $this->_etc[$name] = $value;
    }
    
    /**
     * Returns misc variable value
     * @param string name Variable name
     * @return mixed Variable value or null if variable is null or not found
     */
    function getEtc($name) {
        if (isset($this->_etc[$name])) $res = $this->_etc[$name];
            else $res = null;
        return $res;
    }

    /**
     * Adds one or more include paths
     * @param false|string|array $path Path(s) to add (FALSE means directory with 'classes' where current file resides)
     * @param bool $prepend Add this path to beginning of include_path list (not the end)
     */
    static function addIncludePath($path = false, $prepend = false) {
        return Ac_Util::addIncludePath($path, $prepend);
    }
    
    function instantiate($appName = null, $isBackend = false, $language = false, $adapterClass = 'Ac_Joomla_Adapter', $dispatcherClass = 'Ac_Dispatcher', $adapterExtraSettings = array()) {
        if (!isset($GLOBALS['avsDispatcher']) || !is_a($GLOBALS['avsDispatcher'], 'Ac_Dispatcher')) {
            $GLOBALS['avsDispatcher'] = new Ac_Dispatcher($appName, $isBackend, $language, $adapterClass, $adapterExtraSettings);
        }
        $res = $GLOBALS['avsDispatcher'];
        $res->init();
        return $res;
    }
    
    /**
     * @param string appName name of the application (component). Defaults to the $GLOBALS['option'] is missing
     * @param bool isBackend whether the dispatcher is called from the backend or not
     */
    function __construct($appName = null, $isBackend = false, $language = false, $adapterClass = 'Ac_Joomla_Adapter', $adapterExtraSettings = array()) {
        if (is_null($appName)) {
            if (isset($GLOBALS['option']) && is_string($GLOBALS['option']) && preg_match('/^\w+$/', $GLOBALS['option'])) {
                $appName = $GLOBALS['option'];  
            } else {
                $appName = 'AvancoreApp';
                //trigger_error (__FILE__."::".__FUNCTION__." - cannot determine 'appName' param value", E_USER_ERROR);
            }
        }
        $this->_appName = $appName;
        $this->_isBackend = $isBackend;
        $this->_adapterClass = $adapterClass;
        $this->_adapterExtraSettings = $adapterExtraSettings;
        $this->_initLanguage = $language;
    }
    
    static function registerAutoload() {
        return Ac_Util::registerAutoload();
    }
    
    function init() {
        if (!$this->_init) {
            $this->_init = true;
            $this->adapter = new $this->_adapterClass($this->_adapterExtraSettings);
            $this->database = $this->adapter->database;
            $this->config = $this->adapter->config;
            if ($this->_initLanguage === false) $this->language = $this->_getDefaultLanguage();
                else $this->language = $this->_initLanguage;
            $this->_loadLanguage();
        }
    }
    
    function getLanguage() {
        return $this->language;
    }
    
    function _getDefaultLanguage() {
        return $this->getConfig('language', 'english');
    }

    static function getSafeIncludePath() {        
        $bd = explode(PATH_SEPARATOR, ini_get('open_basedir'));
        $p = explode(PATH_SEPARATOR, ini_get('include_path'));
        if ($bd) {
            foreach ($p as $i => $dir) {
                $found = false;
                foreach ($bd as $dir2) {
                    if (!strncmp($dir, $dir2, strlen($dir2))) {
                        $found = true;
                        break;
                    } 
                }
                if (!$found) unset($p[$i]);
            }
        }
        return $p;
    } 
    
    function _loadLanguage() {
    	if (($this->config->languagesDir !== false) && is_file($f = $this->config->languagesDir.DIRECTORY_SEPARATOR.$this->language.'.php')) {
        	require_once($f);
        } elseif (is_file($f = ($this->getDir().'/languages/'.$this->language.'.php'))) {
        	require_once($f);
        } else {
        	$found = false;
        	$ip = self::getSafeIncludePath();
        	foreach ($ip as $p) {
        		if (is_file($p.'/languages'.$this->language.'.php')) {
        			$found = true;
        			break;
        		}
        	}
        	if ($found) require_once('languages/'.$this->language.'.php');
        	else {
        		require_once(dirname(__FILE__).'/../../languages/'.$this->language.'.php');
        	}
        }
    }
    
    /**
     * @return bool Whether the dispatcher is called from the backend or not
     */
    function isBackend() {
        $res = $this->_isBackend;
        return $res;
    }
    
    
    /**
     * Returns name of the component directory (administrator/components/$this->_appName for backend and administrator/components/$this->_appName for frontend) 
     * without trailing slash.
     * 
     * @param bool frontend Return frontend or backend directory (true => frontend, false => backend (default)) 
     */
    function getDir($frontend = false) {
        if ($this->config) {
            if ($frontend) {
                $res = $this->config->getFrontendDir();
            } else {
                $res = $this->config->getBackendDir();
            }
        } else $res = $this->getAppDir();
        return $res;
    }
    
    /**
     * @return string
     */
    static function getAppDir() {
        return dirname(__FILE__).'/../..';
    }
    
    function loadInterface($interfaceName) {        
        
        if (!interface_exists($interfaceName)) { // New behavior - use relative path to classDir
            $fileName = str_replace('_', '/', $interfaceName).'.php';
            $classDir = dirname(__FILE__).'/../';
            $f = $classDir.$fileName;
            if (is_file($f)) require($f);
                else require($fileName);
            if (!interface_exists($interfaceName)) trigger_error (__FILE__."::".__FUNCTION__." - interface '$interfaceName' not found in the $fileName", E_USER_ERROR);
        }
        return $interfaceName;
    }
    
    function getVendorPath() {
        return dirname(dirname(dirname(__FILE__))).'/vendor';
    }
    
    /**
     * getInstance() function __construct a backbone of Singleton pattern. It's impletemented via GLOBALS (and not via static vars) since
     * this will allow to Ac_Dispatcher::getInstance() return descendants of Ac_Dispatcher class, not only it's direct instances.
     * 
     * The function __construct static.
     * 
     * @return Ac_Dispatcher 
     */
    static function getInstance() {
        if (!(
            isset($GLOBALS['avsDispatcher']) 
            && is_object($GLOBALS['avsDispatcher']) 
            && is_a($GLOBALS['avsDispatcher'], 'Ac_Dispatcher'))
        ) {
            $GLOBALS['avsDispatcher'] = new Ac_Dispatcher(); 
        }
            
        $res = $GLOBALS['avsDispatcher'];
        
        return $res;            
    }
    
    static function hasInstance() {
        $res =  isset($GLOBALS['avsDispatcher']) 
            && is_object($GLOBALS['avsDispatcher']) 
            && is_a($GLOBALS['avsDispatcher'], 'Ac_Dispatcher');
        return $res;
    } 
    
    static function setInstance(Ac_Dispatcher $instance = null) { 
        $GLOBALS['avsDispatcher'] = $instance;
    }
    
    function getConfig($varName = false, $default = null) {
        if ($this->_config === false) {
            $this->_config = $this->adapter->config->getConfigArray();
        }
        if (strlen($varName)) {
            $res = isset($this->_config[$varName])? $this->_config[$varName] : $default;
        } else $res = $this->_config;
        
        return $res;
    }
    
    function getAppName() {
        return $this->_appName;
    }
    
    function getConfigPath() {
        return $this->getDir(false).'/app.config.php';
    }

    /**
     * @return Ac_Model_Mapper
     */
    static function getMapper($className) {
        $res = Ac_Model_Mapper::getMapper($className);
        return $res;
    }
    
    /**
     * @return Ac_Legacy_User
     */
    function getUser() {
        $res = $this->adapter->getUser();
        return $res;
    }
    
    /**
     * @return Ac_Legacy_Session
     */
    function getSession() {
        $res = $this->adapter->getSession();
        return $res;
    }
    
    function getCacheDir() {
        if (isset($this->config->cachePath) && $this->config->cachePath) return $this->config->cachePath;
            else {
                return $this->getDir().'/cache';
            }
    }
    
    /**
     * @return Cache_Lite
     */
    function getCache() {
        if ($this->_cache === false) {
            
            if (!strcasecmp(get_class($this->adapter), 'Ac_Joomla_Adapter')) {
                if (!class_exists('Cache_Lite')) require_once($this->config->absolutePath.'/includes/Cache/Lite.php');
            }
            $options = array('cacheDir' => $this->getCacheDir().'/'.$this->config->cachePrefix, 'readControl' => false);
            if ($cl = $this->getConfig('cacheLifeTime')) {
                $options['lifeTime'] = $cl;
            } 
            $this->_cache = new Cache_Lite($options);
        }
        return $this->_cache;
    }
    
    static function cacheGet($key, $cacheGroup = 'default') {
        $disp = Ac_Dispatcher::getInstance();
        $c = $disp->getCache();
        $res = $c->get($key, $cacheGroup);
        $disp->cacheCleanup(0.01);
        return $res;
    }
    
    static function cacheRemove($key, $cacheGroup = 'default') {
        $disp = Ac_Dispatcher::getInstance();
        $c = $disp->getCache();
        $res = $c->remove($key, 'default');
        return $res;
    }
    
    static function cacheClean($cacheGroup = 'default') {
        $disp = Ac_Dispatcher::getInstance();
        $c = $disp->getCache();
        $res = $c->clean($cacheGroup);
        return $res;
    }

    static function cacheSet($key, $value, $cacheGroup = 'default') {
        $disp = Ac_Dispatcher::getInstance();
        $c = $disp->getCache();
        $disp->cacheCleanup(0.01);
        return $c->save($value, $key, $cacheGroup);
    }
    
    static function loadClass($className) {
        return Ac_Util::loadClass($className);
    }
    
    function cacheCleanup($probability = true) {
        if (is_float($probability) && $probability != 0) {
            if (!(rand(0, ceil(1 / abs($probability))) == 1)) return false; 
        }
        $disp = Ac_Dispatcher::getInstance();
        $cacheDir = $this->getCacheDir();
        if ($cl = $this->getConfig('cacheLifeTime')) {
            $lifeTime = $cl;
        } else $lifeTime = 3600;
        $t = time() - $lifeTime;
        $px = $this->config->cachePrefix.'cache_';
        $l = strlen($px);
        if ($dir = opendir($d = $cacheDir)) {
            while ($f = readdir($dir)) {
                $fn = $d.'/'.$f;
                if (is_file($fn) && !strncmp($f, $px, $l) && filemtime($fn) < $t) {
                    unlink($fn);
                }
            }
            closedir($dir);
        } 
    }
    
    /**
     * @return Ac_Sql_Db_Ae
     */
    function getSqlDb() {
        if ($this->sqlDb === false) $this->sqlDb = new Ac_Sql_Db_Ae($this->database);
        return $this->sqlDb;
    }
    
}

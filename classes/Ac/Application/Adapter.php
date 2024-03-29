<?php

if (!class_exists('Ac_Util', false)) require_once(dirname(__FILE__).'/../Util.php');
if (!class_exists('Ac_Prototyped', false)) require_once(dirname(__FILE__).'/../Prototyped.php');

/**
 * @todo Document all config' properties and where are they used
 * 
 * @property $checkDirs
 * @property $classPaths
 * @property $genPath
 * @property $varPath
 * @property $varCachePath
 * @property $varTmpPath
 * @property $varFlagsPath
 * @property $varLogsPath
 * @property $varDumpsPath
 * @property $scriptsPath
 * @property $scriptsSqlUpgradePath
 * @property $vendorPath
 * @property $initPath
 * @property $lanugagesPaths
 * @property $lanugagesPaths_gen
 * @property $webUrl
 * @property $webAssetsUrl
 * @property $outputPrototype
 * @property $overrides
 * @property $charset
 * @property $assetPlaceholders
 * @property $cachePrototype
 * @property $dbPrototype
 * @property $legacyDatabasePrototype
 * @property $appInitOptions
 * @property $logEnabled
 * @property $services
 * @property $mailSenderPrototype
 * @property $componentPrototypes
 * @property $userProviderId
 *
 * @property $managerImagesUrl
 * @property $joomlaComponentName
 * @property $cacheLifetime
 * @property $rteDefaultInstance
 */
class Ac_Application_Adapter extends Ac_Prototyped {

    protected $appClass = false;
    
    protected $appClassFile = false;
    
    protected $configFiles = false;
    
    protected $configFromFiles = false;
    
    protected $configFromInit = array();
    
    protected $config = false;
    
    protected $overrides = array();
    
    protected $envName = false;
    
    protected $configPath = false;
    
    protected $services = array();
    
    protected $classesDir = false;
    
    protected $isConstructing = true;
    
    function getEnvName() {
        if ($this->envName === false) {
            if (defined('AVANCORE_ENV')) $this->envName = AVANCORE_ENV;
            elseif (strlen($e = getenv('AVANCORE_ENV'))) $this->envName = $e;
            else $this->envName = 'devel';
        }
        return $this->envName;
    }
    
    function setEnvName($value) {
        $this->envName = $value;
    }
    
    function setAppClassFile($value) {
        $this->appClassFile = $value;
        $this->classesDir = false;
    }
    
    protected function setConfigFiles($value) {
        if ($value !== false) $value = Ac_Util::toArray($value);
        $this->configFiles = $value;
    }
    
    function setOverrideValue($option, $value) {
        $this->overrides[$option] = $value;
    }
    
    function unsetOverrideValue($option) {
        unset($this->overrides[$option]);
    }
    
    function getConfigFiles() {
        if ($this->configFiles === false) {
            $this->configFiles = array();
            $dir = $this->intGetConfigDir();
            if (strlen($dir)) {
                if (is_file($n = $dir.'/app.config.php')) $this->configFiles[] = $n;
                if (strlen($e = $this->getEnvName()) && is_file($n = $dir.'/'.$e.'.env.config.php')) $this->configFiles[] = $n;
                if (is_file($n = $dir.'/'.get_class($this).'.adapter.config.php')) $this->configFiles[] = $n;
            }
        }
        return $this->configFiles;
    }
    
    function setConfigPath($configPath) {
        $this->configPath = $configPath;
    }
    
    protected function intGetConfigFromFiles() {
        $this->configFromFiles = array();
        foreach ($this->getConfigFiles() as $fn) {
            $config = array();
            require($fn);
            Ac_Util::ms($this->configFromFiles, $config);
        }
        return $this->configFromFiles;
    }
    
    function __construct(array $options = array()) {
        foreach ($gotKeys = $this->initFromPrototype($options, false) as $key) {
            unset($options[$key]);
        }
        if (isset($options['class'])) unset($options['class']);
        $this->configFromInit = $options;
        $this->isConstructing = false;
    }
    
    protected function intGetOverrides() {
        if ($this->overrides === false) {
            $this->overrides = array();
            Ac_Util::ms($this->overrides, $this->configFromInit);
            Ac_Util::ms($this->overrides, $this->getConfigFromFile());
        }
    }
    
    protected function intGetConfigDir() {
        $res = false;
        $dir = $this->detectClassesDir();
        if (strlen($dir)) {
            $dir = dirname($dir);
            if (isset($this->configFromInit['deployDirPath'])) {
                $res = $this->configFromInit['deployDirPath'];
                if (!is_dir($res)) trigger_error("Warning: directory mentioned in 'deployDirPath' init-provided property, '{$res}', does not exist", E_USER_WARNING);
            } elseif (strlen($dir)) {
                if (is_dir($n = $dir.'/config')) {
                    $res = $n;
                    if (strlen($this->appClass) && is_dir($n = $dir.'/'.$this->appClass)) $res = $n;
                } elseif (is_dir($n = $dir.'/deploy')) {
                    $res = $n;
                    if (strlen($this->appClass) && is_dir($n = $dir.'/'.$this->appClass)) $res = $n;
                }
            }
        }
        return $res;
    }
    
    protected function detectClassesDir() {
        $this->classesDir = false;
        if ($this->classesDir === false) {
            $res = false;
            if (strlen($this->appClassFile)) {
                $s = $this->appClassFile;
                while (basename($s) !== 'classes') {
                    $s = dirname($s);
                }
                if (strlen($s) > 1) $res = $s;
            }
            $this->classesDir = $res;
        }
        return $this->classesDir;
    }
    
    function getAppRootDir() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    protected function doGetDefaultAppRootDir() {
        $dir = $this->detectClassesDir();
        if ($dir) $res = dirname($dir);
            else $res = '';
        return $res;
    }
    
    /**
     * @param type $configKey
     * @param type $autoValue 
     */
    protected function detectDir($configKey, $autoValue) {
        if (!isset($this->config[$configKey])) {
            if (!$this->checkDirs || is_dir($autoValue)) $this->config[$configKey] = $autoValue;
        } else {
            if ($this->checkDirs) {
                if (is_array($this->config[$configKey])) {
                    foreach ($this->config[$configKey] as $i => $dir) {
                        if (!is_dir($dir))
                            throw new Exception("Directory not found: '{$dir}' (specified in the configuration key '{$configKey}.{$i}')");
                    }
                } else {
                    if (!is_dir($this->config[$configKey])) throw new Exception("Directory not found: '{$this->config[$configKey]}' (specified in the configuration key '{$configKey}')");
                }
            }
        }
        return isset($this->config[$configKey]);
    }
    
    protected function setDefault($configKey, $autoValue) {
        if (!isset($this->config[$configKey])) $this->config[$configKey] = $autoValue;
    }
    
    protected function calcMissingConfig() {
        $cd = $this->detectClassesDir();
        $this->setDefault('checkDirs', true);
        $this->detectDir('classPaths', $cd);
        $cd = dirname($cd);
        $this->detectDir('genPath', $cd.'/gen/classes');
        if ($this->detectDir('varPath', $cd.'/var')) {
            $vp = $this->config['varPath'];
            $this->detectDir('varCachePath', $vp.'/cache') || $this->detectDir('varCachePath', $vp);
            $this->detectDir('varTmpPath', $vp.'/tmp') || $this->detectDir('varTmpPath', $vp);
            $this->detectDir('varDumpsPath', $vp.'/dumps') || $this->detectDir('varDumpsPath', $vp);
            $this->detectDir('varFlagsPath', $vp.'/flags') || $this->detectDir('varFlagsPath', $vp);
            $this->detectDir('varLogsPath', $vp.'/logs') || $this->detectDir('varLogsPath', $vp);
        }
        if ($this->detectDir('scriptsPath', $cd.'/scripts')) {
            $this->detectDir('scriptsSqlUpgradePath', $this->config['scriptsPath'].'/sql/upgrade');
        }
        
        $this->detectDir('vendorPath', $cd.'/vendor');
        
        $this->detectDir('initPath', $cd.'/init');
        if (!isset($this->config[$lp = 'languagesPaths'])) {
            $this->detectDir($lp = 'languagesPaths', $cd.'/languages');
            if ($this->detectDir('languagesPaths_gen', $cd.'/gen/languages')) {
                if (!isset($this->config[$lp])) $this->config[$lp] = '';
                    else $this->config[$lp] .= PATH_SEPARATOR;
                $this->config[$lp] .= $this->config['languagesPaths_gen'];
                unset($this->config['languagesPaths_gen']);
            }
        }
        
        $this->guessUrls();
        $this->guessDatabase();
        $this->guessCache();
        $this->guessOutputPrototype();
        
        if (isset($this->services['flags']) && is_array($this->services['flags']) && !array_key_exists('dir', $this->services['flags'])) {
            $this->services['flags']['dir'] = $this->getVarFlagsPath();
        }
        
    }
    
    protected function guessUrls() {
        if (!isset($this->config['webUrl'])) {
            $u = Ac_Url::guessBase();
            $u->query = array();
            $this->config['webUrl'] = $u->toString();
        }
        
        if (!isset($this->config['webAssetsUrl']) || !isset($this->config['adminImagesUrl'])) {
            if (!isset($u) && isset($this->config['webUrl'])) $u = new Ac_Url($this->config['webUrl']);
            if (isset($u)) {
                $u->query = array();
                if (substr($u->path, -1) !== '/') $u->path = dirname($u->path).'/';
                if (!isset($this->config['webAssetsUrl'])) {
                    $this->config['webAssetsUrl'] = $u->toString().'assets';
                }
                if (!isset($this->config['adminImagesUrl']))$this->config['adminImagesUrl'] = $u->toString().'images';
            }
        }
    }
    
    protected function guessDatabase() {
        if (!isset($this->config[$k = 'database'])) {
            //$this->config[$k] = array('class' => 'Ac_Legacy_Database_Native');
        }
    }
    
    protected function guessCache() {
        if (!isset($this->config[$k = 'cache'])) {
            if (isset($this->config['varCachePath']) && strlen($this->config['varCachePath'])) {
                $this->config[$k] = array(
                    'class' => 'Ac_Cache',
                    'cacheDir' => $this->config['varCachePath'],
                );
            } else {
                $this->config[$k] = array(
                    'class' => 'Ac_Cache',
                    'enabled' => false,
                );
            }
        }
    }
    
    protected function guessOutputPrototype() {
        if (!isset($this->config[$k = 'outputPrototype'])) {
            $this->config[$k] = array('class' => 'Ac_Controller_Output_Native');
        }
    }
    
    protected function intGetArrConfig() {
        if ($this->config === false) {
            Ac_Util::ms($this->config, $this->intGetConfigFromFiles());
            Ac_Util::ms($this->config, $this->configFromInit);
            $this->calcMissingConfig();
            if (isset($this->config['overrides']) && is_array($this->config['overrides'])) {
                $tmp = $this->config['overrides'];
                unset($this->config['overrides']);
                Ac_Util::ms($this->config, $this->config['overrides']);
            }
        }
        return $this->config;
    }
    
    protected function intGetConfigValue($option, $fromFun = true) {
        $res = null;
        // if $option below is passed-by-copy (''.$option), the bug below won't happen
        if (array_key_exists($option, $this->overrides)) return $this->overrides[$option];
        if ($fromFun) $option[0] = strtolower($option[0]);
        if (method_exists($this, $m = 'doGet'.$option)) $res = $this->$m();
        if (is_null($res)) {
            $conf = $this->intGetArrConfig();
            // comment this to find PHP7 bug (PHP 7.0.1, apache module)
            $option = ''.$option; // Some real PHP7 WTFery goes here - can't write a reliable test case!!
            if (isset($conf[$option])) $res = $conf[$option];
            elseif (method_exists($this, $m = 'doGetDefault'.$option)) $res = $this->$m();
            // uncomment to find PHP7 bug
            //if (isset($conf[$option]) !== isset($conf[''.$option])) {
            //    die('cookadodie PHP7 rocks');
            //}
        }
        return $res;
    }
    
    function getConfigValue($option, $default = null) {
        $res = $this->intGetConfigValue($option, false);
        if (is_null($res)) $res = $default;
        return $res;
    }
    
    /**
     * Whether adapter should check existence of directories specified in the config
     * (disable it for production environments for performance)
     * 
     * Default: true
     * 
     * @return bool
     */
    function getCheckDirs() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getClassPaths() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
 
    function getGenPath() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getVarPath() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getVarCachePath() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getVarTmpPath() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getVarDumpsPath() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getVarFlagsPath() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getVarLogsPath() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getScriptsPath() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getScriptsSqlUpgradePath() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getVendorPath() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getInitPath() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getLanguagesPaths() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getConfigPath() {
        if ($this->configPath === false) {
            $this->configPath = $this->intGetConfigDir();
        }
        return $this->configPath;
    }
    
    function getWebUrl() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getSiteUrl() {
        return $this->getWebUrl();
    }
    
    function getWebAssetsUrl() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getAdminImagesUrl() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getCharset() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    protected function doGetDefaultCharset() {
        return 'utf-8';
    }
    
    /**
     * @return array
     */
    function getAssetPlaceholders() {
        $res = Ac_Util::toArray($this->intGetConfigValue(substr(__FUNCTION__, 3)));
        return $res;
    }
    
    function getLegacyDatabasePrototype() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getDbPrototype() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getMailSenderPrototype() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    protected function doGetDefaultMailSenderPrototype() {
        $res = array(
            'class' => 'Ac_Mail_PHPMailer_Mail',
            'defaultCharset' => $this->getCharset(),
        );
        return $res;
    }
    
    function getCachePrototype() {
        $res = $this->intGetConfigValue(substr(__FUNCTION__, 3));
        if (is_null($res)) $res = array();
        if (is_array($res)) {
            $res = Ac_Util::m($this->doGetDefaultCachePrototype(), $res);
        }
        return $res;
    }
    
    protected function doGetDefaultCachePrototype() {
        $res = array('class' => 'Ac_Cache', 'cacheDir' => $this->getVarCachePath());
        if (($cl = $this->getConfigValue('cacheLifetime')) !== false) {
            $res['lifetime'] = $cl;
        }
        return $res;
    }
    
    function getOutputPrototype() {
        return $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getAppInitOptions() {
        return Ac_Util::toArray($this->intGetConfigValue(substr(__FUNCTION__, 3)));
    }
    
    function getLogEnabled() {
        return (bool) $this->intGetConfigValue(substr(__FUNCTION__, 3));
    }
    
    function getDefaultComponentPrototypes() {
        return array(
            Ac_Application::CORE_COMPONENT_MANAGER_CONFIG_SERVICE => 'Ac_Admin_ManagerConfigService',
            Ac_Application::CORE_COMPONENT_FLAGS => [
                'dir' => $this->getVarFlagsPath()
            ]
        );
    }
    
    function getComponentPrototypes () {
        $res = $this->getDefaultComponentPrototypes();
        $conf = $this->getConfigValue('componentPrototypes', array());
        if (!is_array($conf)) $conf = array();
        Ac_Util::ms($res, $conf);
        return $res;
    }

    function __get($varName) {
        return $this->getConfigValue($varName);
    }
    
    function __set($varName, $value) {
        $this->setOverrideValue($varName, $value);
    }
    
    function __isset($varName) {
        // to avoid calling getConfigValue() during constructor phase
        if ($this->isConstructing) return false; 
        
        return $this->getConfigValue($varName, null) !== null;
    }
    
    function __unset($varName) {
        $this->unsetOverrideValue($varName);
    }
    
    /**
     * @return Ac_Controller_Output
     */
    function createDefaultOutput() {
        $res = new Ac_Controller_Output_Native(['showOuterHtml' => true]);
        return $res;
    }
    
    /**
     * @return Ac_Controller_Context
     */
    function createDefaultContext() {
        $res = new Ac_Controller_Context_Http;
        $res->populate(null, false, null, true);
        if ($this->webUrl) $baseUrl = $this->webUrl;
        else {
            $baseUrl = Ac_Url::guess(true);
            $baseUrl->query = [];
        }
        $res->setBaseUrl($baseUrl);
        return $res;
    }
    
    
}
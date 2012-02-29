<?php

Ae_Dispatcher::loadClass('Cg_Util');

class Cg_Generator {
    
    /**
     * Hostname to access database
     * @var string
     */
    var $host = false;
    
    /**
     * Username to access database
     * @var string
     */
    var $user = false;
    
    /**
     * Password to access database
     * @var string
     */
    var $password = false;
    
    var $otherDbOptions = array();
    
    /**
     * Class of Dbs Inspector to get info on the database
     * @var string
     */
    var $inspectorClass = 'Ae_Sql_Dbi_Inspector';
    
    /**
     * @var string Name of directory to output to
     */
    var $outputDir = 'output';
    
    var $targetDir = '../';
    
    /**
     * @var Ae_Legacy_Database 
     */
    var $_db = false;
    
    /**
     * @var Ae_Sql_Dbi_Inspector
     */
    var $_inspector = false;
    
    /**
     * Static config array for current project
     * @var array
     */
    var $staticConfig = false;
    
    /**
     * Name of configuration file
     * @var string
     */
    var $_configFileName = false;
    
    /**
     * Array of Cg_Domain objects
     */
    var $_domains = false;
    
    /**
     * @var string Name of generator log file
     */
    var $logFileName = 'generator_log.txt'; 
    
    /**
     * @var bool Whether to overwrite log on each run or not 
     */
    var $overwriteLog = false;
    
    /**
     * @var bool Whether to print log messages without 'important' tag
     */
    var $verbose = false;
    
    /**
     * @var resource
     */
    var $_logFile = false;
    
    /**
     * Names of entities (domains and models) to proceess (by default, generator processes all domains, all models). Can be in following forms:
     * - string 'domain1, domain2.model1, domain2.model2, ...
     * - array ('domain1', 'domain2.model1', 'domain3' => array(), 'domain4' => array('model4', 'model5'))
     * In specified above array form, 'domain1' or 'domain3' => array() means 'all models from domains 1 and 3'   
     * @var array
     */
    var $genEntities = array();
    
    var $genEditable = true;
    
    var $genNonEditable = true;
    
    var $ovrEditable = true;
    
    var $strategySettings = array();
    
    /**
     * Are added under each domain's config 
     */
    var $domainDefaults = array();
    
    var $clearOutputDir = false;
    
    /**
     * Add static keywords and type hinting in appropriate places.
     * @var bool
     */
    var $php5 = false;
    
    /**
     * Generate <App>_<Model>_<Finder> class extends Pmt_Finder for each model.
     * @var bool
     */
    var $generatePmtFinders = false;
    
    /**
     * Number of bytes that were written during last run
     */
    var $_outputBytes = false;
    
    /**
     * Number of files that were written during last run
     */
    var $_outputFiles = false;
    
    /**
     * @param string $configFileName name of file with static configuration of project
     */
    function Cg_Generator($configFileName, $runtimeOptions = array()) {
        $this->_configFileName = $configFileName;
        $this->_loadStaticConfig();
        if (isset($this->staticConfig['generator']) && is_array($this->staticConfig['generator'])) {
            if (isset($this->staticConfig['generator']['staticConfig'])) unset($this->staticConfig['generator']['staticConfig']);
            Ae_Util::simpleBind($this->staticConfig['generator'], $this); 
            Ae_Util::simpleBind($runtimeOptions, $this);
        }
    }
    
    /**
     * Parses $this->genEntities from free form (see var description) into more usable form 'domain' => array ('model', 'model'...)
     */
    function parseGenEntities() {
        if (is_string($this->genEntities)) $this->genEntities = preg_split("/, */", $this->genEntities);
        $parsed = array();
        foreach ($this->genEntities as $dom => $mod) {
            if (is_string($mod)) {
                $dm = explode(".", $mod, 2);
                if (isset($dm[1])) $parsed[$dm[0]][] = $dm[1];
                    else $parsed[$dm[0]] = array();
            } elseif (is_array($mod)) {
                $parsed[$dom] = $mod;
            }
        }
        if (!count($parsed)) {
            foreach ($this->listDomains() as $d) $parsed[$d] = array();
        }
        foreach ($parsed as $dom => $models) {
            if (!count($models)) {
                $domain = $this->getDomain($dom);
                $models = $domain->listModels();
            }
            $parsed[$dom] = array_unique($models);
        }
        return $parsed;
    }
    
    function _loadStaticConfig() {
        $this->staticConfig = array();
        if ($this->_configFileName) {
            require($this->_configFileName);
            if (isset($config) && is_array($config)) $this->staticConfig = Cg_Generator::_expandPaths($config);
        }
    }
    
    /**
     * @return Ae_Legacy_Database 
     */
    function getDb() {
        if ($this->_db === false) {
        	$disp = Ae_Dispatcher::getInstance();
        	if ($disp && $disp->config) {
        	   $dbClass = $disp->config->dbClass;
        	} else {
        	    $dbClass = false;
        	}
        	if (!strlen($dbClass)) $dbClass = 'Ae_Legacy_Database_Native';
            Ae_Dispatcher::loadClass($dbClass);
            $dbOptions = array_merge(array(
                'host' => $this->host,
                'user' => $this->user,
                'password' => $this->password,
            ), $this->otherDbOptions);
            $this->_db = new $dbClass($dbOptions);
        }
        return $this->_db;
    }
    
    /**
     * @return Ae_Sql_Dbi_Inspector
     */
    function getInspector() {
        if ($this->_inspector === false) {
            Ae_Dispatcher::loadClass($c = $this->inspectorClass);
            $db = $this->getDb();
            $this->_inspector = new $c($db, false);
        }
        return $this->_inspector;
    }
    
    function listDomains() {
        $l = '_domains';
        if ($this->$l === false) {
            $this->$l = array();
            if (isset($this->staticConfig['domains']) && is_array($this->staticConfig['domains'])) { 
                Ae_Dispatcher::loadClass('Cg_Domain');
                foreach ($this->staticConfig['domains'] as $name => $config) {
                    if (is_array($this->domainDefaults)) $config = Ae_Util::m($this->domainDefaults, $config);
                    $obj = new Cg_Domain($this, $name, $config);
                    $this->{$l}[$name] = $obj; 
                }
            }
        }
        return array_keys($this->$l);
    }
    
    /**
     * @return Cg_Domain
     */
    function getDomain($name) {
        if (!in_array($name, $this->listDomains())) trigger_error ('No such domain: \''.$name.'\'', E_USER_ERROR);
        return $this->_domains[$name];
    }
    
    /**
     * Returns array where each key is processed as dot-separated path
     *      Cg_Generator::expandPaths(array('x' => 10, 'y.z' => 20, 'foo.bar' => array ('baz' => 40)), '.q.w.e' => 'goo')
     * will return
     *      array(
     *          x => 10
     *          y => array(
     *              z => 20
     *          )
     *          
     *          foo => array
     *              bar => array (
     *                  baz => 40
     *              )
     *          )
     *          
     *          .q.w.e => goo
     *      )
     * If before path expansion add key-value pair 'y' => 30, such scalar value will be overwritten.
     * Path that start with "." are not expanded.  
     */
    function _expandPaths($array) {
        $keys = array_keys($array);
        foreach ($keys as $i=>$k) 
        {
            if (is_array($array[$k])) {$ak = Cg_Generator::_expandPaths($array[$k]); $array[$k] = $ak;}
            if ((($sp = strpos($k, '.')) !== false) && ($sp != 0)) {
                list($head, $tail) = explode('.', $k, 2);
                $value = $array[$k];
                $replacement = array ($head => array());
                unset($array[$k]);
                $path = explode('.', $k);
                Ae_Util::setArrayByPath($array, $path, $value);
            }
        }
        return $array;
    }
    
    /**
     * @return Cg_Strategy
     */
    function & createStrategyForDomain($domainName) {
        $class = Ae_Util::getArrayByPath($this->staticConfig, array('domains', $domainName, 'strategyClass'), 'Cg_Strategy');
        Ae_Dispatcher::loadClass($class);
        if (is_array($this->strategySettings)) $ss = $this->strategySettings; else $ss = array();
        $ss['genNonEditable'] = $this->genNonEditable;
        $dom = $this->getDomain($domainName);
        Ae_Util::ms($ss, $dom->getStrategySettings());
        $res = new $class($this, $domainName, $this->outputDir, $this->genEditable, $this->ovrEditable, $ss);
        return $res;
    }
    
    function log($message, $important = false) {
        if ($this->logFileName) {
            if ($this->_logFile === false) {
                if ($this->overwriteLog) $this->_logFile = fopen($this->logFileName, "w");
                    else $this->_logFile = fopen($this->logFileName, "a");
                if ($this->_logFile === false) {
                    trigger_error ("Cannot open log file '{$this->logFileName}'", E_USER_WARNING);
                    $this->_logFile = null;
                }
            }
            if ($this->_logFile) fputs($this->_logFile, date("Y-m-d H:i:s")."\t".$message."\n");
            if ($important || $this->verbose) echo $message."\n";
        }
    }
    
    
    
    function run() {
        
        $errLog = $this->logFileName.'.errors.log';
        $settings = array('error_reporting' => E_ALL, 'html_errors' => false, 'display_errors' => 0, 'log_errors' => 1, 'error_log' => $errLog, 'ignore_repeated_errors' => true);
        $oldSettings = array();
        if ($this->overwriteLog && is_file($errLog)) {
            fclose(fopen($errLog, "w"));
        } else {
            if (is_file($errLog)) {
                fopen($errLog, "w");
                fputs($errLog, "\n\n----------------------------------\n\n");
            }
        }
        
        foreach ($settings as $s => $v) {
            $oldSettings[$s] = ini_get($s);
            ini_set($s, $v); 
        }
        
        $this->log('Generator started ----------------');
        
        $this->_outputBytes = 0;
        $this->_outputFiles = 0;
        
        if ($this->clearOutputDir && $this->outputDir) Cg_Util::cleanDir($this->outputDir);
        $todo = $this->parseGenEntities();
        foreach ($todo as $domain => $models) {
            $strat = $this->createStrategyForDomain($domain);
            $strat->generateCodeForModels($models);
            $strat->generateCommonCode();
        }
        
        $this->log('Generator finished: '.$this->getOutputBytes().' bytes in '.$this->getOutputFiles().' files ----------------');
        
        foreach ($oldSettings as $s => $v) {
            ini_set($s, $v); 
        }
        
    }
    
    function addOutputStats($files = 0, $bytes = 0) {
        $this->_outputBytes += $bytes;
        $this->_outputFiles += $files;
    }
    
    function getOutputBytes() {
        return $this->_outputBytes;
    }
    
    function getOutputFiles() {
        return $this->_outputFiles;
    }
    
    function closeLog() {
        if ($this->_logFile) fclose($this->_logFile);
    }
    
    
}

?>
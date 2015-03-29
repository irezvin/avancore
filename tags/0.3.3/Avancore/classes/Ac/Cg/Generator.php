<?php

class Ac_Cg_Generator {
    
    var $dbPrototype = false;
    
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
    var $inspectorClass = false;
    
    /**
     * @var string Name of directory to output to
     */
    var $outputDir = 'output';
    
    var $targetDir = '../';
    
    /**
     * @var Ac_Sql_Db 
     */
    var $_db = false;
    
    /**
     * @var Ac_Sql_Dbi_Inspector
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
     * Array of Ac_Cg_Domain objects
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
     * Ac_Cg_Writer_Abstract instance or its' prototype
     */
    var $writer = false;
    
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
     * Number of bytes that were written during last run
     */
    var $_outputBytes = false;
    
    /**
     * Number of files that were written during last run
     */
    var $_outputFiles = false;
    
    var $lintify = true;
    
    var $lintCommand = "php -l %s 2>&1";
    
    /**
     * @param string $configOrFileName name of file with static configuration of project
     */
    function Ac_Cg_Generator($configOrFileName, $runtimeOptions = array()) {
        if (is_array($configOrFileName)) $this->staticConfig = $configOrFileName;
        else {
            $this->_configFileName = $configOrFileName;
            $this->_loadStaticConfig();
        }
        if (isset($this->staticConfig['generator']) && is_array($this->staticConfig['generator'])) {
            if (isset($this->staticConfig['generator']['staticConfig'])) unset($this->staticConfig['generator']['staticConfig']);
            Ac_Util::simpleBind($this->staticConfig['generator'], $this); 
            Ac_Util::simpleBind($runtimeOptions, $this);
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
            if (isset($config) && is_array($config)) $this->staticConfig = Ac_Cg_Generator::_expandPaths($config);
        }
    }
    
    /**
     * @return Ac_Sql_Db
     */
    function getDb() {
        if ($this->_db === false) {
            if ($this->dbPrototype === false) {
                $dsnExtra = "";
                if (isset($this->otherDbOptions['charset']) && strlen($this->otherDbOptions['charset'])) $dsnExtra .= ";charset=".$this->otherDbOptions['charset'];
                $dbPrototype = array(
                    'class' => 'Ac_Sql_Db_Pdo',
                    'pdo' => array(
                        'dsn' => 'mysql:host='.$this->host.$dsnExtra,
                        'username' => $this->user,
                        'password' => $this->password,
                        'driver_options' => array(
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_PERSISTENT => true,
                        ),
                    ),
                );
            } else $dbPrototype = $this->dbPrototype;
            $this->_db = Ac_Prototyped::factory($dbPrototype, 'Ac_Sql_Db');
        }
        return $this->_db;
    }
    
    /**
     * @return Ac_Sql_Dbi_Inspector
     */
    function getInspector() {
        if ($this->_inspector === false) {
            $c = $this->inspectorClass;
            $db = $this->getDb();
            if ($c) {
                $this->_inspector = new $c($db, false);
            } else {
                $this->_inspector = $this->getDb()->getInspector();
            }
        }
        return $this->_inspector;
    }
    
    function listDomains() {
        $l = '_domains';
        if ($this->$l === false) {
            $this->$l = array();
            if (isset($this->staticConfig['domains']) && is_array($this->staticConfig['domains'])) { 
                foreach ($this->staticConfig['domains'] as $name => $config) {
                    if (is_array($this->domainDefaults)) $config = Ac_Util::m($this->domainDefaults, $config);
                    $obj = new Ac_Cg_Domain($this, $name, $config);
                    $this->{$l}[$name] = $obj; 
                }
            }
        }
        return array_keys($this->$l);
    }
    
    /**
     * @return Ac_Cg_Domain
     */
    function getDomain($name) {
        if (!in_array($name, $this->listDomains())) trigger_error ('No such domain: \''.$name.'\'', E_USER_ERROR);
        return $this->_domains[$name];
    }
    
    /**
     * Returns array where each key is processed as dot-separated path
     *      Ac_Cg_Generator::expandPaths(array('x' => 10, 'y.z' => 20, 'foo.bar' => array ('baz' => 40)), '.q.w.e' => 'goo')
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
            if (is_array($array[$k])) {$ak = Ac_Cg_Generator::_expandPaths($array[$k]); $array[$k] = $ak;}
            if ((($sp = strpos($k, '.')) !== false) && ($sp != 0)) {
                list($head, $tail) = explode('.', $k, 2);
                $value = $array[$k];
                $replacement = array ($head => array());
                unset($array[$k]);
                $path = explode('.', $k);
                Ac_Util::setArrayByPath($array, $path, $value);
            }
        }
        return $array;
    }
    
    /**
     * @return Ac_Cg_Strategy
     */
    function createStrategyForDomain($domainName) {
        $class = Ac_Util::getArrayByPath($this->staticConfig, array('domains', $domainName, 'strategyClass'), 'Ac_Cg_Strategy');
        if (is_array($this->strategySettings)) $ss = $this->strategySettings; else $ss = array();
        $ss['genNonEditable'] = $this->genNonEditable;
        $dom = $this->getDomain($domainName);
        Ac_Util::ms($ss, $dom->getStrategySettings());
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
    
    function prepare() {
        
        foreach ($this->listDomains() as $domain) {
            $domainObject = $this->getDomain($domain);
            $domainObject->beforeGenerate();
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
                $f = fopen($errLog, "w");
                fputs($f, "\n\n----------------------------------\n\n");
            }
        }
        
        foreach ($settings as $s => $v) {
            $oldSettings[$s] = ini_get($s);
            ini_set($s, $v); 
        }
        
        $this->log('Generator started ----------------');

        $writer = $this->getWriter();
        $writer->begin();
        
        if ($this->clearOutputDir && $this->outputDir) Ac_Cg_Util::cleanDir($this->outputDir);
        
        $todo = $this->parseGenEntities();
        
        foreach ($todo as $domain => $models) {
            $strat = $this->createStrategyForDomain($domain);
            $strat->generateCodeForModels($models);
            $strat->generateCommonCode();
        }
        
        $this->_outputBytes = $writer->getTotalSize();
        $this->_outputFiles = $writer->getFileCount();
        
        $this->log('Generator finished: '.$this->getOutputBytes().' bytes in '.$this->getOutputFiles().' files ----------------');
        
        foreach ($oldSettings as $s => $v) {
            if ($s == "error_log" && strlen(ini_get("open_basedir")) && !strlen($v)) {
                // workaround for open_basedir warning when restoring error_log option back to empty
                ini_restore($s);
            }
                else ini_set($s, $v); 
        }
        
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
    
    function runLint($path) {
        if (strlen($this->lintCommand)) {
            $ePath = escapeshellarg($path);
            exec(sprintf($this->lintCommand, $ePath), $out, $return);
            if (!!$return) {
                $this->log(implode("\n", $out)."\n", true);
            }
        }
    }
    
    /**
     * 
     * @param string|array $json
     * @param type $isFile
     * @param type $newName
     * @throws Ac_E_InvalidCall
     * @throws Exception
     * @throws type
     */
    function importDomain($json, $isFile = false, $newName = false) {
        if (is_array($json) && $isFile) 
            throw new Ac_E_InvalidCall ("WTF: is \$isFile is TRUE, \$json must be a filename, not an array");
        if ($isFile) {
            if (!is_file($json)) throw new Exception("\$json points to non-existent file: '$json'");
            $jsonData = file_get_contents($json);
            $json = json_decode($jsonData, true);
        } else {
            if (is_string($json)) $json = json_decode ($json, true);
        }
        if (!is_array($json)) throw new Ac_E_InvalidCall("\$json not an array");
        if (!isset($json['__class']) || $json['__class'] !== 'Ac_Cg_Domain') {
            throw new Exception ("'__class' => 'Ac_Cg_Domain' missing in \$json data");
        }
        if ($newName !== false) $json['name'] = $newName;
        $name = $json['name'];
        if (isset($this->_domains[$name])) {
            throw Ac_E_InvalidCall::alreadySuchItem("domain", $name);
        }
        $dom = new Ac_Cg_Domain($this, $name);
        $dom->unserializeFromArray($json);
        $this->_domains[$name] = $dom;
        return $dom;
    }

    /**
     * @return Ac_Cg_Writer_Abstract
     */
    function getWriter() {
        if (!$this->writer) $this->writer = new Ac_Cg_Writer_File(array('basePath' => $this->outputDir));
        elseif (!is_object($this->writer)) $this->writer = Ac_Prototyped::factory ($this->writer, 'Ac_Cg_Writer_Abstract');
        return $this->writer;
    }
    
    
}


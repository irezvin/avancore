<?php

class Ac_Cg_Generator {

    const CONTENT_DIR = '__DIR__';
    
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
    
    var $genEditable = true;
    
    var $genNonEditable = true;
    
    var $ovrEditable = true;
    
    var $ovrNonEditable = true;
    
    var $deployNonEditable = false;
    
    var $deployEditable = false;
    
    var $deployPath = false;
    
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
    
    var $importantLog = array();
    
    /**
     * @param string $configOrFileName name of file with static configuration of project
     */
    function __construct($configOrFileName = false, $runtimeOptions = array()) {
        if (is_array($configOrFileName)) $this->staticConfig = $configOrFileName;
        else {
            $this->_configFileName = $configOrFileName;
            if (strlen($this->_configFileName))
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
        foreach ($keys as $k) 
        {
            if (is_array($array[$k])) {$ak = Ac_Cg_Generator::_expandPaths($array[$k]); $array[$k] = $ak;}
            if ((($sp = strpos($k, '.')) !== false) && ($sp != 0)) {
                $value = $array[$k];
                unset($array[$k]);
                $path = explode('.', $k);
                Ac_Util::setArrayByPath($array, $path, $value);
            }
        }
        return $array;
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
        }
        if ($important || $this->verbose) {
            $c = $important? 'important' : '';
            if (PHP_SAPI === 'cli') {
                static $stderr;
                if (!$stderr) $stderr = fopen('php://stderr', 'a');
                fputs($stderr, $message."\n");
            } else {
                echo "<p class='message {$c}'>$message</p>\n";
            }
        }
        if ($important) $this->importantLog[] = $message;
    }
    
    function prepare() {
        
        foreach ($this->listDomains() as $domain) {
            $domainObject = $this->getDomain($domain);
            $domainObject->beforeGenerate();
        }
    }
    
    protected $runData = false;
    
    function begin() {
        if ($this->runData !== false) throw new Ac_E_InvalidUsage("Cannot ".__METHOD__." when hasBegan(); call end() first");
        
        $this->runData = array();
        
        $settings = array(
            'error_reporting' => E_ALL, 
            'html_errors' => false, 
            'display_errors' => 0, 
            'log_errors' => 1, 
            'ignore_repeated_errors' => true
        );
        
        if (strlen($this->logFileName)) {
            $errLog = $this->logFileName.'.errors.log';
            $settings['error_log'] = $errLog; 
        }
        
        $oldSettings = array();
        
        if ($this->overwriteLog && is_file($errLog)) {
            unlink($errLog);
        }
        
        foreach ($settings as $s => $v) {
            $oldSettings[$s] = ini_get($s);
            ini_set($s, $v); 
        }
        
        $this->log('Generator started ----------------');

        $writer = $this->getWriter();
        $writer->begin();
        
        if ($this->clearOutputDir && $this->outputDir) Ac_Cg_Util::cleanDir($this->outputDir);
        
        $this->runData['oldSettings'] = $oldSettings;
        $this->runData['writer'] = $this->getWriter();
    }
    
    function end() {
        if ($this->runData === false) throw new Ac_E_InvalidUsage("Cannot ".__METHOD__." without prior begin(); check with hasBegan() next time");
        
        $writer = $this->runData['writer'];
        
        $this->_outputBytes = $writer->getTotalSize();
        $this->_outputFiles = $writer->getFileCount();
        
        $this->log('Generator finished: '.$this->getOutputBytes().' bytes in '.$this->getOutputFiles().' files ----------------');
        
        $oldSettings = $this->runData['oldSettings'];
        
        foreach ($oldSettings as $s => $v) {
            if ($s == "error_log" && strlen(ini_get("open_basedir")) && !strlen($v)) {
                // workaround for open_basedir warning when restoring error_log option back to empty
                ini_restore($s);
            }
            else ini_set($s, $v); 
        }
        $this->runData = false;
    }
    
    function deploy() {
        
        // TODO: use REAL lists of editable and non-editable files instead of (hardcoded) /gen and /classes
        
        if ($this->deployNonEditable && $this->genNonEditable) {
            if (!strlen($this->deployPath)) throw new Ac_E_InvalidUsage("Cannot \$deployNonEditable without \$deployPath!");
            $this->writer->deploy($this->outputDir.'/gen', $this->deployPath, false, $err, true);
            if ($err) $this->log ($err, true);
        }
        if ($this->deployEditable && $this->genEditable) {
            if (!strlen($this->deployPath)) throw new Ac_E_InvalidUsage("Cannot \$deployEditable without \$deployPath!");
            $this->writer->deploy($this->outputDir.'/classes', dirname($this->deployPath).'/classes', true, $err, true);
            if ($err) $this->log ($err, true);
        }
        
    }
    
    function hasBegan() {
        return (bool) $this->runData;
    }
    
    function run($todo = false) {
        
        if (!$this->hasBegan()) $this->begin();
        
        foreach ($this->listDomains() as $name) {
            $dom = $this->getDomain($name);
            foreach ($dom->getAllTemplateInstances() as $tpl)
                $this->processTemplate($tpl);
        }
        
        $this->deploy();
        
        $this->end();
        
    }
    
    function __destruct() {
        if ($this->hasBegan()) {
            trigger_error("Destroying Generator that had begin() without end() called", E_USER_NOTICE);
            $this->end();
        }
    }
    
    /**
     * Processes a fully-instantiated and configured template
     */
    function processTemplate(Ac_Cg_Template $template) {
        if (!$this->hasBegan()) throw new Ac_E_InvalidUsage("Cannot ".__METHOD__."() without prior begin(). Check with hasBegan() next time!");
        $writer = $this->runData['writer'];
        foreach ($template->listFiles() as $n) {
            $skip = false;
            $p = $template->getFilePath($n);
            $editable = $template->fileIsUserEditable($n);
            if ($editable) {
                if (!$this->genEditable) $skip = true;
                $ovr = $this->ovrEditable;
            } else {
            	if (!$this->genNonEditable) $skip = true;
                $ovr = $this->ovrNonEditable;
            }
            if (!$skip) {
                $this->log($p.": writing file ");
                $template->outputFile($n, $writer, $ovr);
                if (strlen($this->outputDir)) $p = rtrim($this->outputDir, '/\\').'/'.$p;
                if ($this->lintify && is_file($p)) $this->runLint($p);
            }
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
    
    function syncAvancore($destDir, $destWebDir = false, $srcDir = false, $overwriteDest = false, $deleteFromDest = false) {
        if ($srcDir === false) $srcDir = dirname(__FILE__).'/../../..';
            if ($destWebDir == false) $destWebDir = $destDir.'/web/assets';

        if (!is_dir($srcDir)) throw new Ac_E_InvalidUsage("\$srcDir '{$srcDir}' does not exist");
        if (!is_file($srcDir.'/classes/Ac/Avancore.php'))
            throw new Ac_E_InvalidUsage("Directory '{$srcDir}' doesn't look like one that has Avancore installation");
        if (!is_dir($destDir)) mkdir($destDir, 0777, true);
        
        $sync = new Ac_Cg_DirSync(array(
            'srcDir' => $srcDir,
            'destDir' => $destDir,
        ));
        $sync->ensureDirsNotNested();
            
        $copy = array(
            'bin', 'classes', 'languages', 'obsolete', 'vendor', 'web/assets' => $destWebDir
        );
        foreach ($copy as $src => $dest) {
            if (is_numeric($src)) $src = $dest;
            $fullDest = rtrim($destDir, '/').'/'.ltrim($dest, '/');
            if (!is_dir($fullDest)) mkdir($fullDest, 0777, true);
            $ds = new Ac_Cg_DirSync(array(
                'dryRun' => false,
                'srcDir' => $srcDir.'/'.$src,
                'destDir' => $fullDest,
                'overwriteDest' => $overwriteDest,
                'deleteFromDest' => $deleteFromDest,
            ));
            $ds->run();
        }
    }
    
}

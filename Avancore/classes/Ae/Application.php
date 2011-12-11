<?php

if (!class_exists('Ae_Util', false)) require_once(dirname(__FILE__).'/Util.php');
if (!class_exists('Ae_Autoparams', false)) require_once(dirname(__FILE__).'/Autoparams.php');

abstract class Ae_Application extends Ae_Autoparams {
    
    private static $instances = array();
    
    private static $defaultInstanceIds = array();
    
    private static $defaultInstance = null;
    
    private static $ids = array();

    const stConstructing = 'constructing';
    const stInitializing = 'initializing';
    const stInitialized = 'initialized';
    
    protected $stage = self::stConstructing;
    
    protected $adapter = null;
    
    protected $id = false;

    protected $autoInitialize = true;
    
    protected $addIncludePaths = true;

    protected $legacyDatabase = null;
    
    protected $db = null;
    
    protected $cache = null;
    
    protected $controllers = array();
    
    protected $defaultControllerId = false;
    
    /**
     * To be redefined in concrete subclasses.
     * Placeholder that will link to current application' assets dir by default
     * @var string
     */
    protected $defaultAssetsPlaceholder = null;
    
    abstract function getAppClassFile();
    
    protected static function registerInstance(Ae_Application $instance) {
        if (!self::$defaultInstance) self::$defaultInstance = $instance;
        self::$instances[$class = get_class($instance)][$id = $instance->getId()] = $instance;
        self::$ids[$class][] = $id;
        self::$ids[$class] = array_unique(self::$ids[$class]);
        if (!isset(self::$defaultInstanceIds[$class]))
                self::$defaultInstanceIds[$class] = $id;
    }
    
    protected static function getNextId(Ae_Application $instance) {
        $id = 1;
        $class = get_class($instance);
        while (isset(self::$ids[$class]) && in_array($id, self::$ids[$class])) $id++;
        self::$ids[$class][] = $id;
        return $id;
    }
    
    /**
     * Finds default or specific instance of application with given class
     * Returns Ae_Application subclass' instance of NULL if any/such instance not registered
     * 
     * @param string $className
     * @param string|null $id   If null is provided, default instance is returned
     * @return Ae_Application
     */
    protected static function findInstance($className, $id = null) {
        if (is_null($id)) $id = isset(self::$defaultInstanceIds[$className])? self::$defaultInstanceIds[$className] : null;
        $res = null;
        if (!is_null($id) && isset(self::$instances[$className]) && isset(self::$instances[$className][$id])) 
            $res = self::$instances[$className][$id];
        return $res;
    }
    
    /**
     * @return Ae_Application
     */
    static function getDefaultInstance() {
        return self::$defaultInstance;
    }
    
    static function getInstance($className, $id = null) {
        if (!isset($className)) throw new Exception("\$className not provided");
        $res = self::findInstance($className, $id);
        if (!$res) $res = new $className(array('id' => is_null($id)? false : $id, 'autoRegister' => true));
        return $res;
    }
    
    static function listInstances($className = null) {
        if (strlen($className)) $res = isset(self::$instances[$className])? array_keys(self::$instances[$className]) : array();
        else {
            $res = array();
            foreach (self::$instances as $className => $list) {
                $res[$className] = array_keys($list);
            }
        }
        return $res;
    }

    function setAdapter(Ae_Application_Adapter $adapter) {
        if ($this->adapter) throw new Exception("Can setAdapter() only once");
        $this->adapter = $adapter;
        if (is_array($io = $this->adapter->getAppInitOptions())) $this->initFromOptions($io);
        if ($this->autoInitialize && $this->stage == self::stInitializing) $this->initialize();
    }
    
    /**
     * @return Ae_Application_Adapter
     */
    function getAdapter() {
        // TODO: find and create appropriate adapter if we know anything about the environment
        if (!$this->adapter) $this->adapter = new Ae_Application_Adapter(array('appClassFile' => $this->getAppClassFile()));
        return $this->adapter;
    }

    function setId($id) {
        if ($this->id) throw new Exception ("Can setId() only once (before it was automatically assigned)");
        $this->id = $id;
    }

    function getId() {
        if ($this->id === false) $this->id = Ae_Application::getNextId($this);
        return $this->id;
    }

    function setAutoInitialize($autoInitialize) {
        $this->autoInitialize = $autoInitialize;
    }

    function getAutoInitialize() {
        return $this->autoInitialize;
    }
    
    function __construct (array $options = array()) {
        $this->initFromOptions($options);
        $this->stage = self::stInitializing;
        if ($this->autoInitialize) $this->initialize();
    }
    
    function initialize() {
        $res = false;
        if ($this->stage === self::stInitializing) {
            $adapter = $this->getAdapter();
            if ($this->addIncludePaths) {
                Ae_Dispatcher::addIncludePath($adapter->getClassPaths());
            }
            $this->doOnInitialize();
            $this->stage = self::stInitialized;
            Ae_Application::registerInstance($this);
            $res = true;
        }
        return $res;
    }
    
    protected function doOnInitialize() {
    }

    function setLegacyDatabase(Ae_Database $database = null) {
        $this->legacyDatabase = $database;
    }

    /**
     * @return Ae_Database
     */
    function getLegacyDatabase() {
        if (!$this->legacyDatabase) {
            $db = $this->adapter->getLegacyDatabasePrototype();
            if (is_array($db) && $db) {
                if (!isset($db['tmpDir'])) $db['tmpDir'] = $this->adapter->getVarTmpPath();
            }
            if ($db) $this->legacyDatabase = Ae_Autoparams::factory ($db, 'Ae_Database');
        }
        return $this->legacyDatabase;
    }    

    function setDb(Ae_Sql_Db $db = null) {
        $this->db = $db;
    }

    /**
     * @return Ae_Sql_Db
     */
    function getDb() {
        if ($this->db === null) {
            $this->db = false;
            $db = $this->adapter->getDbPrototype();
            if ($db) $this->db = Ae_Autoparams::factory ($db, 'Ae_Sql_Db');
                elseif (($leg = $this->getLegacyDatabase())) $this->db = new Ae_Sql_Db_Ae($leg);
        }
        return $this->db;
    }

    function setCache(Ae_Cache $cache = null) {
        $this->cache = $cache;
    }

    /**
     * @return Ae_Cache
     */
    function getCache() {
        if ($this->cache === null) {
            $cache = $this->adapter->getCachePrototype();
            if ($cache) $this->cache = Ae_Autoparams::factory ($cache, 'Ae_Cache');
        }
        return $this->cache;
    }

    /**
     * @return array
     */
    function listMappers() {
    }
    
    /**
     * @return Ae_Model_Mapper
     */
    function getMapper($id) {
    }
    
    /**
     * @return array
     */
    function listControllers() {
        return array_keys($this->controllers);
    }
    
    /**
     * @return Ae_Controller
     */
    function getController($id) {
        if (isset($this->controllers[$id])) {
            if (!is_object($this->controllers[$id])) {
                    $proto = $this->controllers[$id];
                    if (!is_array($proto)) $proto = array('class' => $proto);
                    if (!isset($proto['application'])) $proto['application'] = $this;
                    $this->controllers[$id] = Ae_Autoparams::factory($proto, 'Ae_Controller');
            }
            $res = $this->controllers[$id];
        } else {
            throw new Exception("No such controller with id '$id' registered in ".get_class($this));
        }
        return $res;
    }
    
    /**
     * @return Ae_UserConfig
     */
    function getConfig() {
    }
    
    function setConfig(Ae_UserConfig $config) {
    }
    
    function getConfigPrototype() {
    }
    
    function createOutput() {
        $output = $this->getAdapter()->getOutputPrototype();
        $output = Ae_Autoparams::factory($output, 'Ae_Output');
        // TODO: add asset placeholders to output
        return $output;
    }
    
    /**
     * @return Ae_Controller_Response
     * @param bool $noOutput Just return the Response, don't try to output it
     * @param Ae_Output $output Or its prototype. If not provided, Adapter will be used to retrieve Output prototype
     */
    function processRequest($noOutput = false, Ae_Output $output = null) {
        if ($this->defaultControllerId === false) throw new Exception("\$defaultControllerId property not set");
        $controller = $this->getController($this->defaultControllerId);
        $response = $controller->getResponse();
        $res = $response;
        if (!$noOutput) {
            if (!$output) $output = $this->createOutput();
            $output->outputResponse($res);
        }
        return $res;
    }
    
    function getDefaultAssetPlaceholders() {
        $res = array();
        if (strlen($this->defaultAssetsPlaceholder)) $res[$this->defaultAssetsPlaceholder] = $this->adapter->getWebAssetsUrl();
        return $res;
    }
    
    function getAssetPlaceholders() {
        $res = array_merge($this->getDefaultAssetPlaceholders(), $this->adapter->getAssetPlaceholders());
        return $res;
    }
    
}
<?php

if (!class_exists('Ac_Util', false)) require_once(dirname(__FILE__).'/Util.php');
if (!class_exists('Ac_Prototyped', false)) require_once(dirname(__FILE__).'/Prototyped.php');

abstract class Ac_Application extends Ac_Prototyped implements Ac_I_ServiceProvider {
    
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
    
    protected $mappers = false;
    
    protected $services = array();
    
    /**
     * To be redefined in concrete subclasses.
     * Placeholder that will link to current application' assets dir by default
     * @var string
     */
    protected $defaultAssetsPlaceholder = null;
    
    abstract function getAppClassFile();
    
    protected static function registerInstance(Ac_Application $instance) {
        if (!self::$defaultInstance) self::$defaultInstance = $instance;
        self::$instances[$class = get_class($instance)][$id = $instance->getId()] = $instance;
        self::$ids[$class][] = $id;
        self::$ids[$class] = array_unique(self::$ids[$class]);
        if (!isset(self::$defaultInstanceIds[$class]))
                self::$defaultInstanceIds[$class] = $id;
    }
    
    function setDefaultInstance(Ac_Application $instance) {
        self::$defaultInstance = $instance;
    }
    
    protected static function getNextId(Ac_Application $instance) {
        $id = 1;
        $class = get_class($instance);
        while (isset(self::$ids[$class]) && in_array($id, self::$ids[$class])) $id++;
        self::$ids[$class][] = $id;
        return $id;
    }
    
    /**
     * Finds default or specific instance of application with given class
     * Returns Ac_Application subclass' instance of NULL if any/such instance not registered
     * 
     * @param string $className
     * @param string|null $id   If null is provided, default instance is returned
     * @return Ac_Application
     */
    protected static function findInstance($className, $id = null) {
        if (is_null($id)) $id = isset(self::$defaultInstanceIds[$className])? self::$defaultInstanceIds[$className] : null;
        $res = null;
        if (!is_null($id) && isset(self::$instances[$className]) && isset(self::$instances[$className][$id])) 
            $res = self::$instances[$className][$id];
        return $res;
    }
    
    /**
     * @return Ac_Application
     */
    static function getDefaultInstance() {
        return self::$defaultInstance;
    }
    
    static function getApplicationInstance($className, $id = null) {
        if (!isset($className)) throw new Exception("\$className not provided");
        $res = self::findInstance($className, $id);
        if (!$res) $res = new $className(array('id' => is_null($id)? false : $id/*, 'autoRegister' => true*/));
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

    function setAdapter(Ac_Application_Adapter $adapter) {
        if ($this->adapter) throw new Exception("Can setAdapter() only once");
        $this->adapter = $adapter;
        $this->adapter->setAppClassFile($this->getAppClassFile());
        if (is_array($io = $this->adapter->getAppInitOptions())) $this->initFromPrototype($io);
        if ($this->autoInitialize && $this->stage == self::stInitializing) $this->initialize();
    }
    
    /**
     * @return Ac_Application_Adapter
     */
    function getAdapter() {
        // TODO: find and create appropriate adapter if we know anything about the environment
        if (!$this->adapter) $this->adapter = new Ac_Application_Adapter(array('appClassFile' => $this->getAppClassFile()));
        return $this->adapter;
    }

    function setId($id) {
        if ($this->id) throw new Exception ("Can setId() only once (before it was automatically assigned)");
        $this->id = $id;
    }

    function getId() {
        if ($this->id === false) $this->id = Ac_Application::getNextId($this);
        return $this->id;
    }

    function setAutoInitialize($autoInitialize) {
        $this->autoInitialize = $autoInitialize;
    }

    function getAutoInitialize() {
        return $this->autoInitialize;
    }
    
    function __construct (array $options = array()) {
        $this->initFromPrototype($options);
        $this->stage = self::stInitializing;
        if ($this->autoInitialize) $this->initialize();
    }
    
    function initialize() {
        $res = false;
        if ($this->stage === self::stInitializing) {
            $adapter = $this->getAdapter();
            if ($this->addIncludePaths) {
                Ac_Dispatcher::addIncludePath($adapter->getClassPaths());
                if (strlen($gp = $adapter->getGenPath()))Ac_Dispatcher::addIncludePath($adapter->getGenPath());
            }
            $this->doOnInitialize();
            $this->stage = self::stInitialized;
            Ac_Application::registerInstance($this);
            $res = true;
        }
        return $res;
    }
    
    protected function doOnInitialize() {
    }

    function setLegacyDatabase(Ac_Legacy_Database $database = null) {
        $this->legacyDatabase = $database;
    }

    /**
     * @return Ac_Legacy_Database
     */
    function getLegacyDatabase() {
        if (!$this->legacyDatabase) {
            $db = $this->adapter->getLegacyDatabasePrototype();
            if (is_array($db) && $db) {
                $db = Ac_Util::m(array('__construct' => array('config' => array('tmpDir' => $this->adapter->getVarTmpPath()))), $db);
            }
            if ($db) $this->legacyDatabase = Ac_Prototyped::factory ($db, 'Ac_Legacy_Database');
        }
        return $this->legacyDatabase;
    }    

    function setDb(Ac_Sql_Db $db = null) {
        $this->db = $db;
    }

    /**
     * @return Ac_Sql_Db
     */
    function getDb() {
        if ($this->db === null) {
            $this->db = false;
            $db = $this->adapter->getDbPrototype();
            if ($db) $this->db = Ac_Prototyped::factory ($db, 'Ac_Sql_Db');
                elseif (($leg = $this->getLegacyDatabase())) $this->db = new Ac_Sql_Db_Ae($leg);
        }
        return $this->db;
    }

    function setCache(Ac_Cache $cache = null) {
        $this->cache = $cache;
    }

    /**
     * @return Ac_Cache
     */
    function getCache() {
        if ($this->cache === null) {
            $cache = $this->adapter->getCachePrototype();
            if ($cache) $this->cache = Ac_Prototyped::factory ($cache, 'Ac_Cache');
        }
        return $this->cache;
    }

    /**
     * @return array
     */
    function listMappers() {
        if ($this->mappers === false) {
            $this->mappers = Ac_Util::toArray($this->doGetMapperPrototypes());
        }
        return array_keys($this->mappers);
    }
    
    function hasMapper($id) {
        return in_array($id, $this->listMappers());
    }
    
    function addMapper(Ac_Model_Mapper $mapper) {
        $id = $mapper->getId();
        if (in_array($id, $this->listMappers())) throw new Exception("Mapper '$id' already registered in ".get_class($this));
        $this->mappers[$id] = $mapper;
        $mapper->setApplication($this);
    }
    
    protected function doGetMapperPrototypes() {
        return array();
    }
    
    /**
     * @return Ac_Model_Mapper
     */
    function getMapper($id, $dontThrow = false) {
        if (in_array($id, $this->listMappers())) {
            if (!is_object($this->mappers[$id])) {
                $defaults = $this->mappers[$id];
                if (!is_array($defaults)) $defaults = array('class' => $defaults);
                $defaults['application'] = $this;
                $this->mappers[$id] = Ac_Prototyped::factory($defaults, 'Ac_Model_Mapper');
            }
            $res = $this->mappers[$id];
        } else {
            if (!$dontThrow) throw new Exception("No such mapper '{$id}' in ".get_class($this));
            else $res = null;
        }
        return $res;
    }
    
    /**
     * @return array
     */
    function listControllers() {
        return array_keys($this->controllers);
    }
    
    /**
     * @return Ac_Legacy_Controller
     */
    function getController($id) {
        if (isset($this->controllers[$id])) {
            if (!is_object($this->controllers[$id])) {
                    $proto = $this->controllers[$id];
                    if (!is_array($proto)) $proto = array('class' => $proto);
                    if (!isset($proto['application'])) $proto['application'] = $this;
                    $this->controllers[$id] = Ac_Prototyped::factory($proto, 'Ac_Legacy_Controller');
            }
            $res = $this->controllers[$id];
        } else {
            throw new Exception("No such controller with id '$id' registered in ".get_class($this));
        }
        return $res;
    }
    
    /**
     * @return Ac_UserConfig
     */
    function getConfig() {
    }
    
    function setConfig(Ac_UserConfig $config) {
    }
    
    function getConfigPrototype() {
    }
    
    function createOutput() {
        $output = $this->getAdapter()->getOutputPrototype();
        $output = Ac_Prototyped::factory($output, 'Ac_Legacy_Output');
        // TODO: add asset placeholders to output
        return $output;
    }
    
    /**
     * @return Ac_Legacy_Controller_Response
     * @param bool $noOutput Just return the Response, don't try to output it
     * @param Ac_Legacy_Output $output Or its prototype. If not provided, Adapter will be used to retrieve Output prototype
     */
    function processRequest($noOutput = false, Ac_Legacy_Output $output = null) {
        if ($this->defaultControllerId === false) throw new Exception("\$defaultControllerId property not set");
        $controller = $this->getController($this->defaultControllerId);
        $response = $controller->getResponse();
        $res = $response;
        $res->setApplication($this);
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
    
    function createDefaultContext(Ac_Cr_Controller $forController) {
        return new Ac_Cr_Context();
    }
    
    
    function listServices() {
        return array_keys($this->services);
    }
    
    function setServices(array $services, $removeExisting = false) {
        if ($removeExisting) $this->services = $services;
        else Ac_Util::ms($this->services, $services);
    }
    
    function getServices() {
        return $this->services;
    }
    
    function setService($id, $prorotypeOrInstance, $overwrite = false) {
        if (isset($this->services[$id]) && !$overwrite) throw new Exception("Service '\$id' is already registered");
        $this->services[$id] = $prorotypeOrInstance;
    }
    
    function deleteService($id, $dontThrow = false) {
        if (isset($this->services[$id])) unset($this->services[$id]); 
            elseif (!$dontThrow) throw new Exception("No such service: '\$id'");
    }
    
    function getService($id, $dontThrow = false) {
        $res = false;
        if (isset($this->services[$id])) {
            if (!is_object($this->services[$id])) $this->services[$id] = Ac_Prototyped::factory($this->services[$id]);
            $res = $this->services[$id];
        } elseif ($this->adapter && $s = $this->adapter->getService($id, true)) {
            $res = $s;
        } elseif (!$dontThrow) throw new Exception("No such service: '\$id'");
        return $res;
    }
    
    
}
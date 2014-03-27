<?php

if (!class_exists('Ac_Util', false)) require_once(dirname(__FILE__).'/Util.php');
if (!class_exists('Ac_I_ServiceProvider', false)) require_once(dirname(__FILE__).'/I/ServiceProvider.php');
if (!class_exists('Ac_Prototyped', false)) require_once(dirname(__FILE__).'/Prototyped.php');

abstract class Ac_Application extends Ac_Mixin_WithEvents implements Ac_I_ServiceProvider {

    /**
     * onInitialize
     */
    const EVENT_ON_INITIALIZE = 'onInitialize';
    
    /**
     * onGetMapperPrototypes (array & $mapperPrototypes)
     */
    const EVENT_ON_GET_MAPPER_PROTOTYPES = 'onGetMapperPrototypes';
    
    /**
     * onRegisterMappers (array $mappers)
     */
    const EVENT_ON_REGISTER_MAPPERS = 'onRegisterMappers';
    
    /**
     * onGetControllerPrototypes(array & $controllerPrototypes)
     */
    const EVENT_ON_GET_CONTROLLER_PROTOTYPES = 'onGetControllerPrototypes';
    
    /**
     * onRegisterControllers (array $controllers)
     */
    const EVENT_ON_REGISTER_CONTROLLERS = 'onRegisterControllers';
    
    /**
     * onMapperNotFound ($id, & $instance = null)
     */
    const EVENT_ON_MAPPER_NOT_FOUND = 'onMapperNotFound';
    
    /**
     * onControllerNotFound ($id, & $instance = null)
     */
    const EVENT_ON_CONTROLLER_NOT_FOUND = 'onControllerNotFound';
    
    const STAGE_CONSTRUCTING = 'constructing';
    const STAGE_INITIALIZING = 'initializing';
    const STAGE_INITIALIZED = 'initialized';    
    
    private static $instances = array();
    
    private static $defaultInstanceIds = array();
    
    private static $defaultInstance = null;
    
    private static $lastInstance = null;
    
    private static $ids = array();

    protected $stage = self::STAGE_CONSTRUCTING;
    
    /**
     * @var Ac_Application_Adapter
     */
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
     * @var Ac_Legacy_Controller_Context
     */
    protected $defaultLegacyContext = false;
    
    /**
     * To be redefined in concrete subclasses.
     * Placeholder that will link to current application' assets dir by default
     * @var string
     */
    protected $defaultAssetsPlaceholder = null;

    /**
     * @var array
     */
    protected $extraAssetPlaceholders = array();
    
    /**
     * @var Ac_Url
     */
    protected $frontControllerUrl = false;
    
    /**
     * @var Ac_Url
     */
    protected $siteUrl = false;
    
    /**
     * @var Ac_I_User
     */
    protected $user = false;
    
    /**
     * @var Ac_I_Mail_Sender
     */
    protected $mailSender = false;
    
    abstract function getAppClassFile();
    
    protected static function registerInstance(Ac_Application $instance) {
        if (!self::$defaultInstance) self::$defaultInstance = $instance;
        self::$instances[$class = get_class($instance)][$id = $instance->getId()] = $instance;
        self::$ids[$class][] = $id;
        self::$ids[$class] = array_unique(self::$ids[$class]);
        if (!isset(self::$defaultInstanceIds[$class]))
                self::$defaultInstanceIds[$class] = $id;
        self::$lastInstance = $instance;
    }
    
    static function setDefaultInstance(Ac_Application $instance) {
        self::$defaultInstance = $instance;
        self::$defaultInstanceIds[get_class($instance)] = $instance->getId();
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
     * If setDefaultInstance() was called, returns previously set 'defaultInstance'.
     * Otherwise returns last registered instance.
     * 
     * @return Ac_Application
     */
    static function getDefaultInstance() {
        if (!self::$defaultInstance) $res = self::$lastInstance;
            else $res = self::$defaultInstance;
        return $res;
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
        if ($this->autoInitialize && $this->stage == self::STAGE_INITIALIZING) $this->initialize();
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
        parent::__construct($options);
        $this->stage = self::STAGE_INITIALIZING;
        if ($this->autoInitialize) $this->initialize();
    }
    
    function initialize() {
        $res = false;
        if ($this->stage === self::STAGE_INITIALIZING) {
            $adapter = $this->getAdapter();
            if ($this->addIncludePaths) {
                Ac_Util::addIncludePath($adapter->getClassPaths());
                if (strlen($gp = $adapter->getGenPath()))Ac_Util::addIncludePath($adapter->getGenPath());
            }
            $this->doOnInitialize();
            $this->triggerEvent(self::EVENT_ON_INITIALIZE);
            $this->stage = self::STAGE_INITIALIZED;
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
                $db = Ac_Util::m(array('__construct' => array('options' => array('tmpDir' => $this->adapter->getVarTmpPath()))), $db);
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
    
    function getAppRootDir() {
        return $this->adapter->getAppRootDir();
    }

    function setCache(Ac_Cache $cache = null) {
        $this->cache = $cache;
    }

    /**
     * Returns Ac_Cache object. If no cache is injected by setCache or Adapter does not provides any
     * cache prototype, creates disabled (dummy) cache instance.
     * 
     * @return Ac_Cache
     */
    function getCache() {
        if ($this->cache === null) {
            $cache = $this->adapter->getCachePrototype();
            if (is_array($cache) || strlen($cache)) $this->cache = Ac_Prototyped::factory ($cache, 'Ac_Cache');
            else $this->cache = Ac_Prototyped::factory(array('class' => 'Ac_Cache', 'enabled' => false));
        }
        return $this->cache;
    }

    /**
     * @return array
     */
    function listMappers() {
        if ($this->mappers === false) {
            $this->initMappers();
        }
        return array_keys($this->mappers);
    }
    
    protected function initMappers() {
        $this->mappers = array();
        $mappers = Ac_Util::toArray($this->doGetMapperPrototypes());
        $this->triggerEvent(self::EVENT_ON_GET_MAPPER_PROTOTYPES, array(& $mappers));
        $this->mappers = $mappers;
        $reg = array();
        foreach ($this->mappers as $id => $m) {
            if (is_object($m)) {
                if ($m instanceof Ac_Model_Mapper) $reg[$id] = $m;
                else throw new Ac_E_InvalidImplementation("All items in \$mappers collection "
                    . "must be Ac_Model_Mapper descendants, but \$mappers['{$id}'] is ".get_class($m));
                $reg->setId($id);
                $reg->setApplication($this);
            }
        }
        if ($reg) $this->triggerEvent(self::EVENT_ON_REGISTER_MAPPERS($reg));
    }
    
    function hasMapper($id) {
        return in_array($id, $this->listMappers());
    }
    
    function addMapper(Ac_Model_Mapper $mapper) {
        $id = $mapper->getId();
        if (in_array($id, $this->listMappers())) 
            throw Ac_E_InvalidCall::alreadySuchItem('Mapper', $id);
        $this->mappers[$id] = $mapper;
        $mapper->setApplication($this);
        $this->triggerEvent(self::EVENT_ON_REGISTER_MAPPERS, array($id => $mapper));
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
                $defaults['id'] = $id;
                $this->mappers[$id] = Ac_Prototyped::factory($defaults, 'Ac_Model_Mapper');
                $this->triggerEvent(self::EVENT_ON_REGISTER_MAPPERS, array($id => $this->mappers[$id]));
            }
            $res = $this->mappers[$id];
        } else {
            $res = null;
            $this->triggerEvent(self::EVENT_ON_MAPPER_NOT_FOUND, array($id, & $res));
            if ($res) {
                $res->setId($id);
                $this->addMapper($res);
            } else {
                if (!$dontThrow) throw new Exception("No such mapper '{$id}' in ".get_class($this));
            }
        }
        return $res;
    }
    
    /**
     * @return array
     */
    function listControllers() {
        return array_keys($this->controllers);
    }

    function setDefaultLegacyContext(Ac_Legacy_Controller_Context $defaultLegacyContext) {
        $this->defaultLegacyContext = $defaultLegacyContext;
    }

    /**
     * @return Ac_Legacy_Controller_Context
     */
    function getDefaultLegacyContext() {
        return $this->defaultLegacyContext;
    }    
    
    /**
     * @return Ac_Legacy_Controller
     */
    function getController($id) {
        if (isset($this->controllers[$id])) {
            if (!is_object($this->controllers[$id])) {
                    $proto = $this->controllers[$id];
                    if (!is_array($proto)) $proto = array('class' => $proto);
                    if ($this->defaultLegacyContext && !isset($proto['context'])) $proto['context'] = $this->defaultLegacyContext->cloneObject();
                    if (!isset($proto['application'])) $proto['application'] = $this;
                    $this->controllers[$id] = Ac_Prototyped::factory($proto, 'Ac_Legacy_Controller');
            }
            $res = $this->controllers[$id];
        } else {
            throw new Exception("No such controller with id '$id' registered in ".get_class($this));
        }
        return $res;
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
    
    function getAssetPlaceholders($fromAllApplications = false) {
        $res = array_merge($this->getDefaultAssetPlaceholders(), $this->adapter->getAssetPlaceholders());
        if ($this->extraAssetPlaceholders) {
            $res = array_merge($res, $this->extraAssetPlaceholders);
        }
        if ($fromAllApplications) {
            $r = $res;
            foreach (Ac_Application::listInstances() as $cn => $list) {
                foreach ($list as $id) {
                    $instance = Ac_Application::getApplicationInstance($cn, $id);
                    $r = array_merge($instance->getAssetPlaceholders(false), $r);
                }
            }
            $res = $r;
        }
        return $res;
    }

    function setExtraAssetPlaceholders(array $extraAssetPlaceholders) {
        $this->extraAssetPlaceholders = $extraAssetPlaceholders;
    }

    /**
     * @return array
     */
    function getExtraAssetPlaceholders() {
        return $this->extraAssetPlaceholders;
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
        if (is_object($this->services[$id])) Ac_Accessor::setObjectProperty($this->services[$id], 'application', $this);
    }
    
    function deleteService($id, $dontThrow = false) {
        if (isset($this->services[$id])) unset($this->services[$id]); 
            elseif (!$dontThrow) throw new Exception("No such service: '\$id'");
    }
    
    function getService($id, $dontThrow = false) {
        $res = false;
        if (isset($this->services[$id])) {
            if (!is_object($this->services[$id])) {
                $this->services[$id] = Ac_Prototyped::factory($this->services[$id]);
                Ac_Accessor::setObjectProperty($this->services[$id], 'application', $this);
            }
            $res = $this->services[$id];
        } elseif ($this->adapter && $s = $this->adapter->getService($id, true)) {
            $res = $s;
            Ac_Accessor::setObjectProperty($res, 'application', $this);
        } elseif (!$dontThrow) throw new Exception("No such service: '{$id}");
        return $res;
    }
    
    /**
     * @return Ac_Flags
     */
    function getFlags() {
        return $this->getService('flags');
    }

    /**
     * Sets front controller URL
     * @param Ac_Url $url
     */
    function setFrontControllerUrl(Ac_Url $url = null) {
        $this->frontControllerUrl = $url? $url : false;
    }

    /**
     * Returns front controller URL (defaults to Ac_Appliction_Adapter::getWebUrl())
     * @return Ac_Url
     */
    function getFrontControllerUrl() {
        if ($this->frontControllerUrl === false) {
            $wu = $this->getAdapter()->getWebUrl();
            if ($wu) $res = new Ac_Url($wu);
            else $res = Ac_Url::guess ();
        } else {
            $res = clone $this->frontControllerUrl;
        }
        return $res;
    }
    
    /**
     * Returns website url (defaults to Ac_Application_Adapter::getWebUrl())
     * @return Ac_Url
     */
    function getSiteUrl() {
        if ($this->siteUrl === false) {
            $res = new Ac_Url($this->getAdapter()->getSiteUrl());
        } else {
            $res = clone $this->siteUrl;
        }
        return $res;
    }
    
    /**
     * Sets website url (defaults to Ac_Application_Adapter::getSiteUrl())
     * @param type $siteUrl
     */
    function setSiteUrl(Ac_Url $siteUrl = null) {
        $this->siteUrl = $siteUrl? $siteUrl : false;
    }

    function setUser(Ac_I_User $user) {
        $this->user = $user;
    }

    /**
     * @return Ac_I_User
     */
    function getUser() {
        return $this->user;
    }    
    
    function setMailSender(Ac_I_MailSender $mailSender) {
        $this->mailSender = $mailSender;
    }

    /**
     * @return Ac_I_MailSender
     */
    function getMailSender() {
        if ($this->mailSender === false) {
            $this->mailSender = Ac_Prototyped::factory($this->adapter->getMailSenderPrototype(), 'Ac_I_MailSender');
            if ($this->mailSender instanceof Ac_I_Mail_Sender_WithDump) {
                $dd = $this->mailSender->getDumpDir();
                if ($dd === false || is_null($dd)) $this->mailSender->setDumpDir($this->adapter->getVarDumpsPath());
            }
        }
        return $this->mailSender;
    }    
    
}

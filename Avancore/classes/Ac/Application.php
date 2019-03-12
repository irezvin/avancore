<?php

if (!class_exists('Ac_Util', false)) require_once(dirname(__FILE__).'/Util.php');
if (!class_exists('Ac_Prototyped', false)) require_once(dirname(__FILE__).'/Prototyped.php');

abstract class Ac_Application extends Ac_Mixin_WithEvents {

    /**
     * onInitialize
     */
    const EVENT_ON_INITIALIZE = 'onInitialize';
    
    /**
     * onGetCompronentPrototypes(array & $componentPrototypes)
     */
    const EVENT_ON_GET_COMPONENT_PROTOTYPES = 'onGetComponentPrototypes';
    
    /**
     * onRegisterComponents (array $components)
     */
    const EVENT_ON_REGISTER_COMPONENTS = 'onRegisterComponents';
    
    /**
     * onComponentNotFound ($id, & $instance = null)
     */
    const EVENT_ON_COMPONENT_NOT_FOUND = 'onComponentNotFound';
    
    /**
     * onComponentDeleted ($id, $instance)
     */
    const EVENT_ON_COMPONENT_DELETED = 'onComponentDeleted';
    
    const CORE_COMPONENT_DB = 'core.db';
    const CORE_COMPONENT_LEGACY_DB = 'core.legacyDb';
    const CORE_COMPONENT_FLAGS = 'core.flags';
    const CORE_COMPONENT_CACHE = 'core.cache';
    const CORE_COMPONENT_MAIL_SENDER = 'core.mailSender';
    const CORE_COMPONENT_RELATION_PROVIDER_EVALUATOR = 'core.relationProviderEvaluator';
    const CORE_COMPONENT_MANAGER_CONFIG_SERVICE = 'core.managerConfigService';
    const CORE_COMPONENT_URL_MAPPER = 'core.urlMapper';
    const CORE_COMPONENT_FRONT_CONTROLLER = 'core.frontController';
    
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
     * @var Ac_I_User
     */
    protected $user = false;
    
    /**
     * @var array Mixables that will be initialized adter application has been init'
     */
    protected $deferredMixables = array();
    
    protected $deferredCoreMixables = array();
    
    protected $deferredMixablesInit = false;
    
    protected $initsCoreMixables = false;
    
    protected $components = false;
    
    protected $componentListCache = array();
    
    /**
     * @var array
     */
    protected $componentAliases = array();
    
    /**
     * @var array Properties that will be after deferred mixables are created
     */
    protected $deferredMixableProperties = array();
    
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
                if (strlen($gp = $adapter->getGenPath()))
                    Ac_Util::addIncludePath($adapter->getGenPath());
            }
            $this->initDeferredMixables();
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

    function setLegacyDatabase(Ac_Legacy_Database $database) {
        $this->legacyDatabase = $this->addComponent($database, self::CORE_COMPONENT_LEGACY_DB);
    }

    /**
     * @return Ac_Legacy_Database
     */
    function getLegacyDatabase() {
        if (!$this->legacyDatabase) {
            $this->legacyDatabase = $this->getComponent(self::CORE_COMPONENT_LEGACY_DB, 'Ac_Legacy_Database', true);
        }
        return $this->legacyDatabase;
    }

    function setDb(Ac_Sql_Db $db = null) {
        $this->addComponent($db, self::CORE_COMPONENT_DB);
    }

    /**
     * @return Ac_Sql_Db
     */
    function getDb() {
        if ($this->db === null) {
            $this->db = $this->getComponent(self::CORE_COMPONENT_DB, 'Ac_Sql_Db', true);
            if (!$this->db && $leg = $this->getLegacyDatabase()) {
                $this->db = $this->addComponent(new Ac_Sql_Db_Ae($leg), self::CORE_COMPONENT_DB);
            }
        }
        return $this->db;
    }
    
    function getAppRootDir() {
        return $this->adapter->getAppRootDir();
    }

    function setCache(Ac_Cache_Abstract $cache = null) {
        $this->addComponent($cache, self::CORE_COMPONENT_CACHE);
    }

    /**
     * Returns Ac_Cache_Abstract object. If no cache is injected by setCache or Adapter does not provides any
     * cache prototype, creates disabled (dummy) cache instance.
     * 
     * @return Ac_Cache_Abstract
     */
    function getCache() {
        return $this->getComponent(self::CORE_COMPONENT_CACHE, 'Ac_Cache_Abstract');
    }

    /**
     * @return array
     */
    function listMappers() {
        return $this->listComponents('Ac_Model_Mapper');
    }
    
    function hasMapper($id) {
        return in_array($id, $this->listMappers());
    }
    
    /**
     * @return Ac_Model_Mapper
     */
    function getMapper($id, $dontThrow = false) {
        return $this->getComponent($id, 'Ac_Model_Mapper', $dontThrow);
    }
    
    function setComponentAliases(array $componentAliases, $add = false) {
        if ($add) Ac_Util::ms($this->componentAliases, $componentAliases);
            else $this->componentAliases = $componentAliases;
    }

    /**
     * @return array
     */
    function getComponentAliases() {
        return $this->componentAliases;
    }    

    /**
     * @return array
     */
    function listComponents($baseClass = null) {
        if ($this->components === false) {
            $this->initComponents();
        }
        if ($baseClass === null) return array_keys($this->components);
        if (isset($this->componentListCache[$baseClass])) return $this->componentListCache[$baseClass];
        $res = array();
        foreach ($this->components as $k => $item) {
            if (is_object($item)) {
                if ($item instanceof $baseClass) $res[] = $k;
                continue;
            }
            if (is_string($item)) $componentClass = $item;
            elseif (is_array($item) && isset($item['class'])) $componentClass = $item['class'];
            if (
                $baseClass === $componentClass 
                || in_array($baseClass, class_implements($componentClass))
                || in_array($baseClass, class_parents($componentClass))
            ) $res[] = $k;
        }
        $this->componentListCache[$baseClass] = $res;
        return $res;
    }
    
    function getComponentClass($id) {
        if (isset($this->components[$id]) && !is_object($this->components[$id])) {
            if (is_scalar($this->components[$id])) return $this->components[$id];
            if (isset($this->components[$id]['class'])) return $this->components[$id]['class'];
        }
        $component = $this->getComponent($id, null, true);
        if (!$component) return;
        return get_class($component);
    }
    
    protected function registerComponents(array $components) {
        if (!$components) return;
        foreach ($components as $id => $reg) {
            if ($reg instanceof Ac_I_ApplicationComponent) $reg->setApplication($this);
            if ($reg instanceof Ac_I_NamedApplicationComponent) $reg->setId($id);
        }
        $this->triggerEvent(self::EVENT_ON_REGISTER_COMPONENTS, $components);
    }
    
    protected function initComponents() {
        $this->components = array();
        $components = Ac_Util::toArray($this->doGetComponentPrototypes());
        Ac_Util::ms($components, $this->adapter->getComponentPrototypes());
        $this->triggerEvent(self::EVENT_ON_GET_COMPONENT_PROTOTYPES, array(& $components));
        $this->components = array_filter($components); // strip empty entries
        $this->componentListCache = array();        
        $reg = array_filter($this->components, 'is_object');
        if ($reg) $this->registerComponents($reg);
    }
    
    function hasComponent($id) {
        return in_array($id, $this->listComponents());
    }    
    
    function addComponent($component, $id = null) {
        if (is_null($id)) {
            $class = null;
            if (is_object($component)) {
                if ($component instanceof Ac_I_NamedApplicationComponent) $id = $component->getId();
                $class = get_class($component);
            } elseif (is_array($component)) {
                if (isset($component['id']) && is_string($component['id'])) {
                    $id = $component['id'];
                }
                if (isset($component['class']) && is_string($component['class'])) {
                    $id = $component['class'];
                }
            } elseif (is_string($component)) {
                $class = $component;
            }
            if (!strlen($id)) $id = $class;
            if (!strlen($id)) 
                throw new Ac_E_InvalidCall(__METHOD__.": Cannot determine component id or class; provide \$id must be provided");
        }
        if (in_array($id, $this->listComponents())) {
            throw Ac_E_InvalidCall::alreadySuchItem('Component', $id);
        } else {
            $this->components[$id] = $component;
            if (is_object($component)) {
                $this->registerComponents(array($id => $component));
            }
        }
        $this->componentListCache = array();
        return $this->components[$id];
    }
    
    function deleteComponent($id) {
        $component = $this->components[$id];
        $this->componentListCache = array();
        $this->components[$id] = null;
        if (is_object($component))
            $this->triggerEvent (self::EVENT_ON_COMPONENT_DELETED, $id, $component);
    }

    /**
     * @deprecated
     */
    protected function doGetMapperPrototypes() {
        return array();
    }
    
    protected function doGetComponentPrototypes() {
        $adapter = $this->getAdapter();
        $cachePrototype = array('class' => 'Ac_Cache');
        if ($cc = $this->adapter->getCachePrototype()) Ac_Util::ms($cachePrototype, $cc);
        else $cachePrototype['enabled'] = false;

        $res = array(
            self::CORE_COMPONENT_DB => $adapter->getDbPrototype(),
            self::CORE_COMPONENT_LEGACY_DB => $adapter->getLegacyDatabasePrototype(),
            self::CORE_COMPONENT_FLAGS => array('class' => 'Ac_Flags'),
            self::CORE_COMPONENT_CACHE => $cachePrototype,
            self::CORE_COMPONENT_MAIL_SENDER => $adapter->getMailSenderPrototype(),
            self::CORE_COMPONENT_RELATION_PROVIDER_EVALUATOR => array('class' => 'Ac_Model_Relation_Provider_Evaluator'),
            self::CORE_COMPONENT_MANAGER_CONFIG_SERVICE => array('class' => 'Ac_Admin_ManagerConfigService'),
            self::CORE_COMPONENT_FRONT_CONTROLLER => array('class' => 'Ac_Application_FrontController'),
            self::CORE_COMPONENT_URL_MAPPER => array('class' => 'Ac_Application_FrontUrlMapper'),
        );
        
        Ac_Util::ms($res, $this->doGetMapperPrototypes());
        
        return $res;
    }
    
    function getComponent($id, $baseClass = null, $dontThrow = false) {
        if ($baseClass === true && func_num_args() == 2) {
            $dontThrow = true;
            $baseClass = null;
        }
        if (isset($this->componentAliases[$id])) {
            $res = $this->getComponent($this->componentAliases[$id], $dontThrow, $baseClass);
        } elseif (in_array($id, $this->listComponents())) {
            if (!is_object($this->components[$id])) {
                $this->components[$id] = Ac_Prototyped::factory($this->components[$id]);
                $this->registerComponents(array($id => $this->components[$id]));
            }
            $res = $this->components[$id];
        } else {
            $res = null;
            $this->triggerEvent(self::EVENT_ON_COMPONENT_NOT_FOUND, array($id, & $res));
            if ($res) {
                $this->addComponent($res, $id);
            } else {
                if (!$dontThrow) throw new Exception("No such component '{$id}' in ".get_class($this));
            }
        }
        if ($res && $baseClass && !($res instanceof $baseClass)) {
            throw new Ac_E_InvalidCall (__METHOD__.": expected component '{$id}' to be '{$baseClass}' instance, got '".get_class($res)."' instead");
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
        $this->addComponent($mailSender, self::CORE_COMPONENT_MAIL_SENDER);
    }

    /**
     * @return Ac_I_MailSender
     */
    function getMailSender() {
        $res = $this->getComponent(self::CORE_COMPONENT_MAIL_SENDER, 'Ac_I_MailSender');
        if ($res instanceof Ac_I_Mail_Sender_WithDump) {
            $dd = $res->getDumpDir();
            if (!strlen($dd)) $res->setDumpDir($this->adapter->getVarDumpsPath());
        }
        return $res;
    }

    function setMixables(array $mixables, $addToExisting = false) {
        // Since Application instance registers directory with classes during initialization phase,
        // classes of mixables that re provided as prototypes may not be available at that moment.
        // So we have to defer creation of mixables with classes that are not available at the moment
        
        if (!$this->deferredMixablesInit) {

            if (!$addToExisting && !$this->initsCoreMixables)
                $this->deferredMixables = array();

            foreach ($mixables as $k => $mix) {
                if (!is_object($mix)) {
                    $class = false;
                    if (isset($mix['class'])) $class = $mix['class'];
                    elseif (is_string($mix)) $class = $mix;
                    if ($class !== false) {
                        if (!class_exists($class)) {
                            $target = $this->initsCoreMixables? 'deferredCoreMixables' : 'deferredMixables';
                        }
                        if (is_numeric($k)) array_push($this->$target, $mix);
                            else $this->$target[$k] = $mix;
                        unset($mixables[$k]);
                    }
                }
            }
            
        }
        
        parent::setMixables($mixables, $addToExisting);
    }
    
    protected function registerCoreMixables() {
        $this->initsCoreMixables = true;
        parent::registerCoreMixables();
        $this->initsCoreMixables = false;
    }
    
    protected function initDeferredMixables() {
        if ($this->deferredMixablesInit) throw new Ac_E_Assertion("initDeferredMixables() must be called "
            . "with \$deferredMixablesInit === false");
        $this->deferredMixablesInit = true;
        if ($this->deferredCoreMixables) {
            $this->setMixables($this->deferredCoreMixables, false);
            $this->deferredCoreMixables = array();
            $ck = array_keys($this->mixables);
            $this->coreMixableIds = array_combine($ck, $ck);
        }
        if ($this->deferredMixables) {
            $this->setMixables($this->deferredMixables, false);
            $this->deferredMixables = array();
        }
    }
    
    function setControllerParam($controllerParam) {
        $this->controllerParam = $controllerParam;
    }

    function getControllerParam() {
        return $this->controllerParam;
    }

    protected $defaultController = false;
    
    protected $defaultAdminController = false;
    
    protected $controllerParam = false;
    
    function handleRequest(Ac_Cr_Context $context = null) {

        $c = $this->getFrontController();
        $c->handleRequest($context);
        
    }

    /**
     * @return Ac_Application_FrontController
     */
    function getFrontController() {
        return $this->getComponent(self::CORE_COMPONENT_FRONT_CONTROLLER, 'Ac_Application_FrontController');
    }

    /**
     * @return Ac_UrlMapper_UrlMapper
     */
    function getUrlMapper() {
        return $this->getComponent(self::CORE_COMPONENT_URL_MAPPER, 'Ac_UrlMapper_UrlMapper');
    }
    
    /**
     * @return Ac_Legacy_Controller
     */
    function getLegacyController($id, $dontThrow = false) {
        return $this->getComponent($id, 'Ac_Legacy_Controller', $dontThrow);
    }
    
    /**
     * @return Ac_Cr_Controller
     */
    function getController($id, $dontThrow = false) {
        return $this->getComponent($id, 'Ac_Cr_Controller', $dontThrow);
    }
    
    protected function handleRequestWithLegacyController() {
        
    }
    
    function handleAdminRequest(Ac_Cr_Context $context = null) {
        
    }
    
    function handleCronJob() {
        
        //  process all components and all mappers
        
    }
    
    /**
     * @return Ac_Flags
     */
    function getFlags() {
        return $this->getComponent(self::CORE_COMPONENT_FLAGS, 'Ac_Flags', true);
    }
    
}

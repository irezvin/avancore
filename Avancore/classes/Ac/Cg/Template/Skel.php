<?php

class Ac_Cg_Template_Skel extends Ac_Cg_Template {
    
    var $appName = 'notset';
    
    var $deIndent = 12;
    
    var $dbPrototype = array();
    
    var $dbName = false;
    
    var $tables = true;
    
    /**
     * Cg Log path relative to the config file
     */
    var $cgRelLogPath = '/../var/log/codegen';
    
    /**
     * Cg Output path relative to the config file
     */
    var $cgRelOutputPath = '/../var/code';
    
    /**
     * @var Ac_Cg_Layout
     */
    protected $layout = false;
    
    function extractConfig(Ac_Application $application) {
        $res = array();
        $adapter = $application->getAdapter();
        $dbPrototype = $adapter->getConfigValue('dbPrototype');
        if (!is_array($dbPrototype)) return array();
        $this->dbPrototype = $dbPrototype;
        if ($this->dbName) $this->dbPrototype['dbName'] = $this->dbName;
        /*$pdo = Ac_Util::getArrayByPath($dbPrototype, array('pdo'));
        $dsn = Ac_Util::getArrayByPath($dbPrototype, array('pdo', 'dsn'));
        if ($dsn) {
            if (!is_array($dsn)) $dsn = Ac_Sql_Db_Pdo::parseDsn($dsn);
        }
        
        if (isset($dsn['dbname'])) $res['DBNAME'] = $dsn['dbname'];
        if (isset($pdo['password'])) $res['PASSWORD'] = $pdo['password'];
        if (isset($pdo['username'])) $res['USERNAME'] = $pdo['username'];
        
        if (isset($dsn['password'])) $res['PASSWORD'] = $dsn['password'];
        if (isset($dsn['user'])) $res['USERNAME'] = $dsn['user'];
        
        if (strlen($d = $adapter->getConfigValue('dbName')))
            $res['DBNAME'] = $adapter->getConfigValue('dbName');
        */
        
        if ($this->dbName) $res['DBNAME'] = $this->dbName;
        
        return $res;
    }

    function setLayout(Ac_Cg_Layout $layout) {
        $this->layout = $layout;
    }

    /**
     * @return Ac_Cg_Layout
     */
    function getLayout() {
        return $this->layout;
    }    
    
    protected function getFileMap() {
        $res = array (
            '{pathBootstrap}/bootstrap.php' => 'bootstrapPhp',
            '{pathClasses}/{App}.php' => 'classesAppPhp',
            '{pathClasses}/{App}/Admin.php' => 'classesAppAdminPhp',
            '{pathClasses}/{App}/AdminTemplate.php' => 'classesAppAdmintemplatePhp',
            '{pathClasses}/{App}/Component.php' => 'classesAppComponentPhp',
            '{pathClasses}/{App}/Controller.php' => 'classesAppControllerPhp',
            '{pathClasses}/{App}/Frontend.php' => 'classesAppFrontendPhp',
            '{pathClasses}/{App}/FrontendTemplate.php' => 'classesAppFrontendtemplatePhp',
            '{pathConfig}/app.config.local.php' => 'configAppConfigLocalPhp',
            '{pathConfig}/app.config.php' => 'configAppConfigPhp',
            '{pathConfig}/codegen.config.php' => 'configCodegenConfigPhp',
            '{pathGen}/classes/{App}/DomainBase.php' => 'genClassesAppDomainbasePhp',
            '{pathAvancore}/config/app.config.php' => 'vendorAvancoreConfigAppConfigPhp',
            '{pathCodegenWeb}/index.php' => 'webCodegenIndexPhp',
            '{pathCodegenWeb}/.htaccess' => array('templatePart' => 'localHtaccess'),
            '{pathAssets}/style.css' => 'webAssetsStyleCss',
            '{pathVarTmp}' => array('content' => Ac_Cg_Generator::CONTENT_DIR),
            '{pathVarCode}' => array('content' => Ac_Cg_Generator::CONTENT_DIR),
            '{pathVarFlags}' => array('content' => Ac_Cg_Generator::CONTENT_DIR),
            '{pathVarLog}' => array('content' => Ac_Cg_Generator::CONTENT_DIR),
            '{pathVarCache}' => array('content' => Ac_Cg_Generator::CONTENT_DIR),
        );
        return $res;
    }
    
    function _generateFilesList() {
        $map = $this->getFileMap();
        $tr = $this->layout->getMapTr(true);
        $res = array();
        foreach ($map as $path => $part) {
            if (is_array($part)) {
                $item = $part;
            } elseif (!strlen($part)) {
                continue;
            } else {
                $item = array(
                    'templatePart' => $part,
                    'isEditable' => ! strncmp($path, 'gen/', 4),
                );
            }
            if (!isset($item['relPath'])) $item['relPath'] = $path;
            for ($i = 0; $i < 3; $i++) {
                $old = $item['relPath'];
                $item['relPath'] = $this->layout->expandPlaceholders($item['relPath'], $tr);
                if ($old === $item) break;
            }
            $res[$path] = $item;
        }
        return $res;
    }
    
    // --- bootstrap.php -----------------------------------------------------------

    function showBootstrapPhp () {

        $this->phpOpen();

    ?>         

            $dir = dirname(__FILE__);
            $config = array();
            require($dir.'/config/app.config.php');
            if (!class_exists('Ac_Util')) require($avancorePath.'/classes/Ac/Util.php');
            require($avancorePath.'/languages/english.php');
            Ac_Util::registerAutoload(true);
            Ac_Util::addIncludePath($dir.'/classes');
            Ac_Util::addIncludePath($dir.'/gen/classes');

            if (isset($config['useLangStrings']) && $config['useLangStrings']) { 
                Ac_Lang_ResourceProvider_Dir::autoRegister($dir.'/languages');
            }

    <?php
    }

    // --- classes/APP.php ---------------------------------------------------------

    function showClassesAppPhp () {

        $this->phpOpen();

    ?>        

            require_once(dirname(__FILE__).'/../gen/classes/'.'[[FILE_APP_BASE]]');

            class [[CLASS_APP]] extends [[CLASS_APP_BASE]] {

                function getAppClassFile() {
                    return dirname(__FILE__);
                }

                /**
                 * @return [[CLASS_APP]]
                 */
                static function getInstance($id = null) {
                    return Ac_Application::getApplicationInstance(__CLASS__, $id);
                }

                /**
                 * @return [[CLASS_COMPONENT]]
                 */
                protected function getComponent($class) {
                    return parent::getComponent($class);
                }

            }


    <?php
    }

    // --- classes/APP/Admin.php ---------------------------------------------------

    function showClassesAppAdminPhp () {

        $this->phpOpen();

    ?>        

            class [[CLASS_ADMIN]] extends Ac_Legacy_Controller_Std_Admin {

                var $_templateClass = '[[CLASS_ADMIN_TEMPLATE]]';

                /**
                 * @var [[CLASS_APP]]
                 */
                protected $application = false;

                function doListMapperClasses() {
                    $res = $this->application->listMappers();
                    return $res;
                }

                function executeDefault() {
                    //$this->_response->redirectUrl = $this->getUrl(array('action' => 'manager', 'mapper' => 'someMapper'), true);
                }

            }
    <?php
    }

    // --- classes/APP/AdminTemplate.php -------------------------------------------

    function showClassesAppAdmintemplatePhp () {

        $this->phpOpen();

    ?>
            

            class [[CLASS_ADMIN_TEMPLATE]] extends Ac_Legacy_Controller_Std_Admin_Template {
                
                /**
                 * @var [[CLASS_ADMIN]]
                 */
                var $controller = false;
                
                
                function getLinks() {
                    $res = array();
                    foreach ($this->controller->doListMapperClasses() as $i) {
                        $map = $this->controller->getApplication()->getMapper($i);
                        $info = $map->getInfo();
                        $cap = strlen($info->pluralCaption)? $info->pluralCaption : $i;
                        $res[$cap] = array('mapper' => $i);
                    }
                    return $res;
                }
                
                function _showWrapper($content) {
                    $this->addAssetLibs('[[APP_PLACEHOLDER]]/style.css');
                    
                    $links = $this->getLinks();
                    
                    $aa = array();
                    foreach ($links as $cap => $link) {
                        if (!is_string($link)) {
                            $u = $this->controller->getUrl($q = Ac_Util::m(array('action' => 'manager'), $link), false);
                            if ($this->context->getManyValues(array_keys($q)) == $q) $cap = '<strong>'.$cap.'</strong>';
                        } else {
                            $u = $link;
                        }
                        $aa[] = Ac_Util::mkElement('a', $cap, array('href' => (string) $u));
                    }
                    echo Ac_Util::mkElement('div', 
                        "<div class='items'>".implode(" &#149; ", $aa)."</div>", 
                        array(
                            'class' => 'adminMenu', 
                        ));
                    parent::_showWrapper($content);        
                }
            }
            
<?php
    }

    // --- classes/APP/Component.php -----------------------------------------------

    function showClassesAppComponentPhp () {

        $this->phpOpen();

    ?>        

            class [[CLASS_COMPONENT]] extends Ac_Application_Component {

                /**
                 * @var [[CLASS_APP]]
                 */
                protected $application = false;

                function setApplication([[CLASS_APP]] $application) {
                    parent::setApplication($application);
                }

                /**
                 * @return [[CLASS_APP]]
                 */
                function getApplication() {
                    return parent::getApplication();
                }

            }
    <?php
    }

    // --- classes/APP/Controller.php ----------------------------------------------

    function showClassesAppControllerPhp () {

        $this->phpOpen();

    ?>        

            class [[CLASS_CONTROLLER]] extends Ac_Legacy_Controller {

                /**
                 * @var [[CLASS_APP]]
                 */
                protected $application = false;

            }
    <?php
    }

    // --- classes/APP/Frontend.php ------------------------------------------------

    function showClassesAppFrontendPhp () {

        $this->phpOpen();

    ?>        

            class [[CLASS_FRONTEND]] extends [[CLASS_CONTROLLER]] {

                /**
                 * @var [[CLASS_APP]]
                 */
                protected $application = false;

                var $_templateClass = '[[CLASS_FRONTEND_TEMPLATE]]';

            }
    <?php
    }

    // --- classes/APP/FrontendTemplate.php ----------------------------------------

    function showClassesAppFrontendtemplatePhp () {

        $this->phpOpen();

    ?>        

            class [[CLASS_FRONTEND_TEMPLATE]] extends Ac_Legacy_Template_Html {

                /**
                 * @var [[CLASS_FRONTEND]]
                 */
                var $controller = false;

            }
    <?php
    }

    // --- config/app.config.local.php ---------------------------------------------

    function showConfigAppConfigLocalPhp () {

        $this->phpOpen();

    ?>        

            $avancorePath = '[[AVANCORE_PATH]]';
            if (!is_dir($avancorePath)) $avancorePath = dirname(__FILE__).'/../vendor/Avancore';

            $config['dbName'] = '[[DBNAME]]';
            
            $config['dbPrototype'] = <?php $this->exportArray($this->dbPrototype, $this->deIndent, true) ?>;
            
    <?php
    }

    // --- config/app.config.php ---------------------------------------------------

    function showConfigAppConfigPhp () {

        $this->phpOpen();

    ?>        

            if (isset($_SERVER) && isset($_SERVER['SERVER_NAME']) && isset($_SERVER['REQUEST_URI'])) {
                $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'? 'https://' : 'http://';
                $url = $scheme.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
                if (substr($url, -1) !== '/') {
                    $url = dirname($url);
                }
            } else {
                $url = 'http://127.0.0.1/[[App]]/';
            }

            $config = array(

                'appName' => '[[App]]',

                'assetPlaceholders' => array(
                    '[[APP_PLACEHOLDER]]' => $url.'/assets',
                    '{AC}' => $url.'/avancore',
                ),
                'useLangStrings' => false,
                'managerImagesUrl' => '{AC}/vendor/images/',

                // will be set later in app.config.local.php
                'dbName' => '**dbname**',
                'dbPrototype' => array(
                    'class' => 'Ac_Sql_Db_Pdo',
                    'pdo' => array(
                        'dsn' => '**not set**',
                    ),
                ),
            );

            require(dirname(__FILE__).'/app.config.local.php');

    <?php
    }

    function getCodegenConfig() {
        $res = array(
            'generator' => array(
                'dbPrototype' => new Ac_Cg_Php_Expression('$appConfig[\'dbPrototype\']'),
                'outputDir' => new Ac_Cg_Php_Expression('dirname(__FILE__).'.$this->export($this->cgRelOutputPath, true)),
                'logFileName' => new Ac_Cg_Php_Expression('dirname(__FILE__).'.$this->export($this->cgRelLogPath, true)),
                'overwriteLog' => true,

                'domainDefaults' => new Ac_Cg_Php_Expression(array(
                        'defaultTitlePropName' => 'title',
                        'defaultPublishedPropName' => 'published',
                        'defaultOrderingPropName' => 'ordering',
                ), 'domainDefaults', 'default settings for all domains', true),
            ),

            'domains' => array(
                'defaultDomain' => new Ac_Cg_Php_Expression(array(
                    'dbName' => new Ac_Cg_Php_Expression('$appConfig[\'dbName\']'),
                        'tablePrefix' => new Ac_Cg_Php_Expression('$appConfig[\'dbPrototype\'][\'dbPrefix\']'),
                        'replaceTablePrefixWith' => '#__',
                        'useLangStrings' => new Ac_Cg_Php_Expression(
                            'isset($config[\'useLangStrings\']) && $config[\'useLangStrings\']', 
                            false, 'will generate language strings'
                        ),

                    'subsystemPrefixes' => new Ac_Cg_Php_Expression(array(), 
                        false, "parts of model identifiers that will be used as subsystem names (model that reference\n"
                        ."each other within one subsystem don't have common prefix in the property identifiers)", true),

                    'subsystemPrefixes' => new Ac_Cg_Php_Expression(array(), 
                        false, "which subsystems don't reference each other", true),
                    
                    'subsystemPrefixes' => new Ac_Cg_Php_Expression(is_string($this->tables) || is_array($this->tables), 
                        false, "generate models from all tables", true),
                    
                    'autoTablesAll' => new Ac_Cg_Php_Expression(!(is_string($this->tables) || is_array($this->tables)), 
                        false, "generate models from all tables", true),
                    
                    'autoTablesIgnore' => new Ac_Cg_Php_Expression(array(), 
                        false, "array or regExp", true),
                    
                    'autoTables' => new Ac_Cg_Php_Expression(
                        is_string($this->tables) || is_array($this->tables)? $this->tables : array(), 
                        false, "array or regExp -- used only if autoTablesAll === FALSE", true),
                    
                    'defaultTitleColumn' => new Ac_Cg_Php_Expression('title', 
                        false, "one or more columns that will be used as titles of records", true),
                    
                    'defaultTitleColumn' => new Ac_Cg_Php_Expression('title', 
                        false, "one or more columns that will be used as titles of records", true),
                    
                    'dictionary' => new Ac_Cg_Php_Expression(array(
                        'data' => new Ac_Cg_Php_Expression(array(), 
                            false, "'index' => array('singular' => 'index', 'plural' => 'indices'),", true)
                        ), false, "exceptions for the inflector", true
                    ), 
                    
                    'schemaExtras' => new Ac_Cg_Php_Expression(array(
                        'tables' => array(), 
                    ), false, "overrides for the SQL schema that will be auto-generated from the database", true),
                    
                    'modelDefaults' => new Ac_Cg_Php_Expression(array(
                        'generateMethodPlaceholders' => false,
                        'hasUniformPropertiesInfo' => true,
                    ), false, "default settings for all models", true),
                ), new Ac_Cg_Php_Expression('$appConfig["appName"]'), false, true)
            )
        );
        return $res;
    }
    
    // --- config/codegen.config.php -----------------------------------------------

    function showConfigCodegenConfigPhp () {

        $this->phpOpen();

    ?>        

            require(dirname(__FILE__).'/app.config.php');
            $appConfig = $config;

            $config = <?php $this->export($this->getCodegenConfig(), false, 12); ?>;
            
            
    <?php
    }

    // --- gen/classes/APP/DomainBase.php ------------------------------------------

    function showGenClassesAppDomainbasePhp () {

        $this->phpOpen();

    ?>        

            abstract class [[CLASS_APP_BASE]] extends Ac_Application {
            }
    <?php
    }

    // --- vendor/Avancore/config/app.config.php -----------------------------------

    function showVendorAvancoreConfigAppConfigPhp () {

        $this->phpOpen();

    ?>        

            require(dirname(__FILE__).'/../../../config/app.config.php');

            $config['varPath'] = dirname(__FILE__).'/../../../var';
    <?php
    }

    // --- web/admin.php -----------------------------------------------------------

    function showWebAdminPhp () {

        $this->phpOpen();

    ?>        

            // comment-out next line after proper authorization is implemented
            die('Access denied');

            require(dirname(__FILE__).'/../bootstrap.php');

            $u = Ac_Url::guess();
            $u->query = array();
            $ctx = new Ac_Legacy_Controller_Context_Http();
            $ctx->populate(array('get', 'post'));
            $ctx->setBaseUrl($u);

            $c = new [[CLASS_ADMIN]]();
            $c->setApplication([[CLASS_APP]]::getInstance());
            $resp = $c->getResponse();

            $o = new Ac_Legacy_Output_Native;
            $o->htmlTemplateSettings['doctypeTag'] = Ac_Legacy_Template_HtmlPage::doctypeHtml5;
            $o->htmlTemplateSettings['addXmlTag'] = false;
            $o->showOuterHtml = true;
            $o->outputResponse($resp);


    <?php
    }

    // --- web/assets/style.css ----------------------------------------------------

    function showWebAssetsStyleCss () {
    ?>        
    <?php
    }

    // --- web/codegen/index.php ---------------------------------------------------

    function showWebCodegenIndexPhp () {

        $this->phpOpen();

    ?>        

            require(dirname(__FILE__).'/../../bootstrap.php');
            require_once('Ac/Cg/Frontend.php');
            $f = new Ac_Cg_Frontend();
            $f->configPath = dirname(__FILE__).'/../../config/codegen.config.php';
            $f->processWebRequest();

    <?php
    }

    // --- web/index.php -----------------------------------------------------------

    function showWebIndexPhp () {

        $this->phpOpen();

    ?>        

            require(dirname(__FILE__).'/../bootstrap.php');

            $u = Ac_Url::guess();
            $u->query = array();
            $ctx = new Ac_Legacy_Controller_Context_Http();
            $ctx->populate(array('get', 'post'));
            $ctx->setBaseUrl($u);

            $c = new [[CLASS_FRONTEND]]();
            $c->setApplication([[CLASS_APP]]::getInstance());
            $resp = $c->getResponse();

            $o = new Ac_Legacy_Output_Native;
            $o->htmlTemplateSettings['doctypeTag'] = Ac_Legacy_Template_HtmlPage::doctypeHtml5;
            $o->htmlTemplateSettings['addXmlTag'] = false;
            $o->showOuterHtml = true;
            $o->outputResponse($resp);


    <?php
    }
    
    protected $translations = false;
    
    var $placeholders = array();
    
    function doInit() {
        $this->appName = ucfirst(strtolower($this->appName));
        parent::doInit();
        $ph = array(
            'app' => strtolower($this->appName),
            'App' => ucfirst($this->appName),
            'APP' => strtoupper($this->appName),
            'APP_PLACEHOLDER' => '{'.strtoupper($this->appName).'}',
            'CLASS_ADMIN' => "{$this->appName}_Admin",
            'CLASS_ADMIN_TEMPLATE' => "{$this->appName}_AdminTemplate",
            'CLASS_APP' => "{$this->appName}",
            'CLASS_APP_BASE' => "{$this->appName}_DomainBase",
            'FILE_APP_BASE' => $this->appName.'/DomainBase.php',
            'CLASS_COMPONENT' => "{$this->appName}_Component",
            'CLASS_CONTROLLER' => "{$this->appName}_Controller",
            'CLASS_FRONTEND' => "{$this->appName}_Frontend",
            'CLASS_FRONTEND_TEMPLATE' => "{$this->appName}_FrontendTemplate",
            'AVANCORE_PATH' => Ac_Avancore::getInstance()->getAppRootDir(),
        );
            
        if (Ac_Application::listInstances('Ac_Avancore')) {
            Ac_Util::ms($ph, $this->extractConfig(Ac_Avancore::getInstance()));
            
        }
        
        $this->placeholders = array_merge($ph, $this->placeholders);
    }
    
    function getTranslations() {
        if ($this->translations === false) {
            $this->translations = array();
            foreach ($this->placeholders as $k => $v) {
                $this->translations["'[[$k]]'"] = $this->export($v, true);
                $this->translations["[[$k]]"] = $v;
            }
        }
        return $this->translations;
    }
    
    function fetchFileContent($id) {
        $res = parent::fetchFileContent($id);
        if ($res === Ac_Cg_Generator::CONTENT_DIR) return $res;
        $res = strtr($res, $this->getTranslations());
        $rep = str_repeat(" ", $this->deIndent);
        $test = $res;
        $test = $rep.trim(str_replace("<"."?php", "", $test));
        $test = preg_replace("/([\n\r]+)\s*[\n\r]+/", "\\1", $test);
        preg_match_all("/^{$rep}/m", $test, $matches1);
        preg_match_all("/^/m", $test, $matches2);
        if ($this->deIndent && count($matches1[0]) && count($matches1[0]) == count($matches2[0])) {
            $res = preg_replace("/^{$rep}/m", "", $res);
        }
        $res = preg_replace("/ +$/m", "\\1", $res); // remove trailing spaces
        return $res;
    }
    
    /**
     * Whether db name is required for initial code generation
     */
    function getDbRequired() {
        return true;
    }
    
    /**
     * Whether tables param is required for initial code generation
     */
    function getTablesRequired() {
        return false;
    }
    
    
}
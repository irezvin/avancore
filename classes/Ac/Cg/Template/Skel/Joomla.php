<?php

class Ac_Cg_Template_Skel_Joomla extends Ac_Cg_Template_Skel {
    
    /**
     * @var Ac_Cg_Layout_Joomla
     */
    protected $layout = false;
    
    var $tables = null;
    
    /**
     * Cg Log path relative to the config file
     */
    var $cgRelLogPath = '/../../../log/avancore/codegen';
    
    /**
     * Cg Output path relative to the config file
     */
    var $cgRelOutputPath = '/../../../tmp/avancore/code';

    function setLayout(Ac_Cg_Layout $layout) {
        if (!$layout instanceof Ac_Cg_Layout_Joomla)
            throw Ac_E_InvalidCall::wrongClass ('layout', $layout, 'Ac_Cg_Layout_Joomla');
        parent::setLayout($layout);
    }

    /**
     * @return Ac_Cg_Layout_Joomla
     */
    function getLayout() {
        return parent::getLayout();
    }    
    
    protected function getFileMap() {
        $res = parent::getFileMap();
        Ac_Util::ms($res, array(
            'components/com_{app}/{app}.php' => 'frontendAppPhp',
            'administrator/components/com_{app}/{app}.php' => 'adminAppPhp',
            'administrator/components/com_{app}/{app}.xml' => 'adminAppXml',
            '{pathVarTmp}/.htaccess' => array('templatePart' => 'localHtaccess'),
            '{pathVarLog}/.htaccess' => array('templatePart' => 'localHtaccess'),
        ));
        return $res;
    }
    
    function getCodegenConfig() {
        if (is_null($this->tables))
            $this->tables = '/#__'.strtolower($this->appName).'_/';
        $res = parent::getCodegenConfig();
        $res['domains']['defaultDomain']->expression = array_merge(
            array(
                'dontPrefixClassesWithAppName' => true,
                'appClassName' => ucfirst($this->appName),
        ), $res['domains']['defaultDomain']->expression);
        return $res;
    }

    
    // --- config/app.config.local.pre.php ---------------------------------------------

    function showConfigAppConfigLocalPrePhp () {

        $this->phpOpen();

    ?>        

            $avancorePath = '[[AVANCORE_PATH]]';
            if (!is_dir($avancorePath)) $avancorePath = JPATH_ROOT.'/libraries/avancore';
            
    <?php
    }
    
    // --- config/app.config.local.post.php ---------------------------------------------

    function showConfigAppConfigLocalPostPhp () {

        $this->phpOpen();

    ?>        
    <?php
    }

    // --- config/app.config.php ---------------------------------------------------

    function showConfigAppConfigPhp () {

        $this->phpOpen();

    ?>        
            if (is_file(dirname(__FILE__).'/app.config.local.pre.php')) {
                require(dirname(__FILE__).'/app.config.local.pre.php');
            }

            if (class_exists('JUri', false)) {
                // use Joomla class to get current URL
                $url = JUri::getInstance()->base(array('scheme', 'host', 'path'));
                $url = preg_replace('#(/administrator)?(/index\.php)?$#', '', $url);
            } else { 
                // guess URL
                if (isset($_SERVER) && isset($_SERVER['SERVER_NAME']) && isset($_SERVER['REQUEST_URI'])) {
                    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'? 'https://' : 'http://';
                    $url = $scheme.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
                    if (substr($url, -1) !== '/') {
                        $url = dirname($url);
                    }
                } else {
                    $url = 'http://127.0.0.1/[[App]]/';
                }
            }


            $config = array(

                'appName' => '[[App]]',

                'assetPlaceholders' => array(
                    '{<?php echo strtoupper($this->appName); ?>}' => $url.'/media/com_[[app]]',
                    '{AC}' => $url.'/media/avancore',
                    '{JQUERY}' => $url.'/media/jui/js/jquery.js'
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
                
                'varFlagsPath' => dirname(__FILE__).'/../../../<?php echo $this->layout->getPathVar('pathVarFlags'); ?>',
                'varTmpPath' => dirname(__FILE__).'/../../../<?php echo $this->layout->getPathVar('pathVarTmp'); ?>',
                'varLogPath' => dirname(__FILE__).'/../../../<?php echo $this->layout->getPathVar('pathVarLog'); ?>',
                'varCachePath' => dirname(__FILE__).'/../../../<?php echo $this->layout->getPathVar('pathVarCache'); ?>',

            );

            if (!class_exists('JConfig', false)) {
                $jconfig = dirname(__FILE__).'/../../../configuration.php';
                if (is_file($jconfig)) require($jconfig);
            }
            if (class_exists('JConfig', false)) {
                $jc = new JConfig;
                if ($jc->dbtype === 'mysqli') {
                    // Let's make a valid DSN
                    $config['dbName'] = $jc->db;
                    $hostport = explode(':', $jc->host);
                    $isSocket = false;
                    $dsn = array(
                        'schema' => 'mysql',
                        'dbname' => $jc->db,
                        'password' => $jc->password,
                        'host' => $hostport[0],
                    );
                    if (isset($hostport[1])) {
                        if (!is_numeric($hostport[1])) {
                            $dsn['socket'] = $hostport[1];
                        } else {
                            $dsn['port'] = $hostport[1];
                        }
                    }
                    $config['dbPrototype'] = array(
                        'class' => 'Ac_Sql_Db_Pdo',
                        'dbName' => $jc->db,
                        'dbPrefix' => $jc->dbprefix,
                        'pdo' => array(
                            'dsn' => $dsn,
                            'username' => $jc->user,
                            'password' => $jc->password,
                        ),
                    );
                    $config['dbDetected'] = true;
                }
            }

            if (is_file(dirname(__FILE__).'/app.config.local.post.php')) {
                require(dirname(__FILE__).'/app.config.local.post.php');
            }

    <?php
    }
    
    // --- bootstrap.php -----------------------------------------------------------

    function showBootstrapPhp () {

        $this->phpOpen();

    ?>      
            
            $dir = dirname(__FILE__);
            $config = array();
            require($dir.'/config/app.config.php');
            $shouldLoadAvancore = !class_exists('Ac_Util');
            if ($shouldLoadAvancore) {
                if (isset($config['avancorePath'])) {
                    $avancorePath = $config['avancorePath'];
                } elseif (defined('JPATH_ROOT')) {
                    $avancorePath = JPATH_ROOT.'/libraries/avancore';
                } else {
                    $avancorePath = dirname(__FILE__).'/../../libraries/avancore';
                }
                require($avancorePath.'/classes/Ac/Util.php');
                Ac_Util::registerAutoload(true);
            }
            require($avancorePath.'/languages/english.php');
            Ac_Util::addIncludePath($dir.'/classes');
            Ac_Util::addIncludePath($dir.'/gen/classes');
            if (isset($config['useLangStrings']) && $config['useLangStrings']) {
                Ac_Lang_ResourceProvider_Dir::autoRegister($dir.'/languages');
            }
            
    <?php
    }
    
    // --- [[App.php]] -------------------------------------------------------------
    
    function showFrontendAppPhp () {
        
        $this->phpOpen();
        
    ?>
            
            require_once(dirname(__FILE__).'/bootstrap.php');
            
            $u = Ac_Url::guess();
            $u->query = array();
            $u->query['option'] = JRequest::getVar('option');
            $u->query['view'] = JRequest::getVar('view');
            
            $ctx = new Ac_Controller_Context_Http(array('baseUrl' => $u));
            $ctx->setData(JRequest::get());
            $itemId = JRequest::getVar('itemId');
            if ($itemId) $u->query['itemId'] = $itemId;
            
            if (JFactory::getApplication()->isAdmin()) {
                $controller = new [[App]]_Admin($ctx);
            } else {
                $controller = new [[App]]_Frontend($ctx);
            }
            
            $controller->setApplication([[App]]::getInstance());
            
            $response = $controller->getResponse();
            $output = new Ac_Controller_Output_Joomla3();
            $output->outputResponse($response);
            
            
    <?php
    
    }
    
    // --- [[administrator/components/com_app/app.php]] ----------------------------
    
    function showAdminAppPhp () {
        
        $this->phpOpen();
        
    ?>
            
            $comName = basename(dirname(__FILE__));
            $subName = basename(__FILE__, '.php');
            require(JPATH_ROOT.'/components/'.$comName.'/'.$subName.'.php');

    <?php

    }
    
    // --- [[administrator/components/com_app/app.xml]] ----------------------------
    
    function showAdminAppXml () {
        
        echo '<'.'?xml version="1.0" encoding="utf-8"?'.'>'."\n";
    ?>
            <extension type="component" version="3.0" method="upgrade">
                <name>com_[[app]]</name>
                <!--author>AUTHOR_NAME</author>
                <creationDate>AUTHOR_YEAR</creationDate>
                <copyright>(C) AUTHOR_YEAR AUTHOR_NAME All rights reserved.
                </copyright>
                <license>GNU GPL</license>
                <authorEmail>AUTHOR_EMAIL</authorEmail>
                <authorUrl>AUTHOR_URL</authorUrl>
                <version>AUTHOR_VERSION</version-->
                <install> <!-- Runs on install -->
                </install>
                <uninstall> <!-- Runs on uninstall -->
                </uninstall>
                <files folder="site">
                </files>
                <administration>
                    <menu link="option=com_[[app]]&amp;action=start">com_[[app]]</menu>
                </administration>
            </extension>            
    <?php
        
    }
    
    /**
     * Whether db name is required for initial code generation
     */
    function getDbRequired() {
        return false;
    }
    
    /**
     * Whether tables param is required for initial code generation
     */
    function getTablesRequired() {
        return false;
    }
    
}
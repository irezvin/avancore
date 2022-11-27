<?php

class Ac_Admin_Controller extends Ac_Controller implements Ac_I_WithEvents {

    use Ac_Event_TraitWithEvents;
    
    const EVENT_GET_LOGIN_FORM_PROTOTYPE = 'onGetLoginFormPrototype';
    
    const EVENT_CREATE_LOGIN_FORM = 'onCreateLoginForm';
    
    var $appCaption = null;
    
    var $appCaptionSuffix = ' Admin';
    
    var $_templateClass = 'Ac_Admin_ControllerTemplate';
    
    var $publicMethods = ['executeLogin', 'executeResetPassword'];
    
    var $extraAssets = [];
    
    var $stateVarName = true;

    var $resetStateOnNoArgs = false;
    
    function __construct(array $options = array()) {
        $this->_autoTplVars = array_merge($this->_autoTplVars, [
            'appCaption',
            'appCaptionSuffix',
        ]);
        parent::__construct($options);
    }
    
    function getAppCaption() {
        if ($this->appCaption) return $this->appCaption;
        return preg_replace('/_.*$/', '', get_class($this->application));
    }
    
    function getLoginFormPrototype() {
        $res = [
            'formTagAttribs' => ['name' => 'adminLogin'],
            'controls' => [
                'username' => ['class' => 'Ac_Form_Control_Text', 'required' => true],
                'password' => ['class' => 'Ac_Form_Control_Text', 'type' => 'password', 'required' => true],
                'submit' => ['class' => 'Ac_Form_Control_Button', 'buttonType' => 'submit', 'caption' => 'Log In'],
                'redir' => ['class' => 'Ac_Form_Control_Text', 'type' => 'hidden', 'showWrapper' => false],
            ],
            'submissionControl' => 'submit'
        ];
        $this->triggerEvent(self::EVENT_GET_LOGIN_FORM_PROTOTYPE, ['res' => & $res]);
        return $res;
    }
    
    function doBeforeExecute() {
        $methodName = $this->getMethodName();
        if (!in_array($methodName, $this->publicMethods)) {
            $user = $this->getApplication()->getUser();
            if (!$user) {
                $this->_response->redirectUrl = $this->getUrl([
                    'action' => 'login',
                    'redir' => $_SERVER['REQUEST_URI'],
                ]);
                return false;
            }
        }
    }
    
    protected function createLoginForm() {
        $form = new Ac_Form($this->getLoginFormPrototype());
        $this->triggerEvent(self::EVENT_CREATE_LOGIN_FORM, ['form' => & $form]);
        return $form;
    }
    
    function execute() {
        $this->_response->redirectUrl = $this->getDefaultLocation();
    }
    
    function getDefaultLocation() {
        return $this->getUrl(['action' => 'dashboard']);
    }
    
    protected function redirAfterLogin() {
        $redir = $this->_context->getData('redir');
        if (!strlen($redir)) {
            $menu = array_values($this->getMenu());
            if ($menu) {
                $redir = $this->getUrl($menu[0]->query);
            }
        }
        if (!strlen($redir)) $redir = $this->getDefaultLocation();
        $this->_response->redirectUrl = $redir;
    }
    
    function executeLogin() {
        if ($this->getApplication()->getUser()) {
            $this->_templatePart = 'loggedIn';
            $this->redirAfterLogin();
            return;
        }
        $loginForm = $this->createLoginForm();
        $this->_tplData['loginForm'] = $loginForm;
        $this->_templatePart = true;
        if (!$loginForm->isSubmitted()) return;
        $value = $loginForm->getValue();
        $data = ['username' => $value['username'], 'password' => $value['password']];
        $errors = [];
        $errors = ['password' => 'Username / password do not match'];
        if ($this->getApplication()->login($data, $errors)) {
            $this->redirAfterLogin();
        } else {
            $loginForm->errors = $errors;
        }
    }
    
    function executeDashboard() {
        $this->_templatePart = true;
    }
    
    function executeLogout() {
        $this->getApplication()->logout();
        $redir = $this->_context->getData('redir');
        if (!strlen($redir)) $redir = $this->getUrl(['action' => 'login']);
        $this->_response->redirectUrl = $redir;
    }

    function executeSettings() {
        $this->_templatePart = true;
    }    
	
	function getStateVarName() {
	    if (!strlen($this->stateVarName)) $res = false;
	    elseif ($this->stateVarName === true) $res = $this->_instanceId;
	    else $res = $this->stateVarName;
	    return $res;
	}
    
    // @TODO: move persistent state handling to the adapter
    
    protected function loadState(array $path) {
        $sv = $this->getStateVarName();
        if (!$sv || !isset($_SESSION[$sv]) || !is_array($_SESSION[$sv])) return;
        return Ac_Util::getArrayByPath($_SESSION[$sv], $path);
    }
    
    protected function saveState(array $state, array $path) {
        $sv = $this->getStateVarName();
        if (!$sv) return;
        if (!isset($_SESSION[$sv])) $_SESSION[$sv] = array();
        Ac_Util::setArrayByPath($_SESSION[$sv], $path, $state);
    }
	
	function applyState(Ac_Controller_Context_Http $c, $mapperId = null) {
        $dataPath = $c->getDataPath(true);
        
        $data = $c->getData();
        
        if (!$mapperId) {
            if (isset($data['mapper']) && $data['mapper']) $mapperId = $data['mapper'];
        }
        
        if (!$mapperId) return;
        
        $dataPath = array_merge(['mapper', $mapperId], $dataPath);
        $state = $this->loadState($dataPath);
        
        if (isset($state['__pathInfo__'])) {
            unset($state['__pathInfo__']);
        }
        
        // Workaround for case when sometimes Cancel button saves ID of record into the state and 
        // all subsequent actions are related only to that record
        Ac_Util::unsetArrayByPath($state, array('keys'));

        if (isset($data['managerAction']) && $data['managerAction'] !== 'list') {
            return;
        }
        
        if (!is_array($state)) $state = array();
        if ($this->resetStateOnNoArgs && !count($data)) $state = array();
        if ($c->getData('filterForm')) $state['filterForm'] = array();
        $state['returnUrl'] = null;
        $state['returnUrl64'] = null;

        $state = Ac_Util::ms($state, $c->getData(), true);

        $c->setData($state);
        $this->saveState($state, $dataPath);
	}    

    /**
     * @return Ac_Admin_Manager
     */
    function createManager($mapper, Ac_Controller_Context $context = null) {
        
        if (!(is_object($mapper))) $mapper = $this->app->getMapper($mapper);

        $mapperId = $mapper->getId();
        
        $managerConfig = array('mapperClass' => $mapperId);
        
        if (is_null($context)) {
            $bu = $this->getUrl(array($this->_methodParamName => 'manager', 'mapper' => $mapperId));
            unset($bu->query['managerAction']);
            $contextOptions = array(
                'baseUrl' => $bu,
                'isInForm' => 'aForm',
            );
            $pathInfo = $this->param('__pathInfo__');
            $context = new Ac_Controller_Context_Http($contextOptions);
            $context->populate('request', $this->_context->getDataPath());
            if ($pathInfo) $context->updateData(['__pathInfo__' => $pathInfo]);
            $this->applyState($context, $mapperId);
        }
        
        if (strlen($mapper->managerClass)) $class = $mapper->managerClass;
            else $class = 'Ac_Admin_Manager';
        if ($extra = $mapper->getManagerConfig()) {
            Ac_Util::ms($managerConfig, $extra);
        }
        if ($this->useUrlMapper) {
            $managerConfig['useUrlMapper'] = true;
        }
        $manager = new $class ($managerConfig);
        $manager->_methodParamName = 'managerAction';
        $manager->setApplication($this->getApplication());
        $manager->setContext($context);
        
        return $manager;
    }
    
    function executeManager($mapper) {
                
        $mapperId = $mapper;
        $mapper = null;
        
        $mapper = Ac_Model_Mapper::getMapper($mapperId);

        if (!$mapper) throw new Ac_Controller_Exception("No such mapper: ".$mapperId, 404);

        $manager = $this->createManager($mapper);
        
        $response = $manager->getResponse();
        if ($response->noWrap) {
            $this->_response = $response;
        } else {
//            if ($this->separateToolbar) {
//                if (strlen($manager->toolbarContent)) {
//                    Ac_Controller_Output_Joomla15::addHtmlToJoomlaToolbar($manager->toolbarContent);
//                }
//            }
            $this->_tplData['manager'] = $manager;
            $this->_tplData['managerResponse'] = $response;
            $this->_templatePart = true;
        }
    }
    
    function listMappers() {
        $aliases = $this->application->getComponentAliases();
        $list = $this->application->listMappers();
        $res = [];
        foreach ($list as $item) {
            if (($k = array_search($item, $aliases)) !== false) $res[] = $k;
            else $res[] = $item;
        }
        return $res;
    }
    
    protected $menuGroups = null;
    
    protected function doGetMenuGroupPrototypes() {
        $groups = $this->getApplication()->getAdapter()->getConfigValue('adminMenuGroups');
        if (is_array($groups)) return $groups;
        return [];
    }
    
    protected function getMenuGroups() {
        if (!is_null($this->menuGroups)) return $this->menuGroups;
        $this->menuGroups = Ac_Prototyped::factoryCollection(
            $this->doGetMenuGroupPrototypes(),
            'Ac_Admin_MenuGroup',
            [],
            'id',
            true
        );
        return $this->menuGroups;        
    }
    
    function getMenu() {
        $res = [];        
        $groups = $this->getMenuGroups();
        foreach ($this->listMappers() as $id) {
            $info = $this->application->getMapper($id)->getInfo();
            $caption = $info->pluralCaption;
            if (!$caption) $caption = ucfirst($id);
            $entry = new Ac_Admin_MenuEntry([
                'id' => 'manager-'.$id,
                'caption' => $caption,
                'query' => [$this->_methodParamName => 'manager', 'mapper' => $id]
            ]);
            $groupId = $info->adminGroupId;
            if ($groupId) {
                $entry->groupId = $groupId;
                if (!isset($groups[$groupId])) {
                    $groups[$groupId] = new Ac_Admin_MenuGroup(['id' => $groupId]);
                }
            }
            if ($this->_methodName === 'executeManager' && $this->param('mapper') === $id) {
                $entry->active = true;
            }            
            $res[$entry->id] = $entry;            
        }        
        
        $groupEntries = [];        
        foreach ($groups as $group) {
            $entry = new Ac_Admin_MenuEntry([
                'id' => 'group-'.$group->id,
                'caption' => $group->getTitle(),
            ]);
            $groupEntries[$entry->id] = $entry;
            $childEntries = [];
            foreach ($res as $k => $childEntry) {
                if (!$group->hasMenuEntry($childEntry)) continue;
                $entry->parentId = $entry->id;
                $childEntries[] = $childEntry;
                unset($res[$k]);
            }
            $entry->setMenuEntries($childEntries);
        }
        
        Ac_Util::ms($res, $groupEntries);
        Ac_Admin_MenuEntry::sortMenuEntries($res);
        
        return $res;
    }
    
    function getUrlMapperPrototype() {
        return [
            'class' => 'Ac_UrlMapper_StaticSignatures',
            'controllerClass' => get_class($this),
            'patterns' => [
                ['definition' => '/m/{mapper}/{?c}{...}', 'const' => ['action' => 'manager']]
            ],
            'ignoreMethods' => [
                'executeManager'
            ]
        ];
    }

//    function executeApi($mapper) {
//    }

}

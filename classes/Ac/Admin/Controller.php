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
    
    function __construct($context = null, $options = array(), $instanceId = false) {
        if (func_num_args() == 1){
            parent::__construct($context);
        } else {
            parent::__construct($context, $options, $instanceId);
        }
        $this->_autoTplVars = array_merge($this->_autoTplVars, [
            'appCaption',
            'appCaptionSuffix',
        ]);
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
                'submit' => ['class' => 'Ac_Form_Control_Button', 'type' => 'submit', 'caption' => 'Log In'],
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
                $this->_response->redirectUrl = $this->getUrl(['action' => 'login']);
                return false;
            }
        }
    }
    
    protected function createLoginForm() {
        $form = new Ac_Form($this->getLoginFormPrototype());
        $this->triggerEvent(self::EVENT_CREATE_LOGIN_FORM, ['form' => & $form]);
        return $form;
    }
    
    function executeLogin() {
        if ($this->getApplication()->getUser()) {
            $this->_templatePart = 'loggedIn';
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
            $redir = $this->_context->getData('redir');
            if (!strlen($redir)) {
                $menu = array_values($this->getMenu());
                if ($menu) {
                    $redir = $this->getUrl($menu[0]->query);
                }
            }
            if (!strlen($redir)) $redir = $this->getUrl(['action' => 'dashboard']);
            $this->_response->redirectUrl = $redir;
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

    function executeConfig() {
    }

    function executeManager($mapper) {
                
        $mapperId = $mapper;
        $mapper = null;
        
        $bu = $this->getUrl(array($this->_methodParamName => 'manager', 'mapper' => $mapperId));
        unset($bu->query['managerAction']);
        $contextOptions = array(
            'baseUrl' => $bu->toString(),
            'isInForm' => 'aForm',
        );
        $context = new Ac_Controller_Context_Http($contextOptions);
        $context->populate('request');
        //$this->applyState($context);

        $managerConfig = array('mapperClass' => $mapperId);
        $mapper = Ac_Model_Mapper::getMapper($mapperId);

        if (!$mapper) throw new Ac_Controller_Exception("No such mapper: ".$mapperId, 404);

        if (strlen($mapper->managerClass)) $class = $mapper->managerClass;
            else $class = 'Ac_Admin_Manager';
        if ($extra = $mapper->getManagerConfig()) {
            Ac_Util::ms($managerConfig, $extra);
        }
        $manager = new $class ($context, $managerConfig);
        $manager->_methodParamName = 'managerAction';
        $manager->setApplication($this->getApplication());
        //if ($this->separateToolbar) $manager->separateToolbar = true;
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
            true,
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

    function executeApi($mapper) {
    }

}

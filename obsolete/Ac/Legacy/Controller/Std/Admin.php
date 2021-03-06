<?php

class Ac_Legacy_Controller_Std_Admin extends Ac_Legacy_Controller_Std_Web {

    /**
     * @var Ac_Application
     */
    protected $application = null;

    var $resetStateOnNoArgs = false;
    
    var $separateToolbar = false;
    
	var $_templateClass = 'Ac_Legacy_Controller_Std_Admin_Template';
	
	var $stateVarName = true;
	
	var $_defaultMethodName = 'default';
    
    var $_defaultMapper = false;
    
    var $extraAssets = array();
	
    function doListMapperClasses() {
        return array();
    }
	
	function executeStart() {
	    $this->_templatePart = 'start';
	}
	
	function executeDefault() {
	    $this->_response->redirectUrl = $this->getUrl(array($this->_methodParamName => 'start'), false);
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
	
	function applyState(Ac_Legacy_Controller_Context_Http $c) {
        $dataPath = $c->getDataPath(true);
        $state = $this->loadState($dataPath);
        
        // Workaround for case when sometimes Cancel button saves ID of record into the state and 
        // all subsequent actions are related only to that record
        Ac_Util::unsetArrayByPath($state, array('keys'));

        $data = $c->getData();
        if (isset($data[$this->_methodParamName]) && $data[$this->_methodParamName] !== 'list') {
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
	
	function executeManager() {
	    $mapperClasses = $this->doListMapperClasses();
	    $mapperId = $this->_context->getData('mapper', $this->_defaultMapper);
	    if (in_array($mapperId, $mapperClasses)) {
	        $bu = $this->getUrl(array($this->_methodParamName => 'manager', 'mapper' => $mapperId));
            $contextOptions = array(
                'baseUrl' => $bu->toString(),
                'isInForm' => 'aForm',
            );
            $context = new Ac_Legacy_Controller_Context_Http($contextOptions);
            $context->populate('request', $px = $mapperId);
            
            $this->applyState($context);
            
            $managerConfig = array('mapperClass' => $mapperId);
            $mapper = Ac_Model_Mapper::getMapper($mapperId);
            
            if (!$mapper) throw new Ac_Legacy_Controller_Exception("No such mapper: ".$mapperId, 404);
            
            if (strlen($mapper->managerClass)) $class = $mapper->managerClass;
                else $class = 'Ac_Admin_Manager';
            if ($extra = $mapper->getManagerConfig()) {
                Ac_Util::ms($managerConfig, $extra);
            }
            $manager = new $class ($context, $managerConfig, $px);
            $manager->setApplication($this->getApplication());
            if ($this->separateToolbar) $manager->separateToolbar = true;
            $response = $manager->getResponse();
            if ($response->noWrap) {
                $this->_response = $response;
            } else {
                if ($this->separateToolbar) {
                    if (strlen($manager->toolbarContent)) {
                        Ac_Legacy_Output_Joomla15::addHtmlToJoomlaToolbar($manager->toolbarContent);
                    }
                }
                $this->_tplData['manager'] = $manager;
                $this->_tplData['managerResponse'] = $response;
                $this->_templatePart = 'manager';
            }
	    } else {
	        $this->_response->redirectUrl = $this->getUrl(array($this->_methodParamName => 'start'));	        
	    }
	}
	
}

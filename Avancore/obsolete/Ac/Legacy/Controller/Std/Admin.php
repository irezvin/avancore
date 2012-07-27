<?php

class Ac_Legacy_Controller_Std_Admin extends Ac_Legacy_Controller_Std_Web {
	
	var $_templateClass = 'Ac_Legacy_Controller_Std_Admin_Template';
	
	var $stateVarName = true;
	
	var $_defaultMethodName = 'default';
    
    var $_defaultMapper = false;
	
    function doListMapperClasses() {
        return array();
    }
	
	function executeStart() {
	    $this->_templatePart = 'start';
	}
	
	function executeDefault() {
	    $this->_response->redirectUrl = $this->getUrl(array('action' => 'start'), false);
	}
	
	function getStateVarName() {
	    if (!strlen($this->stateVarName)) $res = false;
	    elseif ($this->stateVarName === true) $res = $this->_instanceId;
	    else $res = $this->stateVarName;
	    return $res;
	}
	
	function applyState(Ac_Legacy_Controller_Context_Http $c) {
        $p = $c->getDataPath(true);
        if (strlen($sv = $this->getStateVarName())) {
            if (!isset($_SESSION[$sv])) $_SESSION[$sv] = array();
            $state = Ac_Util::getArrayByPath($_SESSION[$sv], $p);
            
            // Workaround for case when sometimes Cancel button saves ID of record into the state and 
            // all subsequent actions are related only to that record
            Ac_Util::unsetArrayByPath($state, array('keys'));
            
            $data = $c->getData();
            if (isset($data['action']) && $data['action'] == 'list') {
	            if (!is_array($state)) $state = array();
	            if (!count($data)) $state = array();
	            Ac_Util::ms($state, $c->getData());            
	            $c->setData($state);
	            Ac_Util::setArrayByPath($_SESSION[$sv], $p, $state);
            }
        }
	}
	
	function executeManager() {
	    $mapperClasses = $this->doListMapperClasses();
	    $mapperId = $this->_context->getData('mapper', $this->_defaultMapper);
	    if (in_array($mapperId, $mapperClasses)) {
	        $bu = $this->getUrl(array('action' => 'manager', 'mapper' => $mapperId));
            $contextOptions = array(
                'baseUrl' => $bu->toString(),
                'isInForm' => 'aForm',
            );
            $context = new Ac_Legacy_Controller_Context_Http($contextOptions);
            $context->populate('request', $px = $mapperId);
            
            $this->applyState($context);
            
            $managerConfig = array('mapperClass' => $mapperId);
            $manager = new Ac_Admin_Manager($context, $managerConfig, $px);
            //$manager->separateToolbar = true;
            $response = & $manager->getResponse();
            if ($manager->toolbarContent) {
            	$GLOBALS['aeToolbar']->content = $manager->toolbarContent; 
            }
            $this->_tplData['manager'] = $manager;
            $this->_tplData['managerResponse'] = $response;
            $this->_templatePart = 'manager';
	    } else {
	        $this->_response->redirectUrl = $this->getUrl(array('action' => 'start'));	        
	    }
	}
	
}

<?php

class Ac_Cr_Controller extends Ac_Autoparams {

    protected $onlyListedParams = false;
    
    protected $paramBlock = false;
    
    protected $paramOverrides = array();
    
    protected $extraParamNames = array();
    
    protected $paramBlockPrototype = false;
    
    protected $tempParamStack = array();
    
    /**
     * @var Ac_Cr_Context
     */
    protected $context = false;

    /**
     * @var Ac_Application
     */
    protected $application = false;
    
    /**
     * @return Ac_Cr_Context
     */
    function getContext() {
        if ($this->context === false) {
            if ($this->application) $this->context = $this->application->createDefaultContext ($this);
                else $this->context = new Ac_Cr_Context();
        }
        return $this->context;
    }

    function setApplication(Ac_Application $application) {
        if ($this->application !== false) throw Ac_E_InvalidCall::canRunMethodOnce ($this, __FUNCTION__);
        $this->application = $application;
    }

    /**
     * @return Ac_Application
     */
    function getApplication() {
        return $this->application;
    }
    
    function setParamBlockPrototype(array $paramBlockPrototype) {
        if ($this->paramBlock) throw new Ac_E_InvalidCall("Cannot setParamBlockPrototype() after setParamBlock()");
        $this->paramBlockPrototype = $paramBlockPrototype;
    }
    
    function setParamBlock(Ac_Param_Block $paramBlock) {
        if ($this->paramBlock !== false) throw Ac_E_InvalidCall::canRunMethodOnce ($this, __FUNCTION__);
        if ($this->paramBlockPrototype !== false) throw new Ac_E_InvalidCall("Cannot setParamBlock() after setParamBlockPrototype()");
        $this->paramBlock = $paramBlock;
        $this->paramBlock->setSource($this->getContext());
    }
    
    /**
     * @return Ac_Param_Block
     */
    function getParamBlock() {
        if ($this->paramBlock === false) {
            if ($this->paramBlockPrototype)
                $this->paramBlock = Ac_Autoparams::factory($this->paramBlockPrototype, 'Ac_Param_Block');
                $this->paramBlock->setSource($this->getContext());
        }
        return $this->paramBlock;
    }
    
    function listParams() {
        $res = array('action');
        if ($b = $this->getParamBlock()) {
            $res = array_merge($res, $b->listParams());
        }
        if ($this->extraParamNames) {
            if ($res) {
                $res = array_unique(array_merge($res, $this->extraParamNames));
            } else {
                $res = $this->extraParamNames;
            }
        }
        return $res;
    }
    
    protected function checkParamNames($paramNames) {
        if ($this->onlyListedParams) {
            if ($d = array_diff($this->listParams(), Ac_Util::toArray($paramNames)))
                throw Ac_E_InvalidCall::noSuchItem('param', $d[0], 'listParams');
        }
    }
    
    function setParams(array $params, $merge = false, $overrideAll = false) {
        $this->checkParamNames(array_keys($params));
        if ($merge) Ae_Util::ms($this->paramOverrides, $params);
            else $this->paramOverrides = $params;
        if ($overrideAll) {
            foreach(array_diff($this->listParams(), array_keys($params)) as $key) $this->paramOverrides[$key] = null;
        }
    }
    
    function getParam($paramName) {
        if (array_key_exists($paramName, $this->paramOverrides)) {
            $res = $this->paramOverrides[$paramName];
        } else {
            $this->checkParamNames($paramName);
            $res = $this->getParamBlock()->getParam($paramName)->getValue();
        }
        return $res;
    }
    
    function setParam($paramName, $paramValue) {
        $this->checkParamNames($paramName);
        $this->paramOverrides[$paramName] = $paramValue;
    }
    
    function resetParam($paramName) {
        if (array_key_exists($paramName, $this->paramOverrides)) unset($this->paramOverrides[$paramName]);
            else $this->checkParamNames($paramName);
    }
    
    /**
     * @return Ac_Cr_Controller_Result
     */
    function getResult($tempParams = false, $overrideAll = false) {
        if ($tempParams !== false && !is_array($tempParams)) throw new Ac_E_InvalidCall("\$tempParams should be either FALSE or an array");
        if ($tempParams !== false) {
            array_push($this->tempParamStack, $this->paramOverrides);
            $this->setParams($tempParams, false, $overrideAll);
        }
        $res = $this->doGetResult();
        if ($tempParams !== false) $this->paramOverrides = array_pop ($this->tempParamStack);
        return $res;
    }
    
    protected function doGetResult() {
        try {
            
        } catch (Ac_E_ControllerException $e) {
            
        }
    }   
    
    /**
     * @return Ac_Cr_Response
     */
    function getResponse() {
    }    
    
    protected function getActionName() {
        return $this->getParam('action');
    }

    function listActions() {
        $res = array();
        $cm = get_class_methods(get_class($this));
        foreach ($cm as $m) {
            if (!strncmp($m, $s = 'execute', $l = strlen($s)) && strlen($m) > $l) {
                $a = substr($m, $l);
                $a{0} = strtolower($a{0});
                $res[] = $a;
            }
        }
        return $res;
    }
    
    function listControllers() {
        $res = array();
        return $res;
    }
    
    function getController($id) {
        throw Ac_E_InvalidCall::noSuchItem('controller', $id, 'listControllers');
    }

    function getMethodWithArgs($actionName) {
    }
    
    protected function handleNoAction() {
    }
    
    protected function pushTemplate($data) {
    }
    
    protected function pushResponse($data) {
    }
    
    protected function pushAction($name, array $params = array()) {
    }
    
    protected function pushRequest($request) {
    }    
    
    
}
<?php

class Ac_Cr_Controller extends Ac_Prototyped {

    protected $onlyListedParams = false;
    
    protected $paramBlock = false;
    
    protected $paramOverrides = array();
    
    protected $extraParamNames = array();
    
    protected $paramBlockPrototype = false;
    
    protected $tempParamStack = array();

    protected $invokeDataStack = array();
    
    /**
     * @var Ac_Cr_Context
     */
    protected $context = false;

    /**
     * @var Ac_Application
     */
    protected $application = false;
    
    protected $accessors = array();

    /**
     * Lazy-got action (name of method to be executed or aggregate action controller)
     * @var string|bool
     */
    protected $action = false;
    
    protected $invokeEnvStack = array();
    
    protected $result = array();
    
    protected $lastResult = false;

    function __get($name) {
        if ($name === 'param') {
            return $this->getParamsAccessor();
        } 
        if ($name === 'use') {
            return $this->getUseParamsAccessor();
        }
        throw Ac_E_InvalidCall::noSuchProperty($this, $name, array('param', 'use'));
    }
    
    /**
     * Returns URL valid for the current moment of execution
     * 
     * @param array $params 
     * @return Ac_Cr_Url
     */
    function getUrl($params = array(), $fullOverride = false) {
        return $this->getContext()->createUrl($params);
    }

    function setAction($action) {
        $this->action = $action;
    }

    function getAction() {
        if ($this->action === false) {
            $this->action = $this->use->action->value();
        }
        return $this->action;
    }    
    
    /**
     * @return Ac_Param_Value
     */
    protected function getParamsAccessor() {
        if (!isset($this->accessors['params'])) {
            $this->accessors['params'] = new Ac_Param_Value($this->paramBlock? $this->paramBlock : $this->context->param, null);
        }
        return $this->accessors['params'];
    }
    
    /**
     * @return Ac_Param_Value
     */
    protected function getUseParamsAccessor() {
        if (!isset($this->accessors['useParams'])) {
            $this->accessors['useParams'] = new Ac_Param_Value($this->paramBlock? $this->paramBlock : $this->context->use, null);
        }
        return $this->accessors['useParams'];
    }
    
    /**
     * @return Ac_Cr_Context
     */
    function getContext() {
        if ($this->context === false) {
            // setter is used internally to retrieve extra initialization data from Context
            if ($this->application) $this->setContext($this->application->createDefaultContext ($this));
                else $this->setContext(new Ac_Cr_Context());
        }
        return $this->context;
    }
    
    function setContext(Ac_Cr_Context $context) {
        if ($this->context) throw Ac_E_InvalidCall::canRunMethodOnce ($this, __FUNCTION__);
        $this->context = $context;
        $this->initializeFromContext();
    }
    
    protected function initializeFromContext() {
        // TODO - load router-provided data from the context
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
                $this->paramBlock = Ac_Prototyped::factory($this->paramBlockPrototype, 'Ac_Param_Block');
                $this->paramBlock->setSource($this->getContext());
        }
        return $this->paramBlock;
    }
        
    /**
     * TODO: think out how tempParams are applied: before or after param block
     * @return Ac_Cr_Result
     */
    function getResult(/*$tempParams = false, $overrideAll = false*/) {
        
        if ($this->lastResult === false) {
            $this->lastResult = null; // lock for the case if doGetResult will occasionally call $this->getResult()
            $res = $this->doGetResult();
            $this->lastResult = $res;
        }
        
        return $this->lastResult;
    }
    
    protected function doGetResult() {
        try {
            $action = $this->getAction();
            if (($impl = $this->getActionImplementation($action)) !== false) {
                if (is_string($impl)) $res = $this->invokeActionMethod($impl);
                else {
                    $res = $impl->getResult();
                }
            } else {
                $res = $this->invokeActionMethod('notFoundAction', array($action));
            }
        } catch (Exception $e) {
            $res = $this->invokeActionMethod('exceptionAction', array($e));
        }
        return $res;
    }
    
    function notFoundAction($action) {
        throw new Ac_E_ControllerException("Action not found: ".$action);
    }
    
    function exceptionAction(Exception $e) {
        // TODO: more complex logic here
        throw $e;
    }
    
    protected function getInvokeEnvDefaults() {
        return array(
            'result' => array(), 
        );
    }
    
    /**
     * Collects data that action method should alter during execution.
     * Is called
     * - before action invocation - to save current data (for nested method calls)
     * - after action invocation - to collect data modified by the action and pass results to processInvokeEnv
     * 
     * @return array
     */
    protected function getInvokeEnv() {
        $res = array();
        foreach (array_keys($this->getInvokeEnvDefaults()) as $prop) $res[$prop] = $this->$prop;
        return $res;
    }
    
    protected function clearInvokeEnv() {
        foreach ($this->getInvokeEnvDefaults() as $prop => $val) $this->$prop = $val;
    }
    
    protected function setInvokeEnv(array $invokeEnv) {
        foreach ($this->getInvokeEnvDefaults() as $prop => $value) 
            $this->$prop = isset($invokeEnv[$prop]) && is_array($invokeEnv[$prop])? 
                $invokeEnv[$prop] : $value;
    }
    
    /**
     * @param array $invokeEnv
     * @param type $methodResult
     * @param type $methodOutput 
     * 
     * @return Ac_Cr_Result
     * 
     * @TODO use Ac_E_Controller_InvalidMethodResult
     */
    protected function processInvokeEnv(array $invokeEnv, $methodReturnValue, $methodOutput) {
        $result = false;
        if (is_object($methodReturnValue) && $methodReturnValue instanceof Ac_Cr_Result) {
            if ($invokeEnv['result']) {
                throw new Exception("Action method should either return Ac_Cr_Result instance or populate Ac_Cr_Controller->\$result, but not both");
            } else {
                $result = $methodReturnValue;
            }
        } else {
            if (is_array($invokeEnv['result']) || is_object($invokeEnv['result'])) {
                $result = Ac_Prototyped::factory($invokeEnv['result'], 'Ac_Cr_Result');
                if ($result !== $methodReturnValue) $result->setMethodReturnValue($methodReturnValue);
                $result->setMethodOutput($methodOutput);
            } else {
                throw new Exception("Unknown type of Ac_Cr_Controller->\$result; array, object or Ac_Cr_Result instance must be provided");
            }
        }
        return $result;
    }
    
    /**
     * @return Ac_Cr_Result
     * @param string $impl Name of the function 
     * @param array $args Arguments to the function
     * @throws Exception 
     */
    protected function invokeActionMethod($impl, array $args = array()) {
        $es = 0;
        try {
            array_push($this->invokeEnvStack, $this->getInvokeEnv());
            $es++;
            $this->clearInvokeEnv();
            ob_start();
            $methodReturnValue = call_user_func_array(array($this, $impl), $args);
            $methodOutput = ob_get_clean();
            $resultEnv = $this->getInvokeEnv();
            $this->setInvokeEnv(array_pop($this->invokeEnvStack));
            $res = $this->processInvokeEnv($resultEnv, $methodReturnValue, $methodOutput);
            return $res;
        } catch (Exception $e) {
            // TODO: write captured output somewhere
            ob_end_flush();
            if ($es) {
                $this->setInvokeEnv(array_pop($this->invokeEnvStack));
            }
            throw $e;
        }
    }
    
    /**
     * Returns one of following values:
     * - string - method name 
     * - object - action controller that will process this action 
     * - FALSE if there is no such action
     * 
     * @param array|string $action
     * @throws Ac_E_InvalidCall
     * @throws bool|string|object
     */
    protected function getActionImplementation($action) {
        $res = false;
        if (is_array($action)) {
            throw new Exception("Action path not implemented yet");
        } elseif (is_string($action)) {
            if (strlen($action)) {
                if (method_exists($this, $method = 'action'.ucfirst($action))) {
                    $res = $method;
                } elseif (in_array($action, $this->listActionControllers())) {
                    $res = $this->getActionController($action);
                }
            } elseif (method_exists($this, $method = 'defaultAction')) {
                $res = $method;
            }
        } else throw Ac_E_InvalidCall::wrongType ('action', $action, array('array', 'string'));
        return $res;
    }
    
    /**
     * @return Ac_Cr_Response
     */
    function getResponse() {
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
    
    function listActionControllers() {
        $res = array();
        return $res;
    }
    
    /**
     * Resets the controller to the post-initialization state
     */
    function reset() {
        $this->action = false;
        $this->lastResult = false;
        $this->clearInvokeEnv();
        $this->invokeEnvStack = array();
        if ($this->context) $this->context->forgetAllParams();
        $this->initializeFromContext();
    }
    
    function getController($id) {
        throw Ac_E_InvalidCall::noSuchItem('controller', $id, 'listControllers');
    }
    
}
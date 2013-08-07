<?php

/**
 * Controller for Avancore 0.2 -- presentation controller 
 */
class Ac_Legacy_Controller implements Ac_I_Prototyped {

    /**
     * @var Ac_Application
     */
    protected $application = null;
    
    /**
     * @var string
     */
    var $_instanceId = false;
    
    /**
     * @access protected 
     */
    var $_defaultResponseClass = 'Ac_Legacy_Controller_Response_Html';
    
    /**
     * @access protected 
     */
    var $_templateClass = false;
    
    var $_templatePart = false;
    
    /**
     * @var Ac_Legacy_Template_Html
     */
    var $_template = false;
    
    /**
     * @var Ac_Legacy_Controller_Context_Http
     */
    var $_context = false;
    
    /**
     * @var array
     */
    var $_rqData = false;
    
    /**
     * @var array
     */
    var $_state = false;
    
    /**
     * @var array
     */
    var $_rqWithState = false;
    
    /**
     * @var bool|array
     */
    var $_errors = false;
    
    /**
     * @var Ac_Legacy_Controller_Context_Info 
     */
    var $_contextInfo = false;
    
    /**
     * @var Ac_Legacy_Controller_Response
     */
    var $_response = false;
    
    var $_bound = false;
    
    /**
     * Prepend response content with controller methods' text output 
     * @var bool|string
     */
    var $_outputToResponse = false;
    
    /**
     * Add method' output to a template variable (provide name here)
     * @var false|string
     */
    var $_outputToTemplate = false;
    
    /**
     * Name of a request parameter that holds name of a controller method.
     * Actual name of a method that will be called will be 'execute<ParamValue>'
     * If such method won't be found, execFallback will be called ('exec' prefix is used instead of 'execute' to prevent calling of fallback handler from the request)
     * @var string
     * @access protected
     **/
    var $_methodParamName = 'action';
    
    /**
     * Name of a default method that will be used when method name is not provided in the request. If empty string is given, 'execute()' method will be called
     * @var string|bool Name of default method or FALSE to use fallback handler if no method name is provided
     * @access protected
     */
    var $_defaultMethodName = '';
    
    var $_errorMethodName = '';
    
    var $_methodParamValue = false;
    
    var $_methodName = false;
    
    var $_autoTplVars = array();
    
    var $_tplData = array();
    
    var $_stateData = array();
    
    var $_autoStateVars = array();
    
    var $templateExtraVars = array();
    
    function hasPublicVars() {
        return true;
    }
    
    /**
     * @param Ac_Legacy_Controller_Context $context
     */
    function __construct ($context = null, $options = array(), $instanceId = false) {

        // Make the call Ac_Prototyped::factory compatible 
        if (is_array($context) && func_num_args() == 1) {
            $options = $context;
            $context = null;
            if (isset($options['context'])) $context = $options['context'];
            if (isset($options['instanceId'])) $instanceId = $options['instanceId'];
        }
        
    	if (!(is_object($context) && $context instanceof Ac_Legacy_Controller_Context)) {
        	if ($context instanceof Ac_Url) $url = $context->cloneObject();
            elseif (is_string($context)) $url = new Ac_Url($context);
            else $url = Ac_Url::guess(true);
            
            $context = null;
            
            $path = '';
            if (is_array($context)) $path = $context;
                elseif (is_string($context)) $path = Ac_Util::pathToArray($context);
            $context = new Ac_Legacy_Controller_Context_Http();
            $context->setBaseUrl($url);
            $context->populate(array('cookie', 'get', 'post'), $path);
	    }

        $this->_context = $context;
        Ac_Util::bindAutoparams($this, $options);
        if ($instanceId !== false) $this->_instanceId = $instanceId;
            else $this->_instanceId = strtolower(get_class($this));
        $this->doInitProperties($options);
        if ($this->doesBindOnCreate()) $this->bindFromRequest();
	    
    }
    
    /**
     * Returns names of fields that will be automatically bound from request
     * @return array
     */
    function listRequestVars() {
        return array();
    }
    
    // --------------------- template methods ---------------------
    
    /**
     * Is called from the constructor
     * @param array $options
     */
    function doInitProperties($options = array()) {
    }
    
    /**
     * Should update controller parameters from $this->_rqData
     */
    function doBindFromRequest() {
    }
    
    /**
     * Should validate bound parameters
     */
    function doValidateRequest() {
    }
    
    /**
     * Should populate $this->_contextInfo properties 
     */
    function doInitializeRequestInfo() {
    }
    
    /**
     * Should set $this->_template properties
     */
    function doPopulateTemplate() {
    }
    
    /**
     * Should set $this->_response properties
     */
    function doPopulateResponse() {
    }
    
    function doBeforeExecute() {
    }
    
    function doAfterExecute() {
    }
    
    function doBeforeGetTemplate() {
    }
    
    /**
     * Template method that should return true if controller should call $this->bindFromRequest() immediately after initialisation.
     * Can be overridden in descendant classes.
     * @return bool
     */
    function doesBindOnCreate() {
        return false;
    }
    
    // --------------------- context-related methods ---------------------

    function bindFromRequest() {
        if ($this->_bound === false) {
            $this->_bound = true;
            $this->_rqData = $this->_context->getData();
            $this->_state = $this->_context->getState();
            $this->_rqWithState = Ac_Util::m($this->_state, $this->_rqData, true);
            $this->doBindFromRequest();
        }
    }
    
    function isRequestValid() {
        if ($this->_errors === false) {
            $this->_errors = array();
            $this->bindFromRequest();
            $this->doValidateRequest();
        }
        return !count($this->_errors);
    }
    
    function getRequestErrors($key = false) {
        $this->isRequestValid();
        if ($key !== false) $res = isset($this->_errors[$key])? $this->_errors[$key] : false;
            else $res = $this->_errors;
        return $res; 
    }
    
    /**
     * @return Ac_Legacy_Controller_Context_Info
     */
    function getContextInfo() {
        if ($this->_contextInfo === false) {
            $this->bindFromRequest();
            $this->_contextInfo = new Ac_Context_Info($this);
            $this->doInitializeRequestInfo();
        }
        return $this->_contextInfo;
    }
    
    /**
     * Returns context of the controller 
     * @return Ac_Legacy_Controller_Context
     */
    function getContext() {
        return $this->_context;
    }
    
    /**
     * @return mixed
     */
    function getMethodParamValue() {
        if ($this->_methodParamValue === false) {
            $this->_methodParamValue = null;
            $mp = $this->_methodParamName;
            if (isset($this->_rqData[$mp])) {
                $this->_methodParamValue = $this->_rqData[$mp];
            }
        }
        return $this->_methodParamValue;
    }
    
    /**
     * @return string
     */
    function getMethodName() {
        if ($this->_methodName === false) {
            $this->_methodName = null;
            $v = $this->getMethodParamValue();
            if (is_null($v) && $this->_defaultMethodName !== false) $v = $this->_defaultMethodName;
            if (is_string($v)) {
                $mtdName = 'execute'.ucfirst($v);
                if (method_exists($this, $mtdName)) $this->_methodName = $mtdName;
            }
        }
        return $this->_methodName;
    }

    /**
     * @return Ac_Url
     */
    function getUrl($extraParams = array(), $preserveCurrentParams = true) {
        $res = false;
        if (is_a($this->_context, 'Ac_Legacy_Controller_Context_Http')) {
            $ctx = $this->_context->cloneObject();
            $ctx->setData();
            $myParams = $this->_stateData;
            foreach ($this->_autoStateVars as $vn) {
                $vv = false;
                if (is_callable($call = array($this, $cName = 'get'.ucfirst($vn)))) {
                    $vv = $this->$cName();
                } elseif (isset($this->{$vn})) $vv = $this->$vn;
                if (($vv !== false) && !is_null($vv)) {
                    $myParams[$vn] = $vv;
                }
            }
            $extraParams = Ac_Util::m($myParams, $extraParams);
            $res = $ctx->getUrl($extraParams);
        }
        return $res;
    }
    
    // --------------------- response-related methods ---------------------
    
    function getResponseClass() {
        $this->bindFromRequest();
        return $this->_defaultResponseClass;
    }
    
    function execute() {
    }
    
    function execFallback($methodParamValue = null) {
    }
    
    /**
     * @return Ac_Legacy_Controller_Response_Html
     */
    function getResponse($methodName = false) {
        if ($methodName !== false) $this->_methodName = $methodName;
        $validRequest = $this->isRequestValid();
        if (!$validRequest && $this->_errorMethodName) {
            $this->_methodName = $this->_errorMethodName;
        }
        if ($validRequest || $this->_errorMethodName) {
            if ($this->_response === false) {
                if ($this->_outputToResponse || ($this->_outputToTemplate !== false)) {
                    ob_start();
                }
                $rc = $this->getResponseClass();
                $this->_response = new $rc;
                if ($this->doBeforeExecute($methodName) !== false) {
                    if ($m = $this->getMethodName()) {
                        $this->$m();
                    } else {
                        $this->execFallback($this->getMethodParamValue());
                    }
                }
                $this->doAfterExecute();
                if ($this->_outputToTemplate !== false) {
                    $this->_tplData[$this->_outputToTemplate] = ob_get_contents();
                    if (!$this->_outputToResponse) ob_end_clean();
                }
                                    
                $this->getTemplate();
                if (strlen($this->_templatePart) && $this->_template) {
                    $this->_response->content .= $this->_template->fetch($this->_templatePart);
                }
                $this->doPopulateResponse();
                if ($this->_outputToResponse) {
                    if (is_string($this->_outputToResponse)) $v = $this->_outputToResponse;
                        else $v = 'content';
                    $this->_response->$v = ob_get_clean().$this->_response->content;
                }
                $res = $this->_response;
            } else {
                $res = $this->_response;
            }
        } else {
            $res = false;
        }
        return $res;
    }
    
    /**
     * @return Ac_Legacy_Template_Html
     */
    function getTemplate() {
        if ($this->_template === false) {
            $this->doBeforeGetTemplate();
            if ($tc = $this->_templateClass) {
                $this->_template = new $tc();
                if ($this->_response === false) {
                    if ($rc = $this->getResponseClass()) {
                        $this->_response = new $rc;
                    }
                }
                $this->_template->htmlResponse = $this->_response;
                $this->_template->setVars($this->_getTplData());
                $this->doPopulateTemplate();
            }
        }
        return $this->_template;
    }

    function getInstanceId() {
        return $this->_instanceId;
    }

    function _listAutoTplVars() {
        return $this->_autoTplVars;
    }
    
    function _getTplData() {
        $res = Ac_Util::m($this->templateExtraVars, $this->_tplData);
        $res['controller'] = $this;
        $res['context'] = $this->getContext();
        $res['newUi'] = true;
        $myVars = array_keys(get_object_vars($this));
        foreach ($this->_listAutoTplVars() as $myVar => $tplVar) {
            if (is_numeric($myVar)) $myVar = $tplVar;
            if (!isset($res[$tplVar])) {
                $val = false;
                $res[$tplVar] = false;
                if (is_callable(array($this, $methodName = 'get'.$myVar))) {
                    $val = $this->$methodName();
                } elseif(in_array($myVar, $myVars)) {
                    $val = $this->$myVar; 
                } else {
                    trigger_error("Cannot find neither getter nor property \${$myVar} that was listend in autoTplVars()", E_USER_WARNING);
                }
                $res[$tplVar] = $val; 
            }
        }
        return $res;
    }

    function setApplication(Ac_Application $application) {
        if ($this->application) throw new Exception("Can setApplication() only once");
        $this->application = $application;
    }

    /**
     * @return Ac_Application
     */
    function getApplication() {
        if (!$this->application) return Ac_Application::getDefaultInstance();
        return $this->application;
    }    
    
}
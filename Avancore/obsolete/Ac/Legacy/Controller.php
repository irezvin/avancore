<?php

class Ac_Legacy_Controller implements Ac_I_Prototyped, Ac_I_Controller, Ac_I_NamedApplicationComponent {

    /**
     * @var Ac_Application
     */
    protected $application = null;
    
    /**
     * @var string
     */
    var $_instanceId = false;
    
    var $_responseClass = 'Ac_Legacy_Controller_Response_Html';
    
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
     * @var Ac_Legacy_Controller_Response
     */
    var $_response = false;
    
    const OUTPUT_RESPONSE_PREPEND = 1;
    
    const OUTPUT_RESPONSE_APPEND = 2;
    
    const OUTPUT_RESPONSE_DROP = 0;
    
    /**
     * Prepend response content with controller methods' text output (only if _outputToTemplate === false or template not set)
     * @var bool|string
     */
    var $_outputToResponse = self::OUTPUT_RESPONSE_PREPEND;
    
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
    
    var $_resultToResponseVar = false;
    
    /**
     * Name of a default method that will be used when method name is not provided in the request. If empty string is given, 'execute()' method will be called
     * @var string|bool Name of default method or FALSE to use fallback handler if no method name is provided
     * @access protected
     */
    var $_defaultMethodName = '';
    
    var $_methodParamValue = false;
    
    var $_methodName = false;
    
    var $_autoTplVars = array();
    
    var $_tplData = array();
    
    var $_stateData = array();
    
    var $templateExtraVars = array();
    
    var $simpleCaching = null;
    
    /**
     * @var array Names of controller properties (i.e. userId -> getUserId() will be called) to add to cache Id
     */
    var $simpleCacheExtra = array();
    
    /**
     * @var Ac_Cache_Abstract
     */
    protected $cache = true;
    
    protected $hitCache = false;
    
    function setId($id) {
        $this->_instanceId = $id;
    }
    
    function getId() {
        return $this->_instanceId;
    }
    
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
            $context->populate(array('get', 'post'), $path);
	    }
        
        if ($instanceId !== false) $this->_instanceId = $instanceId;
            else $this->_instanceId = strtolower(get_class($this));

        Ac_Util::bindAutoparams($this, $options);
        $this->doInitProperties($options);

        $this->setContext($context);
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
    
    function doBeforeExecute() {
    }
    
    function doAfterExecute() {
    }
    
    // --------------------- context-related methods ---------------------

    function bindFromRequest() {
        $this->_rqData = $this->_context->getData();
        $this->_state = $this->_context->getState();
        $this->_rqWithState = Ac_Util::m($this->_state, $this->_rqData, true);
        $this->doBindFromRequest();
    }
    
    function isRequestValid() {
        return true;
    }
    
    function resetState() {
        if ($this instanceof Ac_Form_Control) var_dump('rs', ''.(new Exception));
        // reset controller state on setContext()
        $this->_methodName = false;
        $this->_methodParamValue = false;
        $this->_response = false;
        $this->_state = array();
        $this->_stateData = false;
        $this->_rqData = false;
        $this->_rqWithState = false;
        $this->_tplData = array();
        $this->_templatePart = false;
    }
    
    function setContext(Ac_Legacy_Controller_Context $context) {
        if ($this->_context === $context) return;
        $hadOldContext = (bool) $this->_context;
        $this->_context = $context;
        if ($hadOldContext) $this->resetState();
        $this->bindFromRequest();
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
    function getUrl($extraParams = array(), $preserveCurrentParams = false) {
        if (!$this->_context instanceof Ac_Legacy_Controller_Context_Http) return;
        if ($this->_stateData) {
            $ctx = clone $this->_context;
            if (!$preserveCurrentParams) $ctx->setData(array());
            $ctx->updateData($this->_stateData);
        } else {
            $ctx = $this->_context;
        }
        $res = $ctx->getUrl($extraParams, $preserveCurrentParams);
        return $res;
    }
    
    // --------------------- response-related methods ---------------------
    
    function execute() {
    }
    
    function execFallback($methodParamValue = null) {
    }
    
    
    // TODO: add mixin support
    protected function calcAndValidateMethodArgs($methodName, array $positionalArgs = array()) {
        $signature = Ac_Accessor::getMethodSignature(get_class($this), $methodName);
        $pos = 0;
        $numPositional = count($positionalArgs);
        $res = array();
        foreach ($signature as $arg => $info) {
            $hasValue = false;
            $value = false;
            if ($pos < $numPositional) {
                $hasValue = true;
                $value = $positionalArgs[$pos];
            } else {
                $value = $this->_context->getData($arg, false);
                $hasValue = $value !== false;
            }
            if ($hasValue) {
                $res[] = $value;
            } else {
                if (!$info['optional']) {
                    throw new Ac_E_ControllerException("Bad request: required parameter '{$arg}' is missing", 400);
                }
                $res[] = $info['defaultValue'];
            }
            $pos++;
        }
        return $res;
    }
    
    function calcUrlMapperConfig() {
        $res = array('class' => 'Ac_UrlMapper_StaticSignatures', 'controllerClass' => get_class($this));
        return $res;
    }
    
    /**
     * @return Ac_Cache_Abstract
     */
    function getCache() {
        if (is_object($this->cache)) return $this->cache;
        if ($this->cache === false) return null;
        if ($this->cache === true) $this->cache = $this->getApplication()->getCache();
        elseif (is_array($this->cache)) $this->cache = Ac_Util::m(Ac_Cache_Abstract::getDefaultPrototype(), $this->cache);
        $this->cache = Ac_Prototyped::factory($this->cache, 'Ac_Cache_Abstract');
        return $this->cache;
    }
    
    function setCache($cache) {
        $this->cache = $cache;
    }
    
    /**
     * @return Ac_Cache_Accessor
     */
    protected function getSimpleCachingAccessor(array $getResponseMethodArgs) {
        if (!$this->simpleCaching || $this->_context->requestMethod !== 'get') return new Ac_Cache_Accessor('');
        $id['responseMethodArgs'] = $getResponseMethodArgs;
        $id['baseUrl'] = (string) $this->_context->getBaseUrl();
        $id['contextData'] = $this->_context->getData();
        if ($this->simpleCacheExtra) $id['simpleCacheExtra'] = Ac_Util::getObjectProperty($this, $this->simpleCacheExtra);
        $c = $this->getCache();
        return new Ac_Cache_Accessor($id, $c, $this->_instanceId);
    }
    
    /**
     * @return Ac_Legacy_Controller_Response_Html
     */
    function getLastResponse() {
        return $this->_response;
    }
    
    /**
     * @return Ac_Legacy_Controller_Response_Html
     */
    function getResponse($methodName = false) {
        $this->hitCache = false;
        if ($methodName !== false && strncmp($methodName, ($s = 'execute'), strlen($s))) {
            $methodName = 'execute'.ucfirst($methodName);
        }
        
        if ($this->_response) return $this->_response;
        
        $simpleCachingAccessor = $this->getSimpleCachingAccessor(func_get_args());
        if ($resp = $simpleCachingAccessor->getData()) {
            $this->hitCache = true;
            $this->_response = $resp;
            return $this->_response;
        }
            
        if ($methodName !== false) $this->_methodName = $methodName;
        $validRequest = $this->isRequestValid(); // TODO: makes no sense since we don't know if request is valid at this moment
        if (!$validRequest) {
            throw new Ac_E_ControllerException("Bad request", 400);
        }
        ob_start();
        if (!strlen($this->_responseClass)) {
            throw Ac_E_InvalidUsage(__CLASS__.': _responseClass not set');
        }
        $this->_response = new $this->_responseClass;
        if ($this->doBeforeExecute($methodName) !== false) {
            if ($m = $this->getMethodName()) {
                if (func_num_args() > 1) {
                    $posArgs = func_get_args();
                    array_shift($posArgs);
                } else $posArgs = array();
                $methodArgs = $this->calcAndValidateMethodArgs($m, $posArgs);
                if ($methodArgs) $result = call_user_func_array(array($this, $m), $methodArgs);
                else $result = $this->$m();
                // TODO: improve magic result handling here
                if ($this->_resultToResponseVar) $this->_response->{$this->_resultToResponseVar} = $result;
            } else {
                $this->execFallback($this->getMethodParamValue());
            }
        }
        $this->doAfterExecute();
        $outputBuffer = ob_get_clean();
        $handledOutput = !strlen($outputBuffer);
        // TODO: automatically detect template class and template part
        $template = $this->getTemplate();
        $willRenderTemplate = $template && strlen($this->_templatePart);
        if (!$handledOutput && $willRenderTemplate) {
            if ($this->_outputToTemplate !== false) {
                $this->_tplData[$this->_outputToTemplate] = $outputBuffer;
                $handledOutput = true;
            }
        }
        if (!$handledOutput) {
            if ($this->_outputToResponse === self::OUTPUT_RESPONSE_PREPEND) {
                $this->_response->content = $outputBuffer.$this->_response->content;
            } elseif ($this->_outputToResponse === self::OUTPUT_RESPONSE_APPEND) {
                $this->_response->content .= $outputBuffer;
            } else {
            }
            $handledOutput = true;
        }
        if ($willRenderTemplate) {
            $this->_response->content .= $template->fetch($this->_templatePart);
        }
        if ($this->simpleCaching) $simpleCachingAccessor->put($this->_response);
        return $this->_response;
    }
    
    /**
     * @return Ac_Legacy_Template_Html
     */
    function getTemplate() {
        if ($this->_template === false) {
            if ($tc = $this->_templateClass) {
                $this->_template = new $tc();
                if ($this->_response === false) {
                    if ($rc = $this->getResponseClass()) {
                        $this->_response = new $rc;
                    }
                }
                $this->_template->htmlResponse = $this->_response;
                $this->_template->setVars($this->_getTplData());
            }
        }
        return $this->_template;
    }

    function getInstanceId() {
        return $this->_instanceId;
    }

    function _getTplData() {
        $res = Ac_Util::m($this->templateExtraVars, $this->_tplData);
        $res['controller'] = $this;
        $res['context'] = $this->getContext();
        $res['newUi'] = true;
        $myVars = array_keys(get_object_vars($this));
        foreach ($this->_autoTplVars as $myVar => $tplVar) {
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
        if ($this->application && $this->application !== $application) throw new Exception("Can setApplication() only once");
        $this->application = $application;
    }

    /**
     * @return Ac_Application
     */
    function getApplication() {
        if (!$this->application) return Ac_Application::getDefaultInstance();
        return $this->application;
    }
    
    function getHitCache() {
        return $this->hitCache;
    }
    
}
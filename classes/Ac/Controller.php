<?php

class Ac_Controller extends Ac_Prototyped implements Ac_I_Controller, Ac_I_NamedApplicationComponent {

    /**
     * @var Ac_Application
     */
    protected $application = null;
    
    /**
     * @var string
     */
    var $_instanceId = false;
    
    var $_responseClass = 'Ac_Controller_Response_Html';
    
    /**
     * @access protected 
     */
    var $_templateClass = false;
    
    var $_templatePart = false;
    
    /**
     * @var Ac_Template_Html
     */
    var $_template = false;
    
    /**
     * @var Ac_Controller_Context_Http
     */
    var $_context = false;
    
    protected $isInit = false;
    
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
     * @var Ac_Controller_Response_Html
     */
    var $_response = false;
    
    var $useUrlMapper = false;
    
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
    
    // may be called in the action method to skip result saving to the cache
    var $cacheSkip = false;
    
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
     * @param Ac_Controller_Context $context
     */
    function __construct (array $options = array()) {
        $this->_instanceId = get_class($this);
        if (isset($options['instanceId'])) {
            $this->_instanceId = $options['instanceId'];
            unset($options['instanceId']);
        }
        $this->doInitProperties($options);
        parent::__construct($options);
        $this->isInit = true;
        $this->onInitComplete();
    }
    
    // --------------------- template methods ---------------------
    
    /**
     * Is called from the constructor
     * @param array $options
     */
    protected function doInitProperties(array & $options) {
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

    protected function onInitComplete() {
        if ($this->_context) {
            $this->bindFromRequest();
        }
    }
    
    function bindFromRequest() {
        if ($this->useUrlMapper) $this->unmapUrl();
        $this->_rqData = $this->getContext()->getData();
        $this->_state = $this->getContext()->getState();
        $this->_rqWithState = Ac_Util::m($this->_state, $this->_rqData, true);
        $this->doBindFromRequest();
    }
    
    protected function unmapUrl() {
        $context = $this->getContext();
        if (!$context) return;
        if (!($context instanceof Ac_Controller_Context_Http)) return;
        $baseUrl = $context->getBaseUrl();
        if (!$baseUrl) return;
        $pathInfo = null;
        $url = clone $baseUrl;
        $urlMapper = $this->createUrlMapper();
        if (!$urlMapper) return;
        if (strlen($url->pathInfo)) {
            $pathInfo = $url->pathInfo;
            $url->pathInfo = '';
        } elseif (isset($url->query['__pathInfo__'])) {
            $pathInfo = $url->query['__pathInfo__'];
            unset($url->query['__pathInfo__']);
        } else {
            $pathInfo = $context->getData('__pathInfo__');
        }
        $oldMapper = $url->getUrlMapper();
        $url->setUrlMapper($urlMapper);
        if ($oldMapper) $urlMapper->setParentMapper($oldMapper);
        if ($pathInfo) {
            $params = $urlMapper->stringToParams($pathInfo);
            if (is_null($params)) {
                throw new Exception("Unparsable path: '{$pathInfo}'");
            }
            $this->_context->updateData($params);
            if (!isset($params['__pathInfo__'])) $this->_context->updateData(['__pathInfo__' => null]);
        }
        $this->_context->setBaseUrl($url);
    }
    
    function isRequestValid() {
        return true;
    }
    
    function resetState() {
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
        $this->cacheSkip = false;
    }
    
    function setContext(Ac_Controller_Context $context) {
        if ($this->_context === $context) return;
        $hadOldContext = (bool) $this->_context;
        $this->_context = $context;
        if ($hadOldContext) {
            $this->resetState();
        }
        if ($this->isInit) {
            $this->bindFromRequest();
        }
    }
    
    /**
     * Returns context of the controller 
     * @return Ac_Controller_Context
     */
    function getContext() {
        return $this->_context;
    }
    
    /**
     * @return Ac_Controller_Context
     */
    protected function guessContext() {
        $url = Ac_Url::guess(true);
        $context = new Ac_Controller_Context_Http();
        $context->setBaseUrl($url);
        $context->populate(array('get', 'post'));
        return $context;
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
        if (!($this->_context instanceof Ac_Controller_Context_Http)) return;
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
    
    protected function getUrlMapperPrototype() {
        $res = array('class' => 'Ac_UrlMapper_StaticSignatures', 'controllerClass' => get_class($this));
        return $res;
    }
    
    /**
     * Creates default URL mapper for this controller
     * @return Ac_UrlMapper_UrlMapper
     */
    function createUrlMapper() {
        $config = $this->getUrlMapperPrototype();
        if (!$config) return null;
        return Ac_Prototyped::factory($config, 'Ac_UrlMapper_UrlMapper');
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
    protected function getSimpleCachingAccessor($responseMethodName, array $getResponseMethodArgs) {
        if (!$this->simpleCaching || $this->_context->requestMethod !== 'get') {
            return new Ac_Cache_Accessor('');
        }
        $id['controllerClass'] = get_class($this);
        $id['controllerId'] = $this->_instanceId;
        $id['responseMethodArgs'] = $getResponseMethodArgs;
        $id['baseUrl'] = (string) $this->_context->getBaseUrl();
        $id['contextData'] = $this->_context->getData();
        if ($this->simpleCacheExtra) $id['simpleCacheExtra'] = Ac_Util::getObjectProperty($this, $this->simpleCacheExtra);
        $this->onSimpleCacheId($responseMethodName, $getResponseMethodArgs, $id);
        $c = $this->getCache();
        $res = new Ac_Cache_Accessor($id, $c, $this->_instanceId);
        return $res;
    }
    
    protected function onSimpleCacheId($methodName, $methodArgs, array & $id) {
    }
    
    /**
     * @return Ac_Controller_Response_Html
     */
    function getLastResponse() {
        return $this->_response;
    }
    
    /**
     * @return Ac_Controller_Response_Html
     */
    function getResponse($methodName = false) {
        $this->hitCache = false;
        if ($methodName !== false && strncmp($methodName, ($s = 'execute'), strlen($s))) {
            $methodName = 'execute'.ucfirst($methodName);
        }
        
        if ($this->_response) return $this->_response;
        if (!$this->_context) $this->_context = $this->guessContext();
        
        $simpleCachingAccessor = $this->getSimpleCachingAccessor($methodName, func_get_args());
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
                if ($this->_resultToResponseVar) $this->_response->{$this->_resultToResponseVar} = $result;
                elseif (is_array($result) || (is_object($result) && $result instanceof JsonSerializable)) {
                    $this->processJsonResult($result);
                }
            } else {
                $this->execFallback($this->getMethodParamValue());
            }
        }
        $this->doAfterExecute();
        $outputBuffer = ob_get_clean();
        $handledOutput = !strlen($outputBuffer);
        // TODO: automatically detect template class and template part
        $template = $this->getTemplate();
        $willRenderTemplate = $template && $this->_templatePart;
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
            if ($this->_templatePart === true) {
                $templatePart = preg_replace('/^execute/', '', $this->_methodName);
            } else {
                $templatePart = $this->_templatePart;
            }
            $this->_response->content .= $template->fetch($templatePart);
        }
        if ($this->simpleCaching && !$this->cacheSkip) {
            $simpleCachingAccessor->put($this->_response);
        }
        return $this->_response;
    }
    
    protected function processJsonResult($result) {
        $this->_response = new Ac_Controller_Response_Html();
        $this->_response->content = json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $this->_response->contentType = 'text/json; charset=utf-8';
        $this->_response->redirectUrl = null;
        $this->_response->noWrap = true;
        $this->_response->noHtml = true;
        $this->_templatePart = false;
    }
    
    /**
     * @return Ac_Template_Html
     */
    function getTemplate() {
        if ($this->_template) return $this->_template;
        if (!$this->_templateClass) return null;
        $tc = $this->_templateClass;
        $this->_template = new $tc();
        if ($this->_response === false) {
            if ($rc = $this->getResponseClass()) {
                $this->_response = new $rc;
            }
        }
        $this->_template->htmlResponse = $this->_response;
        $this->_template->setVars($this->_getTplData());
        return $this->_template;
    }

    function getInstanceId() {
        return $this->_instanceId;
    }

    function _getTplData() {
        $res = Ac_Util::m($this->templateExtraVars, $this->_tplData);
        $res['controller'] = $this;
        $res['context'] = $this->getContext();
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
    
    function param($path, $defaultOrOptions = null, & $found = false) {
        $default = null;
        $found = false;
        if (!is_array($defaultOrOptions)) {
            $default = $defaultOrOptions;
        } else {
            if (array_key_exists('default', $defaultOrOptions)) {
                $default = $defaultOrOptions['default'];
            }
        }
        return $this->getContext()->getData($path, $default, $found);
    }
    
    function require($path, array $options = []) {
        $res = $this->param($path, $options, $found);
        
        if ($found) return $res;
        
        $mappedPath = $this->getContext()->mapParam($path);
        throw new Ac_E_ControllerException("Required parameter '{$mappedPath}' not provided", 400);
    }
    
}

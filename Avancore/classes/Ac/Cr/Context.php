<?php

/**
 * @TODO: magic accessor for getParam / useParam
 * @TODO: think out whether useParam should take data from GET only?
 * 
 * @TODO Possible improvement: ability to replace $pathPrefix with mapping strategy 
 *      and call it during updateUrlWithParams. 
 * 
 * The strategy can be used for JS, HTML name & HTML id mapping too.
 * 
 * @property Ac_Accessor param
 * @property Ac_Accessor used
 */
class Ac_Cr_Context extends Ac_Prototyped {
    
    protected $usedParams = array();
    
    protected $pathPrefix = false;
    
    /**
     * @var Ac_Cr_Context
     */
    protected $topContext = false;
    
    /**
     * @var Ac_Request
     */
    protected $request = false;
    
    /**
     * @var Ac_Request
     */
    protected $translatedRequest = false;
    
    /**
     * @var Ac_Cr_Router
     */
    protected $router = false;
    
    /**
     * @var Ac_Cr_Url
     */
    protected $baseUrl = false;
    
    /**
     * @var Ac_Accessor
     */
    protected $paramAccessor = false;
    
    /**
     * @var Ac_Accessor
     */
    protected $useAccessor = false;
    
    function __get($var) {
        if ($var == 'param') {
            if (!$this->paramAccessor) $this->paramAccessor = new Ac_Accessor($this, true, array(
                'class' => 'Ac_Accessor_Strategy_UseMethod', 
                'getMethodName' => 'getParam',
                'hasMethodName' => 'hasParam',
            ));
            return $this->paramAccessor;
        }
        elseif ($var == 'use') {
            if (!$this->useAccessor) $this->useAccessor = new Ac_Accessor($this, true, array(
                'class' => 'Ac_Accessor_Strategy_UseMethod', 
                'getMethodName' => 'useParam',
                'hasMethodName' => 'hasParam',
            ));
            return $this->useAccessor;
        } else {
            throw new Exception("No such property: ".$var);
        }
    }
    
    function __set($var, $value) {
        if ($var == 'param' || $var == 'use') throw new Exception("Cannot assign value to a read-only property {$var}");
        throw new Exception("No such property: $var");
    }
    
    function __isset($var) {
        $res = false;
        if ($var == 'param' || $var == 'use') $res = true;
        return $res;
    }
    
    function setPathPrefix($pathPrefix) {
        if ($this->pathPrefix !== false) throw Ac_E_InvalidCall::canRunMethodOnce ($this, __FUNCTION__);
        $this->pathPrefix = $pathPrefix;
    }
    
    function getPathPrefix() {
        return $this->pathPrefix;
    }
    
    function setBaseUrl($baseUrl) {
        if ($this->baseUrl !== false) throw Ac_E_InvalidCall::canRunMethodOnce ($this, __FUNCTION__);
        $this->baseUrl = is_object($baseUrl)? $baseUrl : new Ac_Cr_Url($baseUrl);
    }
    
    /**
     * @return Ac_Cr_Url 
     */
    function getBaseUrl() {
        if ($this->baseUrl === false) {
            $this->baseUrl = Ac_Cr_Url::guess(true, $this->getRequest());
            if ($this->pathPrefix) Ac_Util::unsetArrayByPath($this->baseUrl->query, $this->pathPrefix);
                else $this->baseUrl->query = array();
        }
        return $this->baseUrl; 
    }
    
    /**
     * @return Ac_Cr_Routers
     */
    function getRouter() {
        if ($this->router === false) {
            if ($this->topContext) $this->router = $this->topContext->getRouter();
            else $this->router = null;
        }
        return $this->router;
    }
    
    function setRouter(Ac_Router $router = null) {
        if ($this->router !== false) throw Ac_E_InvalidCall::canRunMethodOnce ($this, __FUNCTION__);
        $this->router = $router;
    }
    
    /**
     * @return Ac_Request
     */
    function getRequest() {
        if ($this->request === false) {
            if ($this->topContext) $this->request = $this->topContext->getRequest();
            else {
                $this->request = new Ac_Request();
            }
        }
        return $this->request;
    }
    
    function setRequest(Ac_Request $request) {
        if ($this->request !== false) throw Ac_E_InvalidCall::canRunMethodOnce($this, __FUNCTION__);
        $this->request = $request;
    }
    
    /**
     * @return Ac_Cr_Request
     */
    protected function getTranslatedRequest() {
        if ($this->translatedRequest === false) {
            if ($this->getRouter()) $this->translatedRequest = $this->router->translateRequest($this->getRequest());
                else $this->translatedRequest = $this->getRequest();
        }
        return $this->translatedRequest;
    }
    
    protected function translatePath($ownParamPath) {
        if ($this->pathPrefix) $res = array_merge(Ac_Util::pathToArray($this->pathPrefix), Ac_Util::pathToArray($ownParamPath));
            else $res = Ac_Util::pathToArray($ownParamPath);
        return $res;
    }
    
    function getParam($path, $defaultValue = null, & $found = false) {
        $path = $this->translatePath($path);
        $res = $this->getRequest()->getValue($path, $defaultValue, $found);
        return $res;
    }
    
    function useParam($path, $defaultValue = null, & $found = false) {
        $hash = $this->getPathHash($path);
        if (!isset($this->usedParams[$hash])) {
            $res = $this->getParam($path, $defaultValue, $found);
            $this->usedParams[$hash] = array($path, $res, $found);
        } else {
            list($tmp, $res, $found) = $this->usedParams[$hash];
        }
        return $res;
    }
    
    function hasParam($path) {
        $path = $this->translatePath($path);
        $this->getRequest()->getValue($path, null, $res);
        return $res;
    }
    
    /**
     * @return array 
     */
    function getUsedParams() {
        $res = array();
        foreach ($this->usedParams as $hash => $data) {
            list($path, $value, $found) = $data;
            if (!is_array($path)) $path = Ac_Util::pathToArray($path);
            Ac_Util::setArrayByPath($res, $path, $value, true);
        }
        return $res;
    }
    
    function isParamUsed($path, $includeSubPaths = false) {
        if ($includeSubPaths) {
            $l = strlen($hash = $this->getPathHash($path));
            foreach (array_keys($this->usedParams) as $k) {
                if (!strncmp($k, $hash, $l)) {
                    $res = true;
                    break;
                }
            }
        } else {
            $res = isset($this->usedParams[$this->getPathHash($path)]);
        }
        return $res;
    }
    
    function forgetParam($path, $includeSubPaths = false) {
        if ($includeSubPaths) {
            $l = strlen($hash = $this->getPathHash($path));
            foreach (array_keys($this->usedParams) as $k)
                if (!strncmp($k, $hash, $l)) {
                    unset($this->usedParams[$k]);
                }
        } else {
            unset($this->usedParams[$this->getPathHash($path)]);
        }
    }
    
    function forgetAllParams() {
        $this->usedParams = array();
    }

    protected function updateUrlWithUsedParams(Ac_Url $url) {
        foreach ($this->usedParams as $hash => $data) {
            list($path, $value, $found) = $data;
            $translatedPath = $this->translatePath($path);
            Ac_Util::setArrayByPath($url->query, $translatedPath, $value, true);
        }
    }
    
    // TODO: should we fully overwrite params (as it is now) or merge them?
    protected function updateUrlWithParams(Ac_Url $url, array $params) {
        if ($this->pathPrefix) {
            $tmp = array();
            Ac_Util::setArrayByPath($tmp, $this->pathPrefix, $params);
        } else {
            $tmp = $params;
        }
        Ac_Util::ms($url->query, $params);
    }
    
    protected function getPathHash($path) {
        return is_array($path)? Ac_Util::arrayToPath($path) : ''.$path;
    }
    
    /**
     * @return Ac_Cr_Url 
     * @param bool $fullOverride Don't put used params to the context
     */
    function createUrl(array $params = array(), $fullOverride = false) {
        if (!$this->topContext) {
            $res = $this->getBaseUrl()->cloneObject();
        } else {
            $res = $this->topContext->createUrl(array(), $fullOverride);
        }
        if ($r = $this->getRouter()) $res->setRouter($r);
        if (!$fullOverride) $this->updateUrlWithUsedParams($res);
        if ($params) $this->updateUrlWithParams ($res, $params);
        return $res;
    }
    
    function mapJsId($paramName) {
        $a = Ac_Util::pathToArray($this->translatePath($paramName));
        foreach ($a as & $v) $v = ucfirst($v);
        return 'v'.implode('', $a);
    }
    
    function mapHtmlParam($paramName) {
        $res = Ac_Util::arrayToPath($this->translatePath($paramName));
        return $res;
    }
    
    function mapHtmlId($paramName) {
        $a = Ac_Util::pathToArray($this->translatePath($paramName));
        return 'e_'.implode('_', $a);
    }
    
    /**
     * @param string $pathPrefix 
     * @return Ac_Cr_Context
     */
    function createSubContext($pathPrefix = false) {
        $res = new Ac_Cr_Context;
        $res->setTopContext($this, $pathPrefix);
        return $res;
    }
    
    function setTopContext(Ac_Cr_Context $context, $pathPrefix = false) {
        if ($this->topContext) throw Ac_E_InvalidCall::canRunMethodOnce ($this, __FUNCTION__);
        if ($pathPrefix !== false) {
            if ($this->pathPrefix !== $pathPrefix) $this->setPathPrefix($pathPrefix);
        }
        $this->topContext = $context;
    }
    
    function getTopContext() {
        return $this->topContext;
    }
    
}
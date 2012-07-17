<?php

class Ac_Cr_Context {
    
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
     * @var Ac_Url
     */
    protected $baseUrl = false;
    
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
        if ($this->baseUrl === false) $this->baseUrl = Ac_Cr_Url::guess();
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
     * @return Ac_Cr_Reques
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
            else $res = $ownParamPath;
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
    
    protected function updateUrlWithUsedParams(Ac_Url $url) {
        foreach ($this->usedParams as $hash => $data) {
            list($path, $value, $found) = $data;
            $translatedPath = $this->translatePath($path);
            Ac_Util::setArrayByPath($url->query, $translatedPath, $value, true);
        }
    }
    
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
        return md5(serialize($path));
    }
    
    /**
     * @return Ac_Cr_Url 
     */
    function createUrl(array $params = array()) {
        $res = $this->getBaseUrl()->cloneObject();
        if ($r = $this->getRouter()) $res->setRouter($r);
        $this->updateUrlWithUsedParams($res);
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
    
}
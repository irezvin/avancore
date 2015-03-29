<?php

/**
 * Updated Ac_Legacy_Controller with best things taken from my recent projects.
 * Has JSON support, method chaining, output capture, exception handling and caching.
 */
class Ac_Legacy_Controller_Std_Web extends Ac_Legacy_Controller {

    // --------- cache support ---------

    var $cacheDebug = false;
    
    var $cacheSkip = false;
    
    var $cacheGroup = false;

    /**
     * @var false|true|string|array FALSE not to use Ac_Cache; TRUE to use Ac_Cache with default prototype; string - class of custom Ac_Cache implemetation; array with prototype 
     */
    var $aeCache = false;
    
    // Caching parameters
    /**
    * @var array|bool List of web methods that can be cached (or true means all methods can be cached)
    */
    var $cacheableMethods = array();

    /**
     * List of general and specific parameters that are used to compos
     * array('param1', 'param2', 'methodName' => array('param3', 'param4'), 'method2Name' => array('param5')
     * @var array
     */
    var $cacheParamsMap = array();

    var $loadedFromCache = false;

    /**
     * @var array thisPropertyNameToGet => thisVarNameToSet, methodName => array(thisPropertyNameToGet => thisVarNameToSet...)
     */
    var $cacheDataMap = array();

    // --------- json, response chaining & output capture support ---------

    var $isJson = false;

    var $forceJson = false;
    
    /**
     * @var Ac_Legacy_Controller_Response_Html
     */
    var $_response = false;

    var $_outputToResponse = true;

    /**
     * We don't have default method (to see 'not found' messages)
     */
    var $_defaultMethodName = false;

    /**
     * List of response objects that are added with pushResponse() - for multiple response support
     * @var array
     */
    protected $responses = array();

    protected function collectProperties(array $list, $forCache = false) {
        $res = array();
        foreach ($list as $propName) {
            if (is_array($propName)) {
                $val = call_user_func_array(array($this, $propName[0]), array_slice($propName, 1));
                $propName = implode(".", $propName);
            } else {
                if (method_exists($this, $m = 'get'.$propName)) $val = $this->$m();
                else $val = $this->$propName;
            }
            if ($forCache && $val instanceof Ac_Model_Object) $val = $val->getDataFields();
            $res[$propName] = $val;
        }
        return $res;
    }

    function getCacheId(array $extra = array(), $asArray = false) {
        $res = false;
        $m = $this->getMethodName();
        if (($this->cacheableMethods === true) || is_array($this->cacheableMethods) && in_array($m, $this->cacheableMethods)) {
            if ($this->cacheDebug) Ac_Debug::fb($m, 'method cache enabled');
            $cacheParamsList = array();
            foreach ($this->cacheParamsMap as $k => $v) {
                if (is_numeric($k)) $cacheParamsList[] = $v;
                elseif (is_array($v) && ($k == $m)) $cacheParamsList = array_merge($cacheParamsList, $v);
            }
            // params list received - now get params values
            $res = array_merge($extra, $this->collectProperties($cacheParamsList, true));
            $app = $this->getApplication();
            $adapter = $app->getAdapter();
            if (method_exists($adapter, 'getLiveSite')) {
                $res['_liveSite'] = $adapter->getLiveSite();
            }
            $res['_method'] = get_class($this).'::'.$m;
            if (!$asArray) $res = md5(serialize($res));
        } else {
            if ($this->cacheDebug) Ac_Debug::fb($m, ' non-cacheable method');
        }
        return $res;
    }

    function getCacheDataMap() {
        $res = array();
        $m = $this->getMethodName();
        foreach ($this->cacheDataMap as $k => $v) {
            if (is_string($v)) {
                if (is_numeric($k)) $k = $v;
                $res[$k] = $v;
            } elseif (is_array($v) && $k == $m) {
                foreach ($v as $k1 => $v1) {
                    if (is_numeric($k1)) $k1 = $v1;
                    $res[$k1] = $v1;
                }
            }
        }
        if (!$res) $res = array('_response' => '_response');
        return $res;
    }

    protected function doLoadFromCache($id, $cacheGroup) {
        $val = false;
        if ($c = $this->getCache()) {
            $val = $c->get($id, $cacheGroup);
        }
        return $val;
    }
    
    protected function doSaveToCache($id, $cacheGroup, $content) {
        if ($c = $this->getCache()) {
            $res = $c->put($id, $content, $this->getCacheGroup($this->getMethodName()));
        }
        return $res;
    }
    
    protected function loadFromCache() {
        $res = false;
        if (strlen($id = $this->getCacheId())) {
            $val = $this->doLoadFromCache($id, $this->getCacheGroup($this->getMethodName()));
            if (strlen($val)) $val = unserialize($val);
            if (is_array($val)) {
                
                $res = true;
                
                // This allows us to add 'obsolete validators' to response
                
                if (isset($val['_response']) && $val['_response'] instanceof Ac_Legacy_Controller_Response) {
                    if ($val['_response']->isObsolete()) {
                        $res = false;
                    }
                }
                
                if ($res) {
                    foreach($val as $k => $v) {
                        // we set _internal_ properties from the cache
                        $this->$k = $v;
                    }
                }
            }
        }
        if ($id !== false) {
            if ($this->cacheDebug) Ac_Debug::fb(array('id' => $id, 'src ' => $this->getCacheId(array(), true)), "Page cache ".($res? 'hit' : 'miss'));
        }
        $this->loadedFromCache = $res;
        return $res;
    }

    protected function saveToCache() {
        $res = false;
        if (!$this->loadedFromCache) {
            if (strlen($id = $this->getCacheId())) {
                $cdm = $this->getCacheDataMap();
                $pv = $this->collectProperties(array_keys($cdm), true);
                $res = array();
                foreach ($pv as $k => $v) {
                    $res[$cdm[$k]] = $v;
                }
                if ($this->cacheDebug) Ac_Debug::fb($res, "Save to cache");
                $val = serialize($res);
                $this->doSaveToCache($id, $this->getCacheGroup($this->getMethodName()), $val);
            }
        }
    }

    function getCacheGroup($methodName = false) {
        $res = strlen($this->cacheGroup)? $this->cacheGroup : get_class($this);
        if (strlen($methodName)) $res .= '_'.$methodName;
        return $res;
    }


    /**
     * This function is always executed disregarding to caching
     */
    function doOnResponseStart() {
        $this->bindFromRequest();
    }
    
    function getCacheEnabled() {
        return true;
    }

    /**
     * @return Ac_Legacy_Controller_Response_Html
     */
    function getResponse($methodName = false) {
        if ($methodName !== false) $this->_methodName = $methodName;
        $this->doOnResponseStart();
        if (!$this->cacheSkip && $this->getCacheEnabled()) {
            $this->loadFromCache();
            if ($this->cacheDebug) Ac_Debug::fb($this->loadedFromCache .'/'.(is_object($this->_response)? get_class($this->_response) : gettype($this->_response)), $this->getMethodName().'::loadedFromCache');
        }
        else {
            if ($this->cacheDebug) Ac_Debug::fb($methodName, 'cacheSkip');
        }
        try {
             
            if ($this->_response === false) {
                if ($this->isJson || $this->forceJson) {
                    $this->_defaultResponseClass = 'Ac_Legacy_Controller_Response_Json';
                }
                parent::getResponse();
                if ($this->isJson || $this->forceJson) {
                    $resp = $this->_response;
                    $res = new Ac_Legacy_Controller_Response_Json();
                    foreach (array_merge($this->responses, array($resp)) as $response) {
                        $res->setInnerResponse($response, true);
                    }
                    $this->_response = $res;
                }
            }
            if (!$this->cacheSkip && $this->getCacheEnabled()) $this->saveToCache();
        } catch (Ac_Legacy_Controller_Exception $e) {
            $res = $this->handleException($e);
        }

        if ($this->_response) $res = $this->_response;
        else $res = false;
         
        return $res;
    }
    
    function handleException($e) {
        $this->_tplData['exception'] = $e;
        $this->_response = false;
        $res = parent::getResponse('executeError');
        return $res;
    }

    function executeError() {
        $this->_response->addExtraHeader('HTTP/1.0 404 Not Found', 404);
        //header('HTTP/1.1 500 Internal Server Error');
        $this->cacheSkip = true;
        $this->_templatePart = 'error';
    }

    function execFallback($methodParamValue = null) {
        $this->isJson = false;
        $this->_response->addExtraHeader('HTTP/1.0 404 Not Found', 404);
        $this->_response->addExtraHeader('Status: 404 Not Found');
        $this->cacheSkip = true;
        $this->_response->content = "No such method: '{$methodParamValue}' :(";
    }

    /**
     * @param bool|string $createNewResponse
     *          - TRUE to create new response of default class;
     *          - FALSE to empty response object (useful if next call will be getResponse());
     *          - name of class to specify class of next response
     *
     * Multiple response support:
     * - pushes current response to the response list;
     * - creates blank response object and sets it current - if $createNewResponse isn't false
     */
    function pushResponse($createNewResponse = true, $fetchTemplate = true) {
        if ($fetchTemplate && $this->_response->content === false) {
            $this->getTemplate();
            if (strlen($this->_templatePart) && $this->_template) {
                $this->_response->content .= $this->_template->fetch($this->_templatePart);
            }
        }
        $this->responses[] = $this->_response;
        $this->_response = false;
        if ($this->_template) $this->_template = false;
        if ($createNewResponse !== false) {
            if (is_bool($createNewResponse)) $createNewResponse = $this->getResponseClass();
            $this->_response = new $createNewResponse();
        }
    }
    
    /**
     * @return Ac_Cache
     */
    function getCache() {
        if (!is_object($this->aeCache)) {
            if ($this->aeCache !== false) {
                if ($this->aeCache === true) $this->aeCache = $this->getApplication()->getCache();
                elseif (is_array($this->aeCache)) $this->aeCache = Ac_Util::m(Ac_Cache::getDefaultPrototype(), $this->aeCache);
                $this->aeCache = Ac_Prototyped::factory($this->aeCache, 'Ac_Cache');
            }
        }
        return $this->aeCache;
    }
    


}

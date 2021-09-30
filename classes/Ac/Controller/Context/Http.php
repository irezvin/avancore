<?php

class Ac_Controller_Context_Http extends Ac_Controller_Context {
    
    /**
     * Whether controller's output is intended to be placed in the form and controller should not place <form>...</form> tags into it
     */
    var $isInForm = false;
    
    /**
     * Request method
     *
     * @var string get|post|put|delete...
     */
    var $requestMethod = "post";

    /**
     * Name of request variable that holds state
     */
    var $stateVarName = '_state_';
    
    /**
     * @var Ac_Form
     */
    var $form = false;
    
    var $pathInfo = false;
    
    /**
     * @var Ac_Url
     */
    var $_baseUrl = false;
    
    var $_url = false;
    
    /**
     * @var string
     */
    var $_dataPath = '';
    
    /**
     * @var array
     */
    var $_arrDataPath = array();
    
    /**
     * @var string 'get'|'post'|'cookie'|'request'
     */
    var $autoVariable = false;
    
    protected $usedParams = [];
    
    function doAfterSetData() {
        $this->_url = false;
        if (!$this->stateIsExternal && strlen($this->stateVarName) && isset($this->_data[$this->stateVarName]) && is_array($this->_data[$this->stateVarName])) {
            $this->setState($this->_data[$this->stateVarName]);
            unset($this->_data[$this->stateVarName]);
        }
    }

    /**
     * @param string|array $dataPath
     */
    function setDataPath ($dataPath) {
        $this->_url = false;
        if (!is_array($dataPath)) {
            $this->_dataPath = $dataPath;
            $this->_arrDataPath = Ac_Util::pathToArray($dataPath);
        } else {
            $this->_arrDataPath = $dataPath;
            $this->_dataPath = Ac_Util::arrayToPath($dataPath);
        }
    }
    
    /**
     * @param bool $asArray
     * @return string|array
     */
    function getDataPath($asArray = false) {
        return $asArray? $this->_arrDataPath : $this->_dataPath;
    }

    function populate($varName = null, $dataPath = false, $url = null, $scriptName = null) {
        if (is_null($varName)) $varName = ['get', 'post'];
        else $varName = Ac_Util::toArray($varName);
        $src = array();
        $getVars = null;
        if (!is_null($url)) {
            $url = new Ac_Url($url);
            $getVars = $url->query;
            if ($url->pathInfo) $this->pathInfo = $url->pathInfo;
        }
        if ($scriptName === true) {
            if (isset($_SERVER['SCRIPT_NAME'])) $scriptName = $_SERVER['SCRIPT_NAME'];
            else $scriptName = null;
        }
        if (!$url) $requestUri = Ac_Url::guess();
        else $requestUri = $url;
        $baseUrl = clone($requestUri);
        $resultPath = null;
        if ($scriptName) {
            $this->pathInfo = Ac_Url::guessPathInfo('/'.ltrim($requestUri->path, '/'), $scriptName, $resultPath);
        }
        if (!$this->_baseUrl) {
            if (!is_null($resultPath)) {
                $baseUrl->path = $resultPath;
            }
            $baseUrl->query = [];
            $this->setBaseUrl($baseUrl);
        }
        foreach ($varName as $v) {
            switch($v) {
                case 'get': $s = isset($getVars)? $getVars : $_GET; break;
                case 'post': $s = $_POST; break;
                case 'cookie': $s = $_COOKIE; break;
                case 'request': $s = $_REQUEST; break;
                default: trigger_error ("Unknown request variable '$varName'; use 'get', 'post', 'cookie' or 'request'", E_USER_ERROR);
            }
            $src = Ac_Util::m($src, $s);
        }
        $this->setDataPath($dataPath);
        $data = Ac_Util::getArrayByPath($src, $this->_arrDataPath, array());
        if (ini_get('magic_quotes_gpc')) $data = Ac_Util::stripSlashes($data);
        $this->setData($data);
        if (isset($_SERVER) && isset($_SERVER['REQUEST_METHOD'])) {
            $this->requestMethod = strtolower($_SERVER['REQUEST_METHOD']);
        }
    }
    
    /**
     * Creates new context as a subset of current one
     * @param string|array $subPath Path to subcontext
     * @param string $subClass Class of the new context
     * @return Ac_Controller_Context 
     */
    function spawn($subPath, $subClass = false, $paramsToUrl = array()) {
        if ($subClass === false) $subClass = Ac_Util::fixClassName(get_class($this));
        $res = new $subClass;
        $res->assign($this);
        if (strlen($this->pathInfo) && !strncmp($res->_baseUrl->pathInfo, $this->pathInfo, strlen($this->pathInfo))) {
            $res->_baseUrl->path .= $this->pathInfo;
            $res->_baseUrl->pathInfo = substr($res->_baseUrl->pathInfo, strlen($this->pathInfo));
            $res->pathInfo = '';
        }
        if ($paramsToUrl) {
            $queryOverrides = array();
            foreach($paramsToUrl as $k => $v) {
                if (is_numeric($k)) {
                    $k = $v;
                    $v = $this->getData($k, null);
                    if (!is_null($v)) Ac_Util::setArrayByPath ($queryOverrides, $k, $v);
                } else {
                    $v = array($k => $v);
                    Ac_Util::ms($queryOverrides, $v);
                }
            }
            if ($queryOverrides) {
                $tmp = array();
                Ac_Util::setArrayByPath($tmp, $this->getDataPath(true), $queryOverrides);
                Ac_Util::ms($res->_baseUrl->query, $tmp);
            }
        }
        $arrSubPath  = is_array($subPath)? $subPath : Ac_Util::pathToArray($subPath); 
        if (!is_array($this->_arrDataPath)) {
            $this->_arrDataPath = Ac_Util::pathToArray($this->_dataPath);
            if (!is_array($this->_arrDataPath)) $this->_arrDataPath = array();
        }
        $res->setDataPath(array_merge($this->_arrDataPath, $arrSubPath));
        $d = $this->getData();
        $s = $this->getState();
        $subData = Ac_Util::getArrayByPath($d, $arrSubPath, array());
        $subState = Ac_Util::getArrayByPath($s, $arrSubPath, array());
        $res->setData($subData);
        $res->setState($subState);
        return $res;
    }
    
    function loadPathInfoSegments($separator = '/', $count = 1) {
        if (substr($this->_baseUrl->pathInfo, 0, 1) == $separator) {
            $count += 1;
        } 
        $u = explode($separator, $this->_baseUrl->pathInfo, $count + 1);
        $this->pathInfo = implode($separator, array_slice($u, 0, $count));
        return $this->pathInfo;
    }
    
    /**
     * @param Ac_Url $url
     */
    function setBaseUrl($url) {
        if (!(is_object($url) && $url instanceof Ac_Url)) {
            $url = new Ac_Url(''.$url);
        }
        $this->_baseUrl = $url;
        $this->_url = false;
    }

    /**
     * @return Ac_Url
     */
    function getBaseUrl() {
        return $this->_baseUrl;
    }
    
    /**
     * @return Ac_Url
     */
    function getUrl($extraParams = array(), $withData = true) {
        if (!is_object($this->_baseUrl)) {
            throw new Ac_E_InvalidUsage ("Base URL is not set - call setBaseUrl() first");
        }
        $res = $this->_baseUrl->cloneObject();
        if (strlen($this->pathInfo)) $res->pathInfo .= $this->pathInfo;
        if ($withData) $data = $this->_data;
        Ac_Util::ms($data, $extraParams);
        if ($withData && $this->_state && $this->stateVarName && !$this->stateIsExternal) $data[$this->stateVarName] = $this->_state;
        $d = array();
        Ac_Util::setArrayByPath($d, $this->_arrDataPath, $data);
        Ac_Util::ms($res->query, $d, true);
        return $res;
    }
    
    function getStringUrl() {
        $u = $this->getUrl();
        $res = $u->toString();
        return $res;
    }
    
    function toString() {
        return $this->getStrUrl();
    }
    
    /**
     * @return Ac_Controller_Context_Http
     */
    function cloneObject() {
        $res = parent::cloneObject();
        return $res;
    }
    
    function updateData($values = array(), $byPath = false) {
        if ($byPath) {
            $v = array();
            Ac_Util::setArrayByPath($v, $this->_arrDataPath, $values);
            $values = $v;
        }
        return parent::updateData($values);
    }
    
    function useParam($path, $defaultValue = null, & $found = false) {
        $hash = is_array($path)? Ac_Util::arrayToPath($path) : $path;
        $res = $this->getData($path, $defaultValue, $found);
        $this->_usedParams[$hash] = ['path' => Ac_Util::pathToArray($path), 'value' => $res, 'found' => $found];
        $globalPath = $this->mapParam($path, true);
        if ($found) Ac_Util::setArrayByPath($this->_baseUrl->query, $globalPath, $res);
        return $res;
    }
    
}


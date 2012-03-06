<?php

class Ae_Legacy_Controller_Context_Http extends Ae_Legacy_Controller_Context {
    
    /**
     * Whether controller's output is intended to be placed in the form and controller should not place <form>...</form> tags into it
     */
    var $isInForm = false;
    
    /**
     * Preferred request method
     *
     * @var string get|post|put|delete...
     */
    var $requestMethod = "post";

    /**
     * Name of request variable that holds state
     */
    var $stateVarName = '_state_';
    
    /**
     * @var Ae_Form
     */
    var $form = false;
    
    var $pathInfo = false;
    
    /**
     * @var Ae_Url
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
    
    function doAfterSetData() {
        $this->_url = false;
        if (!$this->stateIsExternal && strlen($this->stateVarName) && isset($this->_data[$this->stateVarName]) && is_array($this->_data[$this->stateVarName])) {
            $this->setState($this->_data[$this->stateVarName]);
            unset($this->_data[$this->stateVarName]);
        }
    }

    function initialize($options) {
        parent::initialize($options);
        if (isset($options['baseUrl'])) {
            if (is_string($options['baseUrl']) && strlen($options['baseUrl'])) {
                $url = new Ae_Url($options['baseUrl']);
                $this->setBaseUrl($url);
            } 
            elseif (is_a($options['baseUrl'], 'Ae_Url')) {
                $this->setBaseUrl($options['baseUrl']);
            }
        }
        Ae_Util::simpleBindAll($options, $this, array('stateVarName', 'isInForm', 'form'));
    }
    
    /**
     * @param string|array $dataPath
     */
    function setDataPath ($dataPath) {
        $this->_url = false;
        if (!is_array($dataPath)) {
            $this->_dataPath = $dataPath;
            $this->_arrDataPath = Ae_Util::pathToArray($dataPath);
        } else {
            $this->_arrDataPath = $dataPath;
            $this->_dataPath = Ae_Util::arrayToPath($dataPath);
        }
    }
    
    /**
     * @param bool $asArray
     * @return string|array
     */
    function getDataPath($asArray = false) {
        return $asArray? $this->_arrDataPath : $this->_dataPath;
    }
    
    function populate($varName, $dataPath = false) {
        if (is_array($varName)) {
            $src = array();
            foreach ($varName as $v) {
                switch($v) {
                    case 'get': $s = & $_GET; break;
                    case 'post': $s = & $_POST; break;
                    case 'cookie': $s = & $_COOKIE; break;
                    case 'request': $s = & $_REQUEST; break;
                }
                $src = Ae_Util::m($src, $s);
            }
        } else {
            switch ($varName) {
                case 'get': $src = & $_GET; break;
                case 'post': $src = & $_POST; break;
                case 'cookie': $src = & $_COOKIE; break;
                case 'request': $src = & $_REQUEST; break;
                default: trigger_error ("Unknown request variable '$varName'; use 'get', 'post', 'cookie' or 'request'", E_USER_ERROR);
            }
        }
        $this->setDataPath($dataPath);
        $data = Ae_Util::getArrayByPath($src, $this->_arrDataPath, array());
        if (get_magic_quotes_gpc()) $data = Ae_Util::stripSlashes($data);
        $this->setData($data);
    }
    
    /**
     * Creates new context as a subset of current one
     * @param string|array $subPath Path to subcontext
     * @param string $subClass Class of the new context
     * @return Ae_Legacy_Controller_Context 
     */
    function & spawn($subPath, $subClass = false, $paramsToUrl = array()) {
        if ($subClass === false) $subClass = Ae_Util::fixClassName(get_class($this));
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
                    if (!is_null($v)) Ae_Util::setArrayByPath ($queryOverrides, $k, $v);
                } else {
                    $v = array($k => $v);
                    Ae_Util::ms($queryOverrides, $v);
                }
            }
            if ($queryOverrides) {
                $tmp = array();
                Ae_Util::setArrayByPath($tmp, $this->getDataPath(true), $queryOverrides);
                Ae_Util::ms($res->_baseUrl->query, $tmp);
            }
        }
        $arrSubPath  = is_array($subPath)? $subPath : Ae_Util::pathToArray($subPath); 
        if (!is_array($this->_arrDataPath)) {
            $this->_arrDataPath = Ae_Util::pathToArray($this->_dataPath);
            if (!is_array($this->_arrDataPath)) $this->_arrDataPath = array();
        }
        $res->setDataPath(array_merge($this->_arrDataPath, $arrSubPath));
        $d = $this->getData();
        $s = $this->getState();
        $subData = Ae_Util::getArrayByPath($d, $arrSubPath, array());
        $subState = Ae_Util::getArrayByPath($s, $arrSubPath, array());
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
     * @param Ae_Url $url
     */
    function setBaseUrl(& $url) {
        $this->_baseUrl = & $url;
        $this->_url = false;
    }
    
    /**
     * @return Ae_Url
     */
    function getUrl($extraParams = array(), $withData = true) {
        if (!is_object($this->_baseUrl)) {
            trigger_error ("Base Url is not set - call setBaseUrl() first", E_USER_ERROR);
        }
//        if ($this->_url === false) {
//            $this->_url = & $this->_baseUrl->cloneObject();
//            $data = $this->_data;
//            Ae_Util::ms($data, $extraParams);
//            if ($this->_state && $this->stateVarName && !$this->stateIsExternal) $data[$this->stateVarName] = $this->_state;
//            $d = array();
//            Ae_Util::setArrayByPath($d, $this->_arrDataPath, $data);
//            Ae_Util::ms($this->_url->query, $d, true);
//        }
//        return $this->_url;

        $res = & $this->_baseUrl->cloneObject();
        if (strlen($this->pathInfo)) $res->pathInfo .= $this->pathInfo;
        if ($withData) $data = $this->_data;
        Ae_Util::ms($data, $extraParams);
        if ($withData && $this->_state && $this->stateVarName && !$this->stateIsExternal) $data[$this->stateVarName] = $this->_state;
        $d = array();
        Ae_Util::setArrayByPath($d, $this->_arrDataPath, $data);
        Ae_Util::ms($res->query, $d, true);
        return $res;
        
    }
    
    function getStringUrl() {
        $u = & $this->getUrl();
        $res = & $u->toString();
        return $res;
    }
    
    function toString() {
        return $this->getStrUrl();
    }
    
    /**
     * @return Ae_Legacy_Controller_Context_Http
     */
    function & cloneObject() {
        $res = & parent::cloneObject();
        return $res;
    }
    
    function updateData($values = array(), $byPath = false) {
        if ($byPath) {
            $v = array();
            Ae_Util::setArrayByPath($v, $this->_arrDataPath, $values);
            $values = $v;
        }
        return parent::updateData($values);
    }
    
}

?>
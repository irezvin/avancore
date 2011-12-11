<?php

class Ae_Controller_Context {

    /**
     * This request is intended to retrieve direct response from the terminal controller of processing chain.
     * This parameter isn't mandatory for the controllers that process this request!
     *
     * @var bool
     */
    var $directRequest = false;
    
    /**
     * Whether state of the controller is stored by the environment
     * @var bool
     */
    var $stateIsExternal = false;
    
    /**
     * @var array
     */
    var $_data = array();
    
    var $_state = array();
    
    function Ae_Controller_Context($options = array()) {
        if (!is_array($options)) trigger_error("\$options must be an array", E_USER_ERROR);
        $this->initialize($options);
    }
    
    /**
     * @access protected
     */
    function initialize($options) {
        if (isset($options['directRequest'])) $this->direct = $options['direct'];
        if (isset($options['stateIsManaged'])) $this->stateIsManaged = $options['stateIsManaged'];
        if (isset($options['data'])) $this->setData($options['data']);
        if (isset($options['state'])) $this->setState($options['state']);
    }
    
    /**
     * @return array
     */
    function getData($path = false, $defaultValue = false) {
        if ($path === false) $res = $this->_data;
        else {
            if (!is_array($path)) $path = Ae_Util::pathToArray($path);
            $res = Ae_Util::getArrayByPath($this->_data, $path, $defaultValue);
        }
        return $res;
    }
    
    function getManyValues(array $paths, $defaultValue = false) {
        $res = array();
        foreach ($paths as $path) {
            if (!is_array($path)) $p = Ae_Util::pathToArray($path);
                else $p = $path;
            $val = $this->getData($p, $defaultValue);
            if (!is_array($path)) $res[$path] = $val;
                else Ae_Util::simpleSetArrayByPath ($res, $path, $val);
        }
        return $res;
    }
    
    function updateData($values = array()) {
        Ae_Util::ms($this->_data, $values);
        $this->doAfterSetData();
    }
    
    function setData($data = array()) {
        $this->_data = $data;
        $this->doAfterSetData();
    }
    
    function doAfterSetData() {
    }
    
    function getState() {
        return $this->_state;
    }
    
    function setState($state = array()) {
        $this->_state = $state;
        $this->doAfterSetState();
    }
    
    function setStateVariable($path, $value) {
        if (!strlen($path) || !count($path)) trigger_error('$path must be specified', E_USER_ERROR);
        if (!is_array($path)) $path = Ae_Util::pathToArray($path);
        Ae_Util::setArrayByPath($this->_state, $path, $value, true);
        $this->doAfterSetState();
    }
    
    function unsetStateVariable($path, $value) {
        if (!strlen($path) || !count($path)) trigger_error('$path must be specified', E_USER_ERROR);
        if (!is_array($path)) $path = Ae_Util::pathToArray($path);
        Ae_Util::unsetArrayByPath($this->_state, $path, $value, true);
        $this->doAfterSetState();
    }
    
    function getStateVariable($path, $default = false) {
        if (!strlen($path) || !count($path)) trigger_error('$path must be specified', E_USER_ERROR);
        $res = Ae_Util::getArrayByPath($this->_state, $value, $default);
        return $res;
    }
    
    function doAfterSetState() {
    }
    
    /**
     * @return Ae_Controller_Context
     */
    function & cloneObject() {
        $className = Ae_Util::fixClassName(get_class($this));
        Ae_Dispatcher::loadClass($className);
        $res = new $className;
        $res->assign($this);
        return $res;
    }
    
    function getCopyExclude() {
        return array();
    }
    
    function doAfterCopy() {
    }
    
    function assign(& $otherContext) {
        $options = array_diff(array_keys(get_object_vars($otherContext)), $this->getCopyExclude());
        foreach ($options as $o) {
            if (is_object($otherContext->$o) && is_callable(array($otherContext->$o, 'cloneObject'))) $this->$o = & $otherContext->$o->cloneObject(); 
                else $this->$o = $otherContext->$o;
        }
        $this->doAfterCopy();
    }
    
    function mapParam($paramPath, $asArray = false) {
        if (!is_array($paramPath)) $paramPath = Ae_Util::pathToArray($paramPath);
        $resPath = array_merge($this->_arrDataPath, $paramPath);
        if (!$asArray) $resPath = Ae_Util::arrayToPath($resPath);
        return $resPath;
    }
    
    function mapIdentifier($identifier) {
        if (count($this->_arrDataPath)) $res = implode("_", array_merge($this->_arrDataPath, strlen($identifier)? array($identifier) : array()));
            else $res = $identifier;
        return $res;
    }
    
}
?>

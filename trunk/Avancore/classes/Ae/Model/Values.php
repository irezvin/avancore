<?php

/**
 * Provides methods to work with lists of values. This feature is tightly used with Ae_Model_Data properties that have limited sets 
 * of possible values with captions (enum-like or set-like ones, especially foreign keys), but can also be used without bound Model. 
 */
class Ae_Model_Values {
    
    /**
     * Data source (is used in self-callbacks - when first param in callbacks is 'true').
     * Can be null or false (but self-callbacks are prohibited).
     * 
     * @var Ae_Model_Data  
     */
    var $data = false;
    
    /**
     * Name of data property that is described by this list (used to be second parameter to callbacks).
     * Can be empty string.
     * 
     * @var string
     */
    var $propName = false;
    
    /**
     * Whether same list of values can be used for all models with same class. Is used only when $this->data is set (all no-data value lists are considered static).
     *
     * @var bool 
     */
    var $isStatic = false;
    
    /**
     * Whether list of values depends on values of other model properties (is primarily used to cache list or in presentational layers to reload forms when some
     * properties are changed). FALSE means no dependencies at all, TRUE means that any property can change values list, and ARRAY with list of property names means
     * that only several properties affect list contents. 
     *  
     * @param bool|array  
     */
    var $depends = false;
    
    /**
     * Callback function to return whole list (array $value => $caption).
     * Should accept parameters Ae_Model_Values 
     * 
     * Special callback format: 
     * - a) regular callback
     * - b) array(true, $methodName) - will call $this->_data->$methodName
     * - c) array(true, $methodName, $arg1, $arg2...) - extra arguments to pass method
     * - d) array(false,$funcName, $arg1, $arg2...) - extra arguments to pass to global function
     * - e) array(className, $methodName, etc...) - extra arguments to pass to callback function
     *
     * @var string|array
     */
    var $getList = false;
    
    /**
     * Callback function to return caption for the list value or FALSE if such value is invalid.
     * Should accept parameters Ae_Model_Values, $value. 
     */
    var $getCaption = false;
    
    /**
     * Callback function to check if such value is allowed. Should accept parameters Ae_Model_Values, $value.
     */
    var $checkValue = false;
    
    /**
     * Callback function to retrieve details for specified value (for example, if it's a foreign key, corresponding database object can be returned)
     */
    var $getDetails = false;
    
    /**
     * Callback function to retrieve details for all values
     */
    var $getAllDetails = false;
    
    /**
     * True if getDetailsCallback() returns details by reference (not by value). Is useful only with self- or global callbacks since call_user_func_array() does not allow
     * to pass result by reference.
     * 
     * @var bool 
     */
    var $returnDetailsByRef = true;
    
    /**
     * If model getter was called to retrieve valueList or all details, cache its result in $valueList or $valueDetails for further use. 
     * Note: if list is staic, setting this property to true makes cached values to be preserved if setData() provides $data of same class as current $this->data.    
     * 
     * @var bool 
     */
    var $cache = true;
    
    /**
     * Simple list of values with their captions
     * @var array ($value => $caption) 
     */
    var $valueList = false;
    
    /**
     * Whether provide reference to & $this as first parameter to all getters
     * @var bool
     */
    var $provideSelfToGetters = true;
    
    var $_cachedValueList = false;
    
    /**
     * Simple list of details for values
     * @var array ($value => & $detail)
     */
    var $valueDetails = false;
    
    var $_cachedValueDetails = false;
    
    /**
     * @return Ae_Model_Values
     */
    function & factoryIndependent($options = array()) {
        $data = null;
        $propName = false;
        $isStatic = true;
        if (isset($options['class']) && $options['class']) {
            $class = $options['class'];
        } else $class = 'Ae_Model_Values';
        $res = new $class($data, $propName, $options, $isStatic);
        if (!is_a($res, 'Ae_Model_Values')) trigger_error ("Class '{$class}' is not derived from Ae_Model_Values", E_USER_ERROR);
        return $res; 
    }
    
    /**
     * Creates Ae_Model_Values using $formOptions. 
     * Class of created instance will be determined by $formOptions['values']['class'], if given.
     * 
     * @param Ae_Model_Data $data
     * @param string $propName name of model property
     * @param bool|array $formOptions Options of that property. If true, they will be retrieved automatically  
     * @static
     * 
     * @return Ae_Model_Values
     */
    function & factoryWithFormOptions (& $data, $propName, $formOptions = true, $isStatic = false) {
        $class = 'Ae_Model_Values';
        if (!is_array($formOptions)) $formOptions = & $data->getFormOptions($propName, $isStatic);
        if (isset($formOptions['values']) && is_array($formOptions['values'])) $options = $formOptions['values'];
        if (!isset($options['valueList']) && isset($formOptions['valueList'])) $options['valueList'] = $formOptions['valueList'];
        if (isset($options['class']) && $options['class']) {
            $class = $options['class'];
        }
        $res = new $class($data, $propName, $options, $isStatic);
        if (!is_a($res, 'Ae_Model_Values')) trigger_error ("Class '{$class}' is not derived from Ae_Model_Values", E_USER_ERROR);
        return $res; 
    }
    
    /**
     * Creates Ae_Model_Values using meta-property object. Concrete class can be specified by $prop->values['class'].  
     * 
     * @param Ae_Model_Property $prop
     * @return Ae_Model_Values
     */
    function & factoryWithProperty (& $prop) {
        $propName = $prop->propName;
        $data = & $prop->srcObject;
        $formOptions = $prop->toFormOptions();
        $isStatic = $prop->isStatic;
        $res = & Ae_Model_Values::factoryWithFormOptions($data, $propName, $formOptions, $isStatic);
        return $res;
    }
    
    /**
     * @param Ae_Model_Data $data
     * @param string $propName name of model property
     * @param bool|array $formOptions Options of list. If non-array and valid Ae_Model_Data - propName pair is provided, they will be retrieved automatically.  
     */
    function Ae_Model_Values (& $data, $propName = false, $options = true, $isStatic = false) {
        if (!is_array($options) && is_a($data, 'Ae_Model_Data') && $propName) {
            $options = array();
            $formOptions = $data->getFormOptions($propName, $isStatic);
            if (isset($formOptions['values']) && is_array($formOptions['values'])) $options = $formOptions['values'];
            if (!isset($options['valueList']) && isset($formOptions['valueList'])) $options['valueList'] = $formOptions['valueList'];
        }
        Ae_Util::simpleBind($options, $this);
        if (!isset($options['isStatic'])) $this->isStatic = $isStatic;
        $this->data = & $data;
        $this->propName = $propName;
    }
    
    function resetCache() {
        $this->_resetCache();
    }
    
    /**
     * Resets object properties to initial state
     * @access protected
     */
    function _reset() {
        foreach (get_class_vars($this) as $k => $v) $this->$k = $v;
    }
    
    /**
     * Resets cached values
     * @access protected
     */
    function _resetCache() {
        $this->_cachedValueDetails = false;
        $this->_cachedValueList = false;    
    }
    
    /**
     * Allows to re-use existing list instance with another data and/or options.
     * @param Ae_Model_Data $data
     * @param string $propName name of model property
     * @param bool|array $formOptions Options of that property. If true, they will be retrieved automatically. If false, they won't be changed.  
     */
    function setData (& $data, $propName = false, $options = false) {
        if ($options === true && is_a($data, 'Ae_Model_Data') && $propName) {
            $options = array();
            $formOptions = $data->getFormOptions($propName, $isStatic);
            if (isset($formOptions['options']) && is_array($formOptions['options'])) $options = $formOptions['options'];
        }
        if (is_array($options)) {
            $this->_reset();
            Ae_Util::simpleBind($options, $this);
        } else {
            if (!is_object($this->data) || !is_object($data)) $this->_resetCache();
            if (!Ae_Util::sameObject($this->data, $data)) {
                if (get_class($this->data) !== get_class($data)) $this->_resetCache(); 
            }
        }
        $this->data = & $data;
        $this->propName = $propName;
    }
    
    /**
     * Returns list of allowed values and corresponding captions
     * @return array ($value => $caption) or FALSE if result cannot be retrieved using current parameters 
     */
    function getValueList() {
        if ($this->valueList !== false) $res = $this->valueList;
        elseif ($this->_cachedValueList !== false) $res = $this->_cachedValueList;
        elseif ($this->getList) {
            $res = $this->_callGetter($this->getList); 
            if ($this->cache) $this->_cachedValueList = $res;
        }
        else $res = $this->_doDefaultGetValueList();
        return $res; 
    }
    
    /**
     * Default template method to retrieve value list 
     * @access protected
     */
    function _doDefaultGetValueList() {
        return false;
    }
    
    /**
     * Returns list of allowed values and corresponding details
     * @return array ($value => $detail) or ($value => & $detail) if $this->returnDetailsByRef is true or FALSE if result cannot be retrieved using current parameters
     */
    function getAllDetails() {
        if ($this->valueDetails !== false) $res = $this->valueDetails;
        elseif ($this->_cachedValueDetails !== false) $res = $this->_cachedValueDetails;
        elseif ($this->getAllDetails) {
            $res = $this->_callGetter($this->getAllDetails);
            if ($this->cache) $this->_cachedValueDetails = $res;
        }
        elseif ((($allValues = $this->getValueList()) !== false) && $this->getDetails) {
            if ($this->returnDetailsByRef) {
                foreach (array_keys($allValues) as $val) {
                    $res[$val] = & $this->_callGetterRef($this->getDetails, $val);
                }
            } else {
                foreach (array_keys($allValues) as $val) {
                    $res[$val] = $this->_callGetter($this->getDetails, $val);
                }
            }
            if ($this->cache) $this->_cachedValueDetails = $res;
        }
        else $res = $this->_doDefaultGetAllDetails();
        return $res;
    }
    
    /**
     * Default template method to retrieve all details 
     * @access protected
     */
    function _doDefaultGetAllDetails() {
        return false;
    }
    
    /**
     * Returns details of specified value
     * @param string $value Valid (or invalid) value of property 
     * @return mixed|false False is returned if details cannot be retrieved or value is invalid
     */
    function getDetails($value) {
        if (is_array($this->valueDetails)) {
            if (strlen($value) && isset($this->valueDetails[$value])) $res = & $this->valueDetails[$value];
            else $res = false;
        } 
        elseif (is_array($this->_cachedValueDetails)) {
            if (strlen($value) && isset($this->_cachedValueDetails[$value])) $res = & $this->_cachedValueDetails[$value];
            else $res = false;
        } 
        elseif ($this->getDetails) {
            if ($this->returnDetailsByRef) $res = & $this->_callGetterRef($this->getDetails, $value);
            else $res = $this->_callGetter($this->getDetails, $value);
        }
        elseif ($this->getAllDetails && $strlen($value)) {
            $allDetails = $this->_callGetter($this->getAllDetails);
            if ($this->cache) $this->_cachedValueDetails = $allDetails;
            if (isset($allDetails[$value])) $res = & $allDetails[$value];
                else $res = false;
        }
        else $res = & $this->_doDefaultGetDetails($value);
        return $res;
    }
    
    /**
     * Default template method to retrieve value list 
     * @access protected
     */
    function & _doDefaultGetDetails($value) {
        $res = false;
        return $res;
    }
    
    /**
     * Returns caption of specified value
     * @param string $value Valid (or invalid) value of property 
     * @return string|false False is returned if value is invalid
     */
    function getCaption($value) {
        if (is_array($this->valueList)) {
            if (strlen($value) && isset($this->valueList[$value])) $res = $this->valueList[$value];
            else $res = false;
        } 
        elseif (is_array($this->_cachedValueList)) {
            if (strlen($value) && isset($this->_cachedValueList[$value])) $res = $this->_cachedValueList[$value];
            else $res = false;
        } 
        elseif ($this->getCaption) {
            $res = $this->_callGetter($this->getCaption, $value);
        }
        elseif ($this->getList && $strlen($value)) {
            $valueList = $this->_callGetter($this->getList);
            if ($this->cache) $this->_cachedValueList = $valueList;
            if (isset($valueList[$value])) $res = $valueList[$value];
                else $res = false;
        }
        else $res = & $this->_doDefaultGetCaption($value);
        return $res;
    }
    
    /**
     * Default template method to retrieve value list 
     * @access protected
     */
    function _doDefaultGetCaption($value) {
        return false;
    }
    
    /**
     * Validates specified value
     * @param string $value Valid (or invalid) value of property 
     * @return bool true if this is valid value
     */
    function check($value) {
        if ($this->checkValue) $res = $this->_callGetter($this->checkValue, $value);
        else $res = $this->_doDefaultCheck($value);
        return $res;
    }
    
    /**
     * Default template method to retrieve value list 
     * @access protected
     */
    function _doDefaultCheck($value) {
        return $this->getCaption($value) !== false;
    }
    
    function _callGetter($cb) {
        if ($this->provideSelfToGetters) $params = array(& $this);
            else $params = array();
        $na = func_num_args();
        for($i = 1; $i < $na; $i++) $params[] = func_get_arg($i);
        if (is_array($cb)) {
            if (($c = count($cb)) > 2) for($i = 2; $i < $c; $i++) $params[] = & $cb[$i];
            
            if ($cb[0] === false) $callback = $cb[1];
            elseif ($cb[0] === true) $callback = array(& $this->data, $cb[1]);
            else $callback = array(& $cb[0], $cb[1]);
            
        } else $callback = $cb;
        
        $res = call_user_func_array($callback, $params);
        return $res;
    }
    
    function & _callGetterRef($cb) {
        $params = array(& $this);
        $na = func_num_args();
        for($i = 1; $i < $na; $i++) $params[] = func_get_arg($i);
        if (is_array($cb)) {
            if (($c = count($cb)) > 2) for($i = 1; $i < $na; $i++) $params[] = & $cb[$i];
            if ($cb[0] === false) $callback = $cb[1];
            elseif ($cb[0] === true) $callback = array(& $this->data, $cb[1]);
            else $callback = array(& $cb[0], $cb[1]);
            
        } else $callback = $cb;
        
        if (is_array($callback)) {
            if (is_object($callback[0])) {
                $call = '$callback[0]->{$callback[1]}';
            } else {
                if (!Ae_Model_Values::isId($callback[0]) || !Ae_Model_Values::isId($callback[1]))
                    trigger_error ("Valid class/method identifiers must be supplied in callback", E_USER_ERROR);
                    
                $call = $callback[0].'::'.$callback[1];
            }
        } else {
            $call = '$callback';
        }
        
        $p = array(); $pc = count($params);
        for ($i = 0; $i < $pc; $i++) $p[] = '$params['.$i.']';
        $php = '$res = & '.$call.'('.implode(', ' , $p).')';
        eval($php); // I hate to do that!
        return $res;
    }
    
    /**
     * @static
     */
    function isId($foo) {
        return preg_match("/^[a-zA-Z_][0-9a-zA-Z_]*$/", $foo);
    }
    
}

?>
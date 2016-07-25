<?php

/**
 * Provides methods to work with lists of values. This feature is tightly used with Ac_Model_Data properties that have limited sets 
 * of possible values with titles (enum-like or set-like ones, especially foreign keys), but can also be used without bound Model. 
 */
class Ac_Model_Values extends Ac_Prototyped {
    
    /**
     * Data source (is used in self-callbacks - when first param in callbacks is 'true').
     * Can be null or false (but self-callbacks are prohibited).
     * 
     * @var Ac_Model_Data  
     */
    protected $data = false;
    
    /**
     * Name of data property that is described by this list (used to be second parameter to callbacks).
     * Can be empty string.
     * 
     * @var string
     */
    protected $propName = false;
    
    /**
     * Whether same list of values can be used for all models with same class. Is used only when $this->data is set (all no-data value lists are considered static).
     *
     * @var bool 
     */
    protected $isStatic = false;
    
    /**
     * Whether list of values depends on values of other model properties (is primarily used to cache list or in presentational layers to reload forms when some
     * properties are changed). FALSE means no dependencies at all, TRUE means that any property can change values list, and ARRAY with list of property names means
     * that only several properties affect list contents. 
     *  
     * @param bool|array  
     */
    protected $depends = false;
    
    /**
     * Callback function to return whole list (array $value => $caption).
     * Should accept parameters Ac_Model_Values 
     * 
     * Special callback format: 
     * - a) regular callback
     * - b) array(true, $methodName) - will call $this->data->$methodName
     * - c) array(true, $methodName, $arg1, $arg2...) - extra arguments to pass method
     * - d) array(false,$funcName, $arg1, $arg2...) - extra arguments to pass to global function
     * - e) array(className, $methodName, etc...) - extra arguments to pass to callback function
     *
     * @var string|array
     */
    protected $getList = false;
    
    /**
     * Callback function to return caption for the list value or FALSE if such value is invalid.
     * Should accept parameters Ac_Model_Values, $value. 
     */
    protected $getTitles = false;
    
    /**
     * Callback function to check if such value is allowed. Should accept parameters Ac_Model_Values, $value.
     */
    protected $checkValue = false;
    
    /**
     * If model getter was called to retrieve valueList its result in $valueList for further use. 
     * Note: if list is staic, setting this property to true makes cached values to be preserved 
     * if setData() provides $data of same class as current $this->data.    
     * 
     * @var bool 
     */
    protected $cache = true;
    
    /**
     * Simple list of values with their titles
     * @var array ($value => $caption) 
     */
    protected $valueList = false;
    
    /**
     * Whether provide reference to & $this as first parameter to all getters
     * @var bool
     */
    protected $provideSelfToGetters = true;
    
    protected $cachedValueList = false;
    
    /**
     * @deperecated
     * @return Ac_Model_Values
     */
    static function factoryIndependent(array $options = array()) {
        return Ac_Prototyped::factory($options, 'Ac_Model_Values');
    }
    
    static function getPrototypeFromProperty(Ac_Model_Property $prop, array $optionsOverride = array()) {
        if ($prop->isStatic && !(isset($optionsOverride['data']) && $optionsOverride['data'])) {
            throw new Ac_E_InvalidCall("Cannot ".__METHOD__."() when \$prop->isStatic "
                . "and no 'data' key provided in \$optionsOverride");
        }
        $res = array(
            'class' => 'Ac_Model_Values',
            'data' => $prop->srcObject,
            'propName' => $prop->propName,
            'isStatic' => $prop->isStatic,
        );
        if (isset($prop->values) && is_array($valuesOptions = $prop->values))
            Ac_Util::ms($res, $valuesOptions);
        if (!isset($res['valueList']) && isset($prop->valueList))
            $res['valueList'] = $prop->valueList;
        
        // now some obscure magic for backwards compatibility
        if ((isset($res['extraJoins']) || isset($res['where'])) 
            && !isset($res['query']) && isset($res['class']) && $res['class'] === 'Ac_Model_Values_Mapper') {
            $res['class'] = 'Ac_Model_Values_Records';
        }
        
        // Ugly hack for better compatibility with legacy code
        if (!isset($res['class']) || $res['class'] === 'Ac_Model_Values') {
            unset($res['mapperClass']);
        }
        
        if ($optionsOverride) Ac_Util::ms($res, $optionsOverride);
        return $res;
    }
    
    /**
     * Creates Ac_Model_Values using meta-property object. Concrete class can be specified by $prop->values['class'].  
     * @return Ac_Model_Values
     */
    static function factoryWithProperty (Ac_Model_Property $prop, array $optionsOverride = array()) {
        $res = Ac_Prototyped::factory(self::getPrototypeFromProperty($prop, $optionsOverride), 'Ac_Model_Values');
        return $res;
    }
    
    /**
     * Resets cached values
     */
    function resetCache() {
        $this->cachedValueList = false;    
    }
    
    /**
     * Allows to re-use existing list instance with another data and/or options.
     * @param mixed $data
     * @param string $propName name of model property
     * @param bool|array $formOptions Options of that property. If true, they will be retrieved automatically. If false, they won't be changed.  
     */
    function setData ($data) {
        if ($data !== ($oldData = $this->data)) {
            $this->data = $data;
            if ($this->resetCacheOnDataChange($oldData, $data)) 
                $this->resetCache();
        }
    }
    
    protected function resetCacheOnDataChange($oldData, $newData) {
        $res = ($oldData !== $newData);
        return $res;
    }
    
    /**
     * @return Ac_Model_Data
     */
    function getData() {
        return $this->data;
    }
    
    /**
     * Returns list of allowed values and corresponding titles
     * @return array ($value => $caption) or FALSE if result cannot be retrieved using current parameters 
     */
    function getValueList($dontCompute = false) {
        if ($dontCompute) return $this->valueList;
        if ($this->valueList !== false) $res = $this->valueList;
        elseif ($this->cachedValueList !== false) $res = $this->cachedValueList;
        elseif ($this->getList) {
            $res = $this->callGetter($this->getList); 
            if ($this->cache) $this->cachedValueList = $res;
        }
        else $res = $this->doDefaultGetValueList();
        return $res; 
    }
    
    /**
     * Default template method to retrieve value list 
     * @access protected
     */
    protected function doDefaultGetValueList() {
        return false;
    }

    /**
     * Returns caption of specified value
     * @deprecated
     * @param string $value Valid (or invalid) value of property 
     * @return string|false False is returned if value is invalid
     */
    final function getCaption($value) {
        $titles = $this->getTitles(array($value));
        if (isset($titles[$value])) $res = $titles[$value];
            else $res = false;
        return $res;
    }
    
    /**
     * Returns caption of specified value
     * @param string $value Valid (or invalid) value of property 
     * @return string|false False is returned if value is invalid
     */
    function getTitles(array $values) {
        $res = array();
        $todo = $values;
        $vv = $this->valueList? $this->valueList : $this->cachedValueList;
        if (is_array($vv)) {
            $res = array_intersect_key($vv, array_flip(array_unique($values)));
            $todo = array_diff($todo, array_keys($res));
        }
        if ($todo) { // still have something to do
            if ($this->getTitles) {
                $res = $this->callGetter($this->getCaption, $todo);
                if (is_array($res)) 
                    foreach ($res as $v => $c)
                        $res[$v] = $c;
            } else {
                $res = $this->doDefaultGetTitles($values);
            }
        }
        return $res;
    }
    
    /**
     * Default template method to retrieve value list 
     * @access protected
     */
    protected function doDefaultGetTitles(array $values) {
        $vl = $this->getValueList();
        foreach (array_intersect_key($vl, array_flip(array_unique($values))) as $v => $c) {
            $res[$v] = $c;
        }
        return $res;
    }
    
    /**
     * Validates specified value
     * @param string $value Valid (or invalid) value of property 
     * @return bool true if this is valid value
     */
    function check($value) {
        if ($this->checkValue) $res = $this->callGetter($this->checkValue, $value);
        else $res = $this->doDefaultCheck($value);
        return $res;
    }
    
    /**
     * Default template method to retrieve value list 
     * @access protected
     */
    protected function doDefaultCheck($value) {
        $res = false;
        if (is_scalar($value)) {
            $res = array_key_exists($value, $this->getValueList());
        }
        return $res;
    }
    
    protected function callGetter($cb) {
        if ($this->provideSelfToGetters) $params = array($this);
            else $params = array();
        $na = func_num_args();
        for($i = 1; $i < $na; $i++) $params[] = func_get_arg($i);
        if (is_array($cb)) {
            if (($c = count($cb)) > 2) for($i = 2; $i < $c; $i++) $params[] = $cb[$i];
            
            if ($cb[0] === false) $callback = $cb[1];
            elseif ($cb[0] === true) $callback = array(& $this->data, $cb[1]);
            else $callback = array(& $cb[0], $cb[1]);
            
        } else $callback = $cb;
        
        $res = call_user_func_array($callback, $params);
        return $res;
    }
    
    /**
     * Removes all values not in the list. Default implementation filters out all non-scalar values
     * @param array $values List of values to check
     * @return array Array without improper values
     */
    function filterValuesArray(array $values) {
        $res = array();
        foreach ($values as $v) {
            if (is_scalar(($v))) $res[] = $v;
        }
        $res = array_intersect($res, array_keys($this->getValueList()));
        return $res;
    }
    
    /**
     * Sets name of data property that is described by this list
     * @param string $propName
     */
    function setPropName($propName) {
        if ($propName !== ($oldPropName = $this->propName)) {
            $this->propName = $propName;
        }
    }

    /**
     * Returns name of data property that is described by this list
     * @return string
     */
    function getPropName() {
        return $this->propName;
    }

    /**
     * Sets whether same list of values can be used for all models with same class
     * @param bool $isStatic
     */
    function setIsStatic($isStatic) {
        if ($isStatic !== ($oldIsStatic = $this->isStatic)) {
            $this->isStatic = $isStatic;
        }
    }

    /**
     * Returns whether same list of values can be used for all models with same class
     * @return bool
     */
    function getIsStatic() {
        return $this->isStatic;
    }

    /**
     * Sets whether list of values depends on values of other model properties
     */
    function setDepends($depends) {
        if ($depends !== ($oldDepends = $this->depends)) {
            $this->depends = $depends;
        }
    }

    /**
     * Returns whether list of values depends on values of other model properties
     */
    function getDepends() {
        return $this->depends;
    }

    /**
     * Sets callback to return whole list
     */
    function setGetList($getList) {
        if ($getList !== ($oldGetList = $this->getList)) {
            $this->getList = $getList;
        }
    }

    /**
     * Returns callback to return whole list
     */
    function getGetList() {
        return $this->getList;
    }

    /**
     * Sets callback to return titles for the list values
     */
    function setGetTitles($getTitles) {
        if ($getTitles !== ($oldGetTitles = $this->getTitles)) {
            $this->getTitles = $getTitles;
        }
    }

    /**
     * Returns callback to return titles for the list values
     */
    function getGetTitles() {
        return $this->getTitles;
    }

    /**
     * Sets callback to check if values are valid
     */
    function setCheckValues($checkValues) {
        if ($checkValues !== ($oldCheckValues = $this->checkValues)) {
            $this->checkValues = $checkValues;
        }
    }

    /**
     * Returns callback to check if values are valid
     */
    function getCheckValues() {
        return $this->checkValues;
    }

    /**
     * Sets callback to retrieve details for specified values
     */
    function setGetDetails($getDetails) {
        if ($getDetails !== ($oldGetDetails = $this->getDetails)) {
            $this->getDetails = $getDetails;
        }
    }

    /**
     * Returns callback to retrieve details for specified values
     */
    function getGetDetails() {
        return $this->getDetails;
    }

    /**
     * Sets wether cache is used
     * @param bool $cache
     */
    function setCache($cache) {
        if ($cache !== ($oldCache = $this->cache)) {
            $this->cache = (bool) $cache;
        }
    }

    /**
     * Returns wether cache is used
     * @return bool
     */
    function getCache() {
        return $this->cache;
    }

    /**
     * Sets list of values provided as-is
     */
    function setValueList($valueList) {
        if ($valueList !== ($oldValueList = $this->valueList)) {
            $this->valueList = $valueList;
        }
    }

    /**
     * Sets whether to provide reference to & $this as first parameter to all getters
     * @param bool $provideSelfToCallbacks
     */
    function setProvideSelfToCallbacks($provideSelfToCallbacks) {
        if ($provideSelfToCallbacks !== ($oldProvideSelfToCallbacks = $this->provideSelfToCallbacks)) {
            $this->provideSelfToCallbacks = (bool) $provideSelfToCallbacks;
        }
    }

    /**
     * Returns whether to provide reference to & $this as first parameter to all getters
     * @return bool
     */
    function getProvideSelfToCallbacks() {
        return $this->provideSelfToCallbacks;
    }

    /**
     * Sets details of values provided as-is
     */
    function setValueDetails($valueDetails) {
        if ($valueDetails !== ($oldValueDetails = $this->valueDetails)) {
            $this->valueDetails = $valueDetails;
        }
    }

    /**
     * Returns details of values provided as-is
     */
    function getValueDetails() {
        return $this->valueDetails;
    }    
    
}


<?php

abstract class Ac_Model_Relation_Provider extends Ac_Prototyped {
    
    /**
     * @var Ac_Application
     */
    protected $app = false;
    
    /**
     * Whether each $keys or $keys2 combination uniquely identifies record in the source
     * @var bool
     */
    protected $unique = false;
    
    function getWithValues (array $destValues, $byKeys = true, array $srcValues = array()) {
        if ($srcValues && (!$this->getAcceptsSrcValues())) 
            throw new Ac_E_InvalidUsage(__METHOD__.': \$srcValues MUST be empty when getAcceptsSrcValues() is FALSE');
        return $this->doGetWithValues($destValues, $byKeys, $srcValues);
    }
    
    function countWithValues (array $destValues, $byKeys = true, array $srcValues = array()) {
        if ($srcValues && (!$this->getAcceptsSrcValues())) 
            throw new Ac_E_InvalidUsage(__METHOD__.': \$srcValues MUST be empty when getAcceptsSrcValues() is FALSE');
        return $this->doCountWithValues($destValues, $byKeys, $srcValues);
    }

    abstract protected function doGetWithValues (array $destValues, $byKeys = true, array $srcValues = array());
    
    abstract protected function doCountWithValues (array $destValues, $byKeys = true, array $srcValues = array());
    
    
    /**
     * Whether getWithValues() / countWithValues() can properly handle non-empty $srcValues
     * @return boolean
     */
    function getAcceptsSrcValues() {
        return false;
    }
    
    function setApp(Ac_Application $app) {
        if ($app !== ($oldApp = $this->app)) {
            $this->app = $app;
        }
    }
    
    /**
     * @return Ac_Application
     */
    function getApp() {
        return $this->app;
    }
    
    protected function putRowToArray(& $row, & $instance, & $array, $keys, $unique) {
        foreach ($keys as $key) $path[] = $row[$key];
        Ac_Util::setArrayByPath($array, $path, $instance, $unique);
    }

    /**
     * Sets Whether each $keys or $keys2 combination uniquely identifies record in the source
     * @param bool $unique
     */
    function setUnique($unique) {
        $this->unique = $unique;
    }

    /**
     * Returns Whether each $keys or $keys2 combination uniquely identifies record in the source
     * @return bool
     */
    function getUnique() {
        return $this->unique;
    }
        
}
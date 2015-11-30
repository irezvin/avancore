<?php

abstract class Ac_Model_Relation_Provider extends Ac_Prototyped {
    
    /**
     * @var Ac_Application
     */
    protected $application = false;
    
    /**
     * Whether each $keys or $keys2 combination uniquely identifies record in the source
     * @var bool
     */
    protected $unique = false;
    
    abstract function getWithValues (array $values, $byKeys = true, array $nnValues = array());
    
    abstract function countWithValues (array $values, $byKeys = true, array $nnValues = array());
    
    function setApplication(Ac_Application $application) {
        if ($application !== ($oldApplication = $this->application)) {
            $this->application = $application;
        }
    }
    
    /**
     * @return Ac_Application
     */
    function getApplication() {
        return $this->application;
    }
    
    protected function putRowToArray(& $row, & $instance, & $array, $keys, $unique) {
        foreach ($keys as $key) $path[] = $row[$key];
        Ac_Util::simpleSetArrayByPathNoRef($array, $path, $instance, $unique);
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
<?php

class Ac_Etl_Detector_Data extends Ac_Prototyped {
    
    protected $fieldName = false;
    
    protected $numNonEmpty = 0;
    
    protected $numEmpty = 0;
    
    protected $numNulls = 0;
    
    protected $numPasses = 0;
    
    protected $numFails = 0;
    
    protected $variants = array();
    
    /**
     * @var bool
     */
    protected $detectCardinality = false;

    /**
     * Lowercase values before comparison (set to string to use mb_strtolower)
     */
    protected $caseFold = false;

    /**
     * Hash values when detecting cardinality
     * @var bool
     */
    protected $md5 = false;

    /**
     * @var bool
     */
    protected $considerEmptyAsPass = false;
    
    /**
     * @param bool $detectCardinality
     */
    function setDetectCardinality($detectCardinality) {
        $this->detectCardinality = $detectCardinality;
    }

    /**
     * @return bool
     */
    function getDetectCardinality() {
        return $this->detectCardinality;
    }

    function setCaseFold($caseFold) {
        $this->caseFold = $caseFold;
    }

    function getCaseFold() {
        return $this->caseFold;
    }

    /**
     * @param bool $md5
     */
    function setMd5($md5) {
        $this->md5 = $md5;
    }

    /**
     * @return bool
     */
    function getMd5() {
        return $this->md5;
    }    
    
    function getNumVariants() {
        return count($this->variants);
    }
    
    function getVariants() {
        return $this->md5? array() : array_keys($this->variants);
    }
    
    function getVariantsWithCounts() {
        return $this->md5? array() : $this->variants;
    }
    
    
    function getNumNonEmpty() {
        return $this->numNonEmpty;
    }
    
    function getNumEmpty() {
        return $this->numEmpty;
    }
    
    function getNumPasses() {
        return $this->numPasses;
    }
    
    function getNumFails() {
        return $this->numFails;
    }
    
    function isOk() {
        return $this->numPasses && !$this->numFails;
    }
    
    function setFieldName($fieldName) {
        $this->fieldName = $fieldName;
    }

    function getFieldName() {
        return $this->fieldName;
    }
    
    function reset() {
        foreach (array('numNonEmpty', 'numEmpty', 'numNulls', 'numPasses', 'numFails') as $s) $this->$s = 0;
        $this->variants = array();
    }
    
    protected function registerValue($value) {
        if ($this->caseFold) $value = is_string($this->caseFold)? mb_strtolower ($value, $this->caseFold) : strtolower ($value);
        if ($this->md5) $value = md5($value);
        if (!isset($this->variants[$value])) $this->variants[$value] = 1;
            else $this->variants[$value]++;
    }
    
    function accept($value, $problems = false) {
        if (!strlen($value)) $this->numEmpty++;
            else $this->numNonEmpty++;
        if (is_null($value)) $this->numNulls++;
        if (!strlen($problems)) {
            if (strlen($value) || $this->considerEmptyAsPass) {
                $this->numPasses++;
                if ($this->detectCardinality) $this->registerValue($value);
            }
        } else $this->numFails++;
    }
    
    
    function getColumnDefinition(Ac_Sql_Db $db) {
        return false;
    }

    /**
     * @param bool $considerEmptyAsPass
     */
    function setConsiderEmptyAsPass($considerEmptyAsPass) {
        $this->considerEmptyAsPass = $considerEmptyAsPass;
    }

    /**
     * @return bool
     */
    function getConsiderEmptyAsPass() {
        return $this->considerEmptyAsPass;
    }    
    
}
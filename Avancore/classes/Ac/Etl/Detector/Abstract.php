<?php

abstract class Ac_Etl_Detector_Abstract extends Ac_Prototyped {

    protected $id = '';

    /**
     * @var Ac_Etl_Detector_Data[]
     */
    protected $data = array();
    
    protected $dataPrototype = array();

    protected $decorators = false;
    
    protected $trim = true;
    
    protected $allowEmpty = true;
    
    protected $nullifyEmpty = true;
    
    function setAllowEmpty($allowEmpty) {
        $this->allowEmpty = (bool) $allowEmpty;
    }
    
    function getAllowEmpty() {
        return $this->allowEmpty;
    }
    
    /**
     * @param bool $trim
     */
    function setTrim($trim) {
        $this->trim = $trim;
    }

    /**
     * @return bool
     */
    function getTrim() {
        return $this->trim;
    }

    /**
     * @param bool $nullifyEmpty
     */
    function setNullifyEmpty($nullifyEmpty) {
        $this->nullifyEmpty = $nullifyEmpty;
    }

    /**
     * @return bool
     */
    function getNullifyEmpty() {
        return $this->nullifyEmpty;
    }
    
    protected function doGetDecoratorPrototypes() {
        return array();
    }
    
    function getDecorators() {
        return $this->decorators;
    }
    
    function setDecorators(array $decorators) {
        $this->decorators = $this->doGetDecoratorPrototypes();
        foreach ($decorators as $k => $v) $this->decorators[$k] = $v;
    }
    
    function reset() {
        $this->data = array();
    }
    
    /**
     * @return Ac_Etl_Detector_Data[]
     */
    function getAllData() {
        return $this->data;
    }
    
    /**
     * @return Ac_Etl_Detector_Data[]
     */
    function getOkData() {
        $res = array();
        foreach ($this->data as $fieldName => $item) 
            if ($item->isOk()) $res[$fieldName] = $item;
        return $res;
    }
    
    function getColumnDefinition(Ac_Etl_Detector_Data $data, Ac_Sql_Db $db) {
        return $data->getColumnDefinition($db);
    }
    
    /**
     * @param string $fieldName
     * @return Ac_Etl_Detector_Data
     */
    function getData($fieldName) {
        if (!isset($this->data[$fieldName]))
            $this->data[$fieldName] = Ac_Prototyped::factory($this->configureData($fieldName), 'Ac_Etl_Detector_Data');
        return $this->data[$fieldName];
    }
    
    function setDataPrototype(array $dataPrototype = array()) {
        $this->dataPrototype = $dataPrototype;
    }
    
    protected function configureData($fieldName) {
        $proto = $this->dataPrototype;
        $proto['fieldName'] = $fieldName;
        return $proto;
    }
    
    function __construct(array $prototype = array()) {
        if (!strlen($this->id)) {
            $c = explode('_', get_class($this));
            $this->id = array_pop($c);
        }
        parent::__construct($prototype);
    }
    
    function getId() {
        return $this->id;
    }
    
    function setId($id) {
        $this->id = $id;
    }
    
    /**
     * @return bool
     */
    abstract function doCheck($value, & $problems = false);
    
    function process($value) {
        if ($this->trim && is_string($value)) $value = trim($value);
        if ($this->nullifyEmpty && is_string($value) && !strlen($value)) $value = null;
        if ($this->decorators === false) $this->decorators = $this->doGetDecoratorPrototypes ();
        foreach ($this->decorators as $k => $v) $value = Ac_Decorator::decorate($v, $value, $this->decorators[$k]);
        return $value;
    }
    
    function acceptRecord(array $record) {
        foreach ($record as $fieldName => $value) {
            $this->check($value, $processed, $problems);
            $this->getData($fieldName)->accept($processed, $problems);
        }
    }
    
    function check($value, & $processed, & $problems = false) {
        $problems = false;
        $processed = $this->process($value);
        if (!$this->allowEmpty && !strlen($processed)) {
            $problems = "Empty values are not allowed";
        }
        if (!strlen($problems)) $this->doCheck($processed, $problems);
        $ok = !strlen($problems);
        return $ok;
    }
    
    /**
     * @param Ac_Sql_Db $db
     * @return array
     */
    function getDefs(Ac_Sql_Db $db) {
        $res = array();
        foreach ($this->getOkData() as $k => $v) {
            $res[$k] = $v->getColumnDefinition($db);
        }
        return $res;
    }
    
}
<?php

class Ac_Etl_Detector_Schema extends Ac_Prototyped {

    protected $detectors = false;
    
    protected $instantiated = false;
    
    protected $allFields = array();
    
    /**
     * @var string
     */
    protected $defaultDef = " VARCHAR(255) NOT NULL DEFAULT ''";
    
    /**
     * @return array
     */
    function getDefaultDetectors() {
        return array(
            'string' => array(
                'class' => 'Ac_Etl_Detector_String',
            ),
            'int' => array(
                'class' => 'Ac_Etl_Detector_Integer',
            ),
            'decimal' => array(
                'class' => 'Ac_Etl_Detector_Decimal',
            )
        );
    }
    
    function setDetectors(array $prototypes = array()) {
        $this->detectors = $prototypes;
        $this->instantiated = false;
    }
    
    function setOverrideDetectors(array $overridePrototypes = array()) {
        $this->detectors = Ac_Util::m($this->getDefaultDetectors(), $overridePrototypes);
        $this->instantiated = false;
    }
    
    /**
     * @param bool $dontInstantiate
     * @return Ac_Etl_Detector_Abstract[]
     */
    function getDetectors($dontInstantiate = false) {
        if (!$dontInstantiate && !$this->instantiated) {
            if ($this->detectors === false)
                $this->detectors = $this->getDefaultDetectors();
            $this->instantiated = true;
            $this->detectors = Ac_Prototyped::factoryCollection ($this->detectors, 'Ac_Etl_Detector_Abstract', array(), 'id');
        }
        return $this->detectors;
    }
    
    function pushOne(array $record) {
        foreach (array_keys($record) as $k) $this->allFields[$k] = $k;
        foreach ($this->getDetectors() as $d)
            $d->acceptRecord($record);
    }
    
    function pushMany(array $records) {
        foreach ($this->getDetectors() as $d) {
            foreach ($records as $record) {
                foreach (array_keys($record) as $k) $this->allFields[$k] = $k;
                $d->acceptRecord($record);
            }
        }
    }
    
    function reset() {
        $this->allFields = array();
        foreach ($this->getDetectors() as $d) {
            $d->reset();
        }
    }
    
    function listAllFields() {
        return $this->allFields;
    }
    
    function getColumnDefs(Ac_Sql_Db $db) {
        $res = array();
        foreach ($this->listAllFields() as $f) $res[$f] = $db->n($f).' '.$this->getDefaultDef();
        foreach ($this->getDetectors() as $name => $dec) {
            foreach ($dec->getDefs($db) as $f => $def) {
                $res[$f] = $def.' /* '.$name.' */';
            }
        }
        return $res;
    }

    /**
     * @param string $defaultDef
     */
    function setDefaultDef($defaultDef) {
        $this->defaultDef = $defaultDef;
    }

    /**
     * @return string
     */
    function getDefaultDef() {
        return $this->defaultDef;
    }
    
}
<?php

abstract class Ac_Model_Typer_Abstract extends Ac_Mixable {
    
    // The mapper can have only one typer at the moment
    protected $mixableId = 'Ac_Model_Typer_Abstract';
    
    /**
     * @var Ac_Model_Mapper
     */
    protected $mixin = false;
    
    protected $mixinClass = 'Ac_Model_Mapper';
    
    protected $myBaseClass = 'Ac_Model_Typer_Abstract';
    
    /**
     * @var Ac_Application
     */
    protected $app = false;
    
    /**
     * Whether to handle Mapper's onBeforeLoadFromRows event.
     * @var bool
     */
    protected $mapperHandlerEnabled = true;

    abstract function getRecordTypeId(Ac_Model_Object $record);
    
    abstract function getRowTypeId($row);
    
    /**
     * @return Ac_Model_Mapper
     */
    abstract function getMapper($typeId);

    /**
     * @return Ac_Model_Object[]
     */
    abstract protected function instantiateSubset (array $rows, $typeId);
    
    /**
     * May be re-defined in child implementations to NOT to call $this->getRowTypeId() for every row
     * @return array($typeId => array($rows))
     * @param array $rows
     */
    protected function classifyRows(array $rows) {
        $res = array();
        foreach ($rows as $k => $item) {
            $typeId = $this->getRowTypeId($item);
            $res[$typeId][$k] = $item;
        }
        return $res;
    }
    
    /**
     * @return array
     */
    function instantiateSet (array $rows) {
        $res = array();
        $groups = $this->classifyRows($rows);
        foreach ($groups as $typeId => $subset) {
            $loaded = $this->instantiateSubset($subset, $typeId);
            foreach ($loaded as $k => $obj) {
                if (!isset($res[$k]))
                    $res[$k] = $obj;
            }
        }
        return $res;
    }

    function setApp(Ac_Application $app) {
        $this->app = $app;
    }

    /**
     * @return Ac_App
     */
    function getApp() {
        if ($this->app) return $this->app;
        elseif ($this->mixin) return $this->mixin->getApp();
    }
    
    function onBeforeLoadFromRows(array & $uniqueRows, array & $objects) {
        if ($this->mapperHandlerEnabled) {
            $newObjects = $this->instantiateSet($uniqueRows);
            $uniqueRows = array_diff_key($uniqueRows, $newObjects);
            if ($this->mixin->useRecordsCollection) {
                foreach ($newObjects as $v) {
                    $this->mixin->registerOrActualizeObject($v);
                }
            }
            foreach ($newObjects as $k => $v) {
                $objects[$k] = $v;
            }
        }
    }

    /**
     * Sets whether to handle Mapper's onBeforeLoadFromRows event
     * @param bool $mapperHandlerEnabled
     */
    function setMapperHandlerEnabled($mapperHandlerEnabled) {
        $this->mapperHandlerEnabled = $mapperHandlerEnabled;
    }

    /**
     * Returns whether to handle Mapper's onBeforeLoadFromRows event
     * @return bool
     */
    function getMapperHandlerEnabled() {
        return $this->mapperHandlerEnabled;
    }    
    
}
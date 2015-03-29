<?php

class Ac_Model_Mixable_ExtraTable extends Ac_Model_Mixable_Object {

    /**
     * @var Ac_Model_Mapper_Mixable_ExtraTable
     */
    protected $mapperExtraTable = false;
    
    protected $mapper = false;

    function setMapperExtraTable(Ac_Model_Mapper_Mixable_ExtraTable $mapperExtraTable) {
        if ($mapperExtraTable !== ($oldMapperExtraTable = $this->mapperExtraTable)) {
            if ($this->mapperExtraTable) throw Ac_E_InvalidCall::canRunMethodOnce ($this, __METHOD__);
            $this->mapperExtraTable = $mapperExtraTable;
        }
    }
    
    function registerMixin(Ac_I_Mixin $mixin) {
        parent::registerMixin($mixin);
        $this->mapper = $mixin->getMapper();
    }
    
    /**
     * @return Ac_Model_Mapper
     */
    protected function getMapper($id = false) {
        if ($id === false) return $this->mapper;
            else return $this->getApplication()->getMapper($id);
    }

    /**
     * @return Ac_Model_Mapper_Mixable_ExtraTable
     */
    function getMapperExtraTable($require = false) {
        $res = $this->mapperExtraTable;
        if (!$res && $require) 
            throw Ac_E_InvalidUsage("\$mapperExtraTable not set");
        return $res;
    }
    
    function listNonMixedMethods() {
        return array_merge(
            parent::listNonMixedMethods(), array(
                'getMapperExtraTable', 
                'setMapperExtraTable'
            )
        );
    }
    
    function listOwnDataProperties() {
        $extra = $this->getMapperExtraTable(true);
        $res = array_keys($extra->getDefaults());
        return $res;
    }
    
}
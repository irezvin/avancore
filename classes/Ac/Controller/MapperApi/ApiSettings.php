<?php

class Ac_Controller_MapperApi_ApiSettings extends Ac_Application_Component {

    protected $mapperId = null;
    
    var $onlyProps = null;
    
    var $includeProps = [];
    
    var $excludeProps = [];
    
    var $denyProps = [];
    
    var $defaultLimit = 50;
    
    var $maxLimit = null;
    
    var $defaultSort = null;
    
    var $ignoreMeta = false;
    
    var $denyAllAssociations = false;
    
    protected $autoConfigure = true;
    
    protected $defaultProps = null;
    
    protected $defaultDenyProps = [];
    
    /**
     * @var Ac_Model_Mapper
     */
    protected $mapper = null;
    
    /**
     * @param bool $autoConfigure
     */
    function setAutoConfigure($autoConfigure) {
        $autoConfigure = !!$autoConfigure;
        $this->autoConfigure = $autoConfigure;
    }

    /**
     * @return bool
     */
    function getAutoConfigure() {
        return $this->autoConfigure;
    }    
    
    function setMapperId($mapperId) {
        $this->mapperId = $mapperId;
        $this->mapper = null;
        $this->reset();
    }
    
    function getMapperId() {
        return $this->mapperId;
    }

    function setMapper(Ac_Model_Mapper $mapper) {
        $this->mapper = $mapper;
        $this->mapperId = $mapper->getId();
        $this->reset();
    }

    /**
     * @return Ac_Model_Mapper
     */
    function getMapper($require = false) {
        if ($this->mapper !== null) return $this->mapper;
        if ($this->mapperId) {
            $this->mapper = $this->application->getMapper($this->mapperId);
        }
        if (!$this->mapper && $require) {
            throw new Exception("Please setMapperId() or setMapper() first");
        }
        return $this->mapper;
    }
    
    /**
     * @return Ac_Model_Object
     */
    protected function getRecordProto() {
        return $this->getMapper(true)->getPrototype();
    }
    
    protected function reset() {
        $this->defaultProps = null;
        $this->defaultDenyProps = null;
    }

    function autoConfigure() {
        $this->defaultDenyProps = [];
        $this->defaultProps = [];
        $proto = $this->getRecordProto();
        $props = $proto->listProperties();
        $associations = $proto->listAssociations();
        $fields = $proto->listFields(true);
        foreach ($props as $propName) {
            $prop = $proto->getPropertyInfo($propName, true);
            if (!$this->ignoreMeta) {
                if (isset($prop->apiDeny) && $prop->apiDeny) {
                    $this->defaultDenyProps[] = $propName;
                    continue;
                }
                if (isset($prop->apiDefault)) {
                    if ($prop->apiDefault) $this->defaultProps[] = $propName;
                    else continue;
                }
            }
            
            if (in_array($propName, $associations)) {
                if ($this->denyAllAssociations) $this->defaultDenyProps[] = $propName;
            }
            
            // don't add lists and associations by default
            if (!in_array($propName, $fields)) {
                continue;
            }
            
            $isBlob = false;
            if (!isset($prop->dataType) || !$prop->dataType || $prop->dataType === 'string') {
                if (!$prop->maxLength || $prop->maxLength > 255) {
                    $isBlob = true;
                }
            }
            if (!$isBlob) $this->defaultProps[] = $propName;
        }
    }
    
    /**
     * if $from contains foo[bar] and foo[baz][quux] and $what contains foo, 
     * will remove both foo[bar] and foo[baz][quux] from resulting array
     */
    function diffProps(array $props, array $exclude) {
        $res = [];
        if (!$exclude) return $props; // nothing to do
        foreach ($props as $propName) {
            $ok = true;
            foreach ($exclude as $excludePropName) {
                if ($propName === $excludePropName) {
                    $ok = false;
                    continue;
                }
                $withPrefix = $exclude.'[';
                if (strncmp($propName, $withPrefix, strlen($withPrefix))) {
                    $ok = false;
                    continue;
                }
            }
            if ($ok) $res[] = $propName;
        }
        return $res;
    }
    
    function getProps($only = [], $add = [], $remove = []) {       
        if ($only) $res = $only;
        else $res = $this->getDefaultProps();
        if ($add) $res = array_unique(array_merge($res, $add));
        if ($remove) $res = $this->diffProps($res, $remove);
        $res = $this->diffProps($res, $this->getDenyProps());
        return $res;
    }

    function getDefaultProps() {
        if ($this->defaultProps !== null) return $this->defaultProps;
        $this->defaultProps = [];
        if ($this->autoConfigure) $this->autoConfigure();
        return $this->defaultProps;
    }
    
    function getDefaultDenyProps() {
        if ($this->defaultDenyProps !== null) return $this->defaultDenyProps;
        $this->defaultDenyProps = [];
        if ($this->autoConfigure) $this->autoConfigure();
        return $this->defaultDenyProps;
    }
    
    function getDenyProps() {
        return array_unique(array_merge($this->getDefaultDenyProps(), $this->denyProps));
    }
    
    function hasPublicVars() {
        return true;
    }
    
}
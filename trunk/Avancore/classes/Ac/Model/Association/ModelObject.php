<?php

abstract class Ac_Model_Association_ModelObject extends Ac_Prototyped implements Ac_I_ModelAssociation {
    
    protected $relationId = false;

    /**
     * @var Ac_Model_Relation
     */
    protected $relation = false;
    
    protected $inMemoryField = false;

    /**
     * @var Ac_Model_Mapper
     */
    protected $mapper = false;
    
    /**
     * @var array
     */
    protected $fieldLinks = false;
    
    protected $id = false;
    
    protected $errorKey = false;

    function setId($id) {
        if ($id !== ($oldId = $this->id)) {
            if ($this->id) throw Ac_E_InvalidCall::canRunMethodOnce($this, __METHOD__);
            $this->id = $id;
        }
    }

    function getId() {
        return $this->id;
    }

    function setFieldLinks(array $fieldLinks) {
        if ($fieldLinks !== ($oldFieldLinks = $this->fieldLinks)) {
            if ($this->fieldLinks) throw Ac_E_InvalidCall::canRunMethodOnce($this, __METHOD__);
            $this->fieldLinks = $fieldLinks;
        }
    }

    /**
     * @return array
     */
    function getFieldLinks($dontThrow = false) {
        if ($this->fieldLinks === false) {
            $relation = $this->getRelation($dontThrow);
            $this->fieldLinks = $relation->getFieldLinks();
        }
        return $this->fieldLinks;
    }    

    function setErrorKey($errorKey) {
        $this->errorKey = $errorKey;
    }

    function getErrorKey() {
        if ($this->errorKey === false) {
            $this->errorKey = $this->id;
            if (!strlen($this->errorKey)) $this->errorKey = $this->getInMemoryField();
        }
        return $this->errorKey;
    }    
    
    function setMapper(Ac_Model_Mapper $mapper) {
        if ($mapper !== ($oldMapper = $this->mapper)) {
            if ($this->mapper) throw Ac_E_InvalidCall::canRunMethodOnce($this, __METHOD__);
            $this->mapper = $mapper;
        }
    }

    /**
     * @return Ac_Model_Mapper
     */
    function getMapper($dontThrow = false) {
        if ($this->mapper === false && $this->relation)
            $this->mapper = $this->relation->getSrcMapper();
        
        if (!$this->mapper && !$dontThrow) 
            throw new Ac_E_InvalidUsage(__CLASS__." cannot do anything without prior setMapper()");
        
        return $this->mapper;
    }    
    
    function setInMemoryField($inMemoryField) {
        if ($inMemoryField !== ($oldInMemoryField = $this->inMemoryField)) {
            if ($this->inMemoryField) throw Ac_E_InvalidCall::canRunMethodOnce($this, __METHOD__);
            $this->inMemoryField = $inMemoryField;
        }
    }

    function getInMemoryField() {
        if ($this->inMemoryField === false) {
            $rel = $this->getRelation(true);
            if ($rel) $this->inMemoryField = $rel->getSrcVarName();
                else throw Ac_E_InvalidUsage("setInMemoryField() or setRelation(), otherwise ".get_class($this)." is unusable");
        }
        return $this->inMemoryField;
    }    
    
    function checkAssociationLoaded($object) {
        $res = $object->{$this->inMemoryField} !== false;
        return $res;
    }

    function setRelationId($relationId) {
        if ($relationId !== ($oldRelationId = $this->relationId)) {
            if ($this->relation) throw new Ac_E_InvalidCall("Cannot ".__METHOD__." after setRelation()");
            if ($this->relationId !== false) throw Ac_E_InvalidCall::canRunMethodOnce($this, __METHOD__);
            $this->relationId = $relationId;
        }
    }

    function getRelationId() {
        return $this->relationId;
    }
    
    function setRelation(Ac_Model_Relation $relation) {
        if ($relation !== ($oldRelation = $this->relation)) {
            if ($this->relation) throw Ac_E_InvalidCall::canRunMethodOnce($this, __METHOD__);
            if ($this->relationId !== false) throw new Ac_E_InvalidCall("Cannot ".__METHOD__." after setRelationId()");
            $this->relation = $relation;
        }
    }

    /**
     * @return Ac_Model_Relation
     */
    function getRelation($dontThrow = false) {
        if ($this->relation === false) {
            if ($this->relationId) {
                if ($mapper = $this->getMapper()) {
                    $this->relation = $mapper->getRelation($this->relationId);
                } elseif (!$dontThrow) {
                    throw new Ac_E_InvalidCall("Cannot ".__METHOD__." without prior ::setMapper()");
                }
            } elseif (!$dontThrow) {
                throw new Ac_E_InvalidCall("Cannot ".__METHOD__." without prior ::setRelationId()");
            }
        }
        return $this->relation;
    }
    
}
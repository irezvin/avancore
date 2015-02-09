<?php

class Ac_Cg_Model_Part extends Ac_Cg_Model {

    /**
     * Will be used to determine mixable' owners
     * @var array
     */
    var $masterFkIds = array();

    /**
     * Which FKs point to models that don't have to have coreMixable (TRUE = ALL)
     * @var array (fkId)
     */
    var $skipMapperMixables = array();
    
    /**
     * Relations to the masters
     * @var array fkId => propName
     */
    var $masterProperties = array();
    
    var $isReferenced = null;
    
    var $parentExtraTableClass = 'Ac_Model_Mapper_Mixable_ExtraTable';
    
    var $parentExtraTableIsAbstract = false;
    
    var $extraRelationPrototypes = array();
    
    var $extraAssociationPrototypes = array();
    
    var $mixableId = array();
    
    /**
     * @var Ac_Cg_Model
     */
    var $masterModel = false;
    
    function getDefaultParentClassName() {
        return 'Ac_Model_Mixable_ExtraTable';
    }
    
    function onShow() {
        $res = parent::onShow();
        Ac_Util::ms($res, array(
            'masterFkIds' => $this->masterFkIds,
            'masterModelName' => $this->masterModel? $this->masterModel->name : false,
            'isReferenced' => $this->isReferenced,
        ));
        return $res;
    }
    
    function getTemplates() {
        return array('Ac_Cg_Template_ModelPart');
    }
    
    function getGenModelClass() {
        return $this->className.'_Base_ObjectMixable';
    }
    
    function getGenMapperClass() {
        $this->init();
        return $this->className.'_Base_ImplMapper';
    }
    
    function getMapperClass() {
        $this->init();
        return $this->className.'_ImplMapper';
    }
    
    function getDefaultParentExtraTableClassName() {
        return 'Ac_Model_Mapper_Mixable_ExtraTable';
    }
    
    function getGenExtraTableClass() {
        $this->init();
        return $this->className.'_Base_ExtraTable';
    }
    
    function getExtraTableClass() {
        $this->init();
        return $this->className.'_MapperMixable';
    }
    
    function init() {
        if ($this->_init) return;
        parent::init();
        $this->initMasterRelations();
    }
    
    protected function initMasterRelations() {
        $incomingFks = array();
        $outgoingFks = array();
        foreach ($this->masterFkIds as $i => $fkId) {
            $prop = $this->findPropertyByForeignKeyId($fkId);
            if (!$prop) {
                $this->errors["masterFkIds"][$i]['notFound'] = "Property specified by foreign key '{$fkId}' not found";
            } else {
                // disable the property
                $prop->enabled = false;
                if (!$prop->thisIsUnique) {
                    $this->warnings["masterFkIds"][$i]['thisNotUnique'] = "ExtraTable record is not unique";
                }
                if (!$prop->otherIsUnique) {
                    $this->warnings["masterFkIds"][$i]['otherNotUnique'] = "Master record is not unique";
                }
                if ($mirror = $prop->getMirrorProperty()) {
                    // disable the mirror property
                    $mirror->enabled = false;
                }
                
                $this->masterProperties[$fkId] = $prop->name;
                if ($prop->isIncoming) $incomingFks[] = $fkId;
                    else $outgoingFks[] = $fkId;
            }
        }
        $sInc = implode(", ", $incomingFks);
        $sOut = implode(", ", $outgoingFks);
        if (count($incomingFks) && count($outgoingFks)) {
            $this->errors['masterFkIds'] = "Cannot mix incoming relations to master properties ({$sInc}) "
                . "with outgoing ($sOut)";
        } elseif (count($outgoingFks) > 1) {
            $this->errors['masterFkIds'] = "Cannot specify more outgoing reference to the master table"
                . " (specified: $sOut)";
        }
        
        // even if we don't have any incoming relations,
        // we assume extraTable is referenced by other records
        
        $this->isReferenced = !count($outgoingFks);
        
        if (count($outgoingFks) === 1) {
            $mp = $this->masterProperties[$outgoingFks[0]];
            $this->masterModel = $this->getProperty($mp)->getOtherModel();
        }
        
    }
    
    protected function adjustObjectProperty(Ac_Cg_Property_Object $prop) {
        
        $other = $prop->getMirrorProperty();

        // incoming associations are disabled at the moment
        $other->enabled = false;
        $prop->canLoadSrc = false;
        
        // still leave association (no loadDest at the moment) for referencing extra table
        // doesn't work at the moment (class of ExtraTable still ends up in gen'd code)
        
        if (!$this->isReferenced && $this->masterModel) {
            $other->setOtherModel($this->masterModel, true);
            $other->enabled = true;
            $other->canLoadDest = false;
            $other->className = $this->masterModel->className;
            $other->mapperClass = $this->masterModel->getMapperClass();
        }
    }
    
    function beforeGenerate() {
        parent::beforeGenerate();
        // adjust relations
        foreach ($this->listProperties() as $i) {
            $prop = $this->getProperty($i);
            if ($prop instanceof Ac_Cg_Property_Object) {
                // We deal only with regular associations
                if (!in_array($prop->name, $this->masterProperties)) {
                    $this->adjustObjectProperty($prop);
                }
            }
        }
        if ($this->skipMapperMixables !== true) {
            $skip = Ac_Util::toArray($this->skipMapperMixables);
            foreach ($this->masterProperties as $fkId => $propName) {
                if (in_array($fkId, $skip)) continue;
                $prop = $this->getProperty($propName);
                if ($prop instanceof Ac_Cg_Property_Object) {
                    $otherModel = $prop->getOtherModel();
                    $mirror = $prop->getMirrorProperty();
                    if ($this->mixableId) $mixName = $this->mixableId;
                    if ($mirror) $mixName = $mirror->getDefaultVarName();
                        else $mixName = $this->single;
                    $otherModel->mapperCoreMixables[$mixName] = $this->getMapperCoreMixablePrototype($prop);
                }
            }
        }
        $this->extraRelationPrototypes = $this->getRelationPrototypes();
        $this->extraAssociationPrototypes = $this->getAssociationPrototypes();
    }
    
    function getMapperCoreMixablePrototype(Ac_Cg_Property_Object $prop) {
        $rel = $prop->getAeModelRelationPrototype();
        $res = array(
            'class' => $this->getExtraTableClass(),
            'colMap' => $rel['fieldLinks'],
        );
        return $res;
    }
    
    function getExtraTableVars() {
        return array(
            'tableName' => $this->table,
            'extraIsReferenced' => $this->isReferenced,
            'modelMixable' => $this->className,
        );
    }
    
    function getMapperRecordClass() {
        return 'Ac_Model_Record';
    }
    
}
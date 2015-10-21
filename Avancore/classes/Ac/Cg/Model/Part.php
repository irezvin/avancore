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
    
    var $mixableId = false;
    
    var $objectPropertiesPrefix = false;
    
    var $mapperMixableExtra = array();
    
    /**
     * Prototype overrides for different models' mapper mixins
     * @var array ($otherModelNameOrRelationName => array($mapperMixableExtras)
     */
    var $perModelMapperMixableExtras = array();
    
    var $objectTypeField = false;
    
    /**
     * @var Ac_Cg_Model
     */
    var $masterModel = false;
    
    var $inline = false;
    
    protected $masterModelName = false;
    
    function getMixableId() {
        if ($this->mixableId === false) {
            $res = $this->single;
            if (!$this->isReferenced && $this->masterModel) {
                $entity = $this->masterModel->single;
                if (!strncmp($this->single, $entity, strlen($entity))) {
                    $shorter = substr($this->single, strlen($entity));
                    if (strlen($shorter)) {
                        $res = $shorter;
                        $res{0} = strtolower($res{0});
                    }
                }
            }
        } else $res = $this->mixableId;
        return $res;
    }
    
    function getObjectPropertiesPrefix() {
        if ($this->objectPropertiesPrefix === false) $res = $this->getMixableId();
            else $res = $this->objectPropertiesPrefix;
        return $res;
    }
    
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
    
    function listInheritedModelMembers() {
        return array_merge(parent::listInheritedModelMembers(), array(
            'inline'
        ));
    }
    
    function init() {
        if ($this->_init) return;
        parent::init();
        $this->initMasterRelations();
    }
    
    protected function calcPartInheritance() {

        $parentModel = $this->getParentModel();
        
        if ($parentModel && $parentModel instanceof Ac_Cg_Model_Part) {
            foreach ($parentModel->masterFkIds as $fkId) {
                if (!in_array($fkId, $this->masterFkIds)) {
                    $this->masterFkIds[] = $fkId;
                }
            }
            foreach ($parentModel->skipMapperMixables as $sm) {
                if (!in_array($sm, $this->skipMapperMixables))
                    $this->skipMapperMixables[] = $sm;
            }
            
            if ($this->parentExtraTableClass === 'Ac_Model_Mapper_Mixable_ExtraTable') {
                $this->parentExtraTableClass = $parentModel->getExtraTableClass();
            }
            
        }
        
    }
    
    protected function initMasterRelations() {
        $incomingFks = array();
        $outgoingFks = array();
        
        $this->calcPartInheritance();
        
        foreach ($this->masterFkIds as $i => $fkId) {
            $prop = $this->findPropertyByForeignKeyId($fkId);
            if (!$prop) {
                $this->errors["masterFkIds"][$i]['notFound'] = "Property specified by foreign key '{$fkId}' not found";
            } else {
                // disable the property
                $prop->enabled = false;
                $prop->ignoreInDescendants = true;
                if (!$prop->thisIsUnique) {
                    $this->warnings["masterFkIds"][$i]['thisNotUnique'] = "ExtraTable record is not unique";
                }
                if (!$prop->otherIsUnique) {
                    $this->warnings["masterFkIds"][$i]['otherNotUnique'] = "Master record is not unique";
                }
                if ($mirror = $prop->getMirrorProperty()) {
                    // disable the mirror property
                    $mirror->enabled = false;
                    $mirror->ignoreInDescendants = true;
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
        
        if ($prop->otherModelIdInMethodsPrefix === false) {
            $prop->otherModelIdInMethodsPrefix = $this->getObjectPropertiesPrefix();
            $prop->varName = $prop->getDefaultVarName();
            $prop->pluralForList = $prop->getDefaultPluralForList();
        }

        // incoming associations are disabled at the moment
        if ($other) {
            $other->enabled = false;
            $other->ignoreInDescendants = true;
        }
        $prop->canLoadSrc = false;
        
        // still leave association (no loadDest at the moment) for referencing extra table
        // doesn't work at the moment (class of ExtraTable still ends up in gen'd code)
        
        if (!$this->isReferenced && $this->masterModel && $other) {
            $other->setOtherModel($this->masterModel, true);
            $other->enabled = true;
            if ($other->otherModelIdInMethodsPrefix === false) {
                $other->otherModelIdInMethodsPrefix = $this->getObjectPropertiesPrefix();
                $other->varName = $other->getDefaultVarName();
                $prop->pluralForList = $other->getDefaultPluralForList();
            }
            
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
                $shouldSkip = in_array($fkId, $skip);
                $prop = $this->getProperty($propName);
                if ($prop instanceof Ac_Cg_Property_Object) {
                    $otherModel = $prop->getOtherModel();
                    $this->prepareOtherModel($otherModel, $prop, $shouldSkip);
                }
            }
        }
        $this->extraRelationPrototypes = $this->getRelationPrototypes();
        $this->extraAssociationPrototypes = $this->getAssociationPrototypes();
    }
    
    function prepareOtherModel(Ac_Cg_Model $otherModel, Ac_Cg_Property_Object $masterProperty, $shouldSkip) {
        $mirror = $masterProperty->getMirrorProperty();
        if ($shouldSkip) {
            $masterProperty->enabled = false;
            if ($mirror) $mirror->enabled = false;
        } else {
            $mixName = $this->getMixableId();
            $otherModel->mapperCoreMixables[$mixName] = $this->getMapperCoreMixablePrototype($masterProperty);
            if ($this->inline) {
                foreach (array_diff($this->listProperties(), $this->masterProperties) as $propName) {
                    // don't overwrite existing properties of other model
                    //while(ob_get_level()) ob_end_clean ();
                    if (!in_array($propName, $otherModel->listProperties())) { 
                        $myProp = $this->getProperty($propName);
                        $prop = clone $myProp;
                        $prop->_model = $otherModel;
                        if ($prop instanceof Ac_Cg_Property_Object && $prop->modelRelation) {
                            $prop->modelRelation = clone $myProp->modelRelation;
                            $prop->modelRelation->createAssociationObject = false;
                            $prop->modelRelation->createRelationObject = false;
                            $otherModel->addRelationInfo($prop->modelRelation);
                        }
                        $otherModel->addProperty($prop);
                    }
                }
            }
        }
    }
    
    function getMapperCoreMixablePrototype(Ac_Cg_Property_Object $prop) {
        $rel = $prop->getAeModelRelationPrototype();
        $res = array(
            'class' => $this->getExtraTableClass(),
            'colMap' => $rel['fieldLinks'],
        );
        $otherModel = $prop->getOtherModel();
        if (isset($this->perModelMapperMixableExtras[$otherModel->name]) && 
            is_array($this->perModelMapperMixableExtras[$otherModel->name])) {
            Ac_Util::ms($res, $this->perModelMapperMixableExtras[$otherModel->name]);
        }
        if ($otherModel->name !== ($relName = $prop->getRelation()->name)) {
            if (isset($this->perModelMapperMixableExtras[$relName]) && 
                is_array($this->perModelMapperMixableExtras[$relName])) {
                Ac_Util::ms($res, $this->perModelMapperMixableExtras[$relName]);
            }
        }
        return $res;
    }
    
    function getExtraTableVars() {
        $res = array(
            'tableName' => $this->table,
            'extraIsReferenced' => $this->isReferenced,
            'modelMixable' => $this->className,
        );
        if ($this->inline) unset($res['modelMixable']);
        if ($mc = $this->getMapperClass()) {
            $res['implMapper'] = $this->getMapperClass();
            unset($res['tableName']);
        }
        if (strlen($this->objectTypeField)) {
            $res['objectTypeField'] = $this->objectTypeField;
        }
        if ($this->mapperMixableExtra) 
            Ac_Util::ms($res, $this->mapperMixableExtra);
        return $res;
    }
    
    function getMapperRecordClass() {
        return 'Ac_Model_Record';
    }

    protected function beforeSerialize(&$vars) {
        unset($vars['masterModel']);
        $vars['masterModelName'] = $this->masterModel? $this->masterModel->name : '';
        parent::beforeSerialize($vars);
    }
    
    public function unserializeFromArray($array) {
        if (isset($array['masterModelName'])) {
            $this->masterModelName = $array['masterModelName'];
        }
        parent::unserializeFromArray($array);
    }
     
    function initProperties() {
        if ($this->masterModelName) {
            $this->masterModel = $this->_domain->getModel($this->masterModelName);
            $this->masterModelName = false;
        }
        foreach ($this->_properties as $p) $p->init();
    }
   
}
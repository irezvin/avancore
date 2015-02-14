<?php

class Ac_Cg_Template_ModelPart extends Ac_Cg_Template_ModelAndMapper {
    
    var $extraTableClass = false;
    
    var $genExtraTableClass = false;
    
    var $parentExtraTableClass = false;
    
    var $parentExtraTableIsAbstract = false;
    
    var $extraTableVars = array();
    
    var $extraRelationPrototypes = array();
    
    var $extraAssociationPrototypes = array();
    
    var $inline = false;
    
    /**
     * @var Ac_Cg_Model_Part
     */
    var $model = false;
    
    function _generateFilesList() {
        $res = parent::_generateFilesList();
        Ac_Util::ms($res, array(
            'extraTable' => array(
                'relPath' => Ac_Cg_Util::className2fileName($this->extraTableClass), 
                'isEditable' => true, 
                'templatePart' => 'extraTable',
            ),
            'genExtraTable' => array(
                'relPath' => 'gen/'.Ac_Cg_Util::className2fileName($this->genExtraTableClass), 
                'isEditable' => false, 
                'templatePart' => 'genExtraTable',
            ),
        ));
        if ($this->inline) {
            unset($res['modelObject']);
            unset($res['genModelObject']);
        }
        return $res;
    }
    
    function doInit() {
        parent::doInit();
        
        $this->hasUniformPropertiesInfo = false;
        $this->tracksChanges = false;
        
        $this->extraTableClass = $this->model->getExtraTableClass();
        $this->genExtraTableClass = $this->model->getGenExtraTableClass();
        $this->parentExtraTableClass = $this->model->parentExtraTableClass;
        $this->parentExtraTableIsAbstract = $this->model->parentExtraTableIsAbstract;
        $this->extraTableVars = $this->model->getExtraTableVars();
        $this->extraRelationPrototypes = $this->model->extraRelationPrototypes;
        $this->extraAssociationPrototypes = $this->model->extraAssociationPrototypes;
        $this->inline = $this->model->inline;
    }
    
    function getAssocStrategy($relationId, $prop) {
        $res = parent::getAssocStrategy($relationId, $prop);
        $res->relationTargetExpression = '$this->mixin';
        return $res;
    }
    
    function showExtraTable() { 
        
    // ------------------------------------------- extraTable -------------------------------------------    
        
?>
<?php $this->phpOpen(); ?> 

class <?php $this->d($this->extraTableClass); ?> extends <?php $this->d($this->genExtraTableClass); ?> {
    
}

<?php        
    }
    
    function showGenExtraTable() { 
        
    // ------------------------------------------- genExtraTable -------------------------------------------    
        
?>
<?php $this->phpOpen(); ?> 

<?php if ($this->parentExtraTableIsAbstract) echo "abstract "; ?>class <?php $this->d($this->genExtraTableClass); ?> extends <?php $this->d($this->parentExtraTableClass); ?> {

<?php   foreach ($this->extraTableVars as $var => $val) { ?>
    protected $<?php echo $var; ?> = <?php $this->export($val); ?>;
    
<?php   } ?>    

<?php if ($this->extraRelationPrototypes) { ?>
    
    protected function doGetRelationPrototypes() {
<?php   if (!in_array($this->parentExtraTableClass, array('Ac_Model_Mapper_Mixable_ExtraTable'))) { ?>
        return Ac_Util::m(parent::doGetRelationPrototypes(), <?php $this->exportArray($this->extraRelationPrototypes, 8); ?>);
<?php   } else { ?>
        return <?php $this->exportArray($this->extraRelationPrototypes, 8); ?>;
<?php   } ?>        
    }
<?php } ?>
<?php if ($this->associationPrototypes) { ?>
    
    protected function doGetAssociationPrototypes() {
<?php   if (!in_array($this->parentExtraTableClass, array('Ac_Model_Mapper_Mixable_ExtraTable'))) { ?>
        return Ac_Util::m(parent::doGetAssociationPrototypes(), <?php $this->exportArray($this->extraAssociationPrototypes, 8); ?>);
<?php   } else { ?>
        return <?php $this->exportArray($this->extraAssociationPrototypes, 8); ?>;
<?php   } ?>
    }
<?php   } ?>
<?php   foreach (array_keys($this->assocProperties) as $relId) { $this->_showMapperMethodsForAssociation($relId, $this->assocProperties[$relId]); } ?>
}

<?php        
    }
    
    
    function showModelGenObject() {  

        $fieldVisibility = $this->createAccessors? 'protected' : 'public';
        
    // ------------------------------------------- modelGenObject -------------------------------------------    
        
?>
<?php $this->phpOpen(); ?>


<?php if ($this->model->parentClassIsAbstract) echo "abstract "; ?>class <?php $this->d($this->genModelClass); ?> extends <?php $this->d($this->parentClass); ?> {

<?php foreach($this->vars as $var => $default) { ?>
    <?php echo $fieldVisibility; ?> $<?php $this->d($var); ?> = <?php $this->export($default); ?>;
<?php } ?>
    
    /**
     * @var <?php echo $this->extraTableClass; ?> 
     */
    protected $mapperExtraTable = false;

    /**
     * @return <?php echo $this->domain->getAppClass(); ?> 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), <?php $this->exportArray($this->ownProperties, 0, false, true); ?>);
    }
<?php if ($this->ownLists) { ?> 
    protected function listOwnLists() {
<?php if ($this->parentClass !== $this->model->getDefaultParentClassName()) { ?>
    return array_merge(parent::listOwnLists(), <?php $this->exportArray($this->ownLists, 0, false, true); ?>);
<?php } else { ?>        
        return <?php $this->exportArray($this->ownLists, 0, false, true); ?>;
<?php }?>
    }

<?php } ?>    
<?php if ($this->ownAssociations) { ?> 
    protected function listOwnAssociations() {
<?php if ($this->parentClass !== $this->model->getDefaultParentClassName()) { ?>
        return array_merge(parent::listOwnLists(), <?php $this->exportArray($this->ownAssociations, 0, false, true); ?>);
<?php } else { ?>
        return <?php $this->exportArray($this->ownAssociations, 0, false, true); ?>;
<?php }?>
    }

<?php } ?>
    protected function getOwnPropertiesInfo() {
    	<?php if ($this->generator->php5) echo 'static $pi = false; if ($pi === false) '; ?>$pi = <?php $this->exportArray($this->ownPropInfo, 8, true); ?>;
<?php   if ($this->parentClass === $this->model->getDefaultParentClassName()) { ?>    
        return $pi;
<?php   } else { ?>
        return Ac_Util::m($pi, parent::getOwnPropertiesInfo());
<?php   } ?>                
    }
    
<?php if ($this->createAccessors) $this->_showModelAccessors(); ?>
<?php foreach (array_keys($this->assocProperties) as $relId) { $this->_showModelMethodsForAssociation($relId, $this->assocProperties[$relId]); } ?>  
    
}

<?php        
    }
    
    function showGenMapper() {
        $tmp = array($this->associationPrototypes, $this->assocProperties);
        $this->associationPrototypes = $this->assocProperties = array();
        parent::showGenMapper();
        list($this->associationPrototypes, $this->assocProperties) = $tmp;
    }    
    
}
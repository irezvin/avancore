<?php

class Cg_Template_Assoc_Strategy extends Cg_Template {
    
    /**
     * @var Cg_Template_ModelAndMapper
     */
    var $template = false;
    
    /**
     * @var Cg_Model
     */
    var $model = false;

    /**
     * @var Cg_Model
     */
    var $otherModel = false;
    
    /**
     * @var Cg_Property_Object
     */
    var $prop = false;
    
    /**
     * @var Cg_Property_Object
     */
    var $mirrorProp = false;
    
    var $mirrorMethod = false;
    var $relationId = false;
    var $var = false;
    var $varId = false;
    var $single = false; 
    var $ucSingle = false;
    var $plural = false;
    var $ucPlural = false;
    var $singleId = false;
    var $count = false;
    var $countId = false;

    var $thisPlural = false;
    var $otherPlural = false;
    var $otherSingle = false;
    var $idThisPlural = false;
    var $idOtherPlural = false;      
    var $ucThisPlural = false;
    var $ucOtherPlural = false;
    var $thisClass = false;
    var $otherClass = false;
    
    var $mirrorSingle = false;
    var $mirrorPlural = false;
    var $ucMirrorSingle = false;
    var $ucMirrorPlural = false;
    
    var $mirrorAddMethod = false;
    var $mirrorRemoveMethod = false;
    var $mirrorVar = false;
    
    function Cg_Template_Assoc_Strategy ($options) {
        Ac_Util::simpleBindAll($options, $this);
    }
    
    function init() {

        $this->var = $this->prop->getClassMemberName();
        $this->varId = '$this->'.$this->var;
        $this->otherModel = $this->prop->getOtherModel();
        
//        $om = $prop->getOtherModel();
//        $single = Cg_Util::makeIdentifier($om->single);
//        $ucSingle = ucfirst($single).$prop->idrSuffixSingle; 
//        $singleId = '$this->'.$single; 

        $this->single = $this->prop->getOtherEntityName(true); 
        $this->ucSingle = ucfirst($this->single);
        $this->plural = $this->prop->getOtherEntityName(false);
        $this->ucPlural = ucfirst($this->plural);
        $this->singleId = '$this->'.$this->single;
        
        if ($this->prop->otherModelIdInMethodsSingle) {
            $this->single = $this->prop->otherModelIdInMethodsSingle;
            $this->ucSingle = ucfirst($this->single);
        }
        
        if ($this->prop->otherModelIdInMethodsPlural) {
            $this->plural = $this->prop->otherModelIdInMethodsPlural;
            $this->ucPlural = ucfirst($this->plural);
        }
        
        if ($this->prop->isList()) {
            $this->count = $this->prop->getCountMemberName();
            $this->countId = '$this->'.$this->count;
        }

        $this->thisPlural = Cg_Util::makeIdentifier($this->model->plural);
        $this->otherPlural = Cg_Util::makeIdentifier($this->otherModel->plural);
        
        if ($this->model->fixMapperMethodNames) {
            $this->otherPlural = Cg_Util::makeIdentifier($this->plural);
        }
        
        if ($this->prop->otherModelIdInMethodsSingle) $this->otherSingle = $this->prop->otherModelIdInMethodsSingle;
        if ($this->prop->otherModelIdInMethodsPlural) $this->otherPlural = $this->prop->otherModelIdInMethodsPlural; 
        
        if (strlen($px = $this->prop->otherModelIdInMethodsPrefix)) {
            $this->single = $px.ucfirst($this->single);
            $this->plural = $px.ucfirst($this->plural);
            $this->ucSingle = ucfirst($px).$this->ucSingle;
            $this->ucPlural = ucfirst($px).$this->ucPlural;
            $this->otherSingle = $px.ucfirst($this->otherSingle);
            $this->otherPlural = $px.ucfirst($this->otherPlural); 
        }
        
        
        $this->idThisPlural = '$'.$this->thisPlural;
        $this->idOtherPlural = '$'.$this->otherPlural;      
        $this->ucThisPlural = ucfirst($this->thisPlural);
        $this->ucOtherPlural = ucfirst($this->otherPlural);
        $this->thisClass = $this->template->modelClass;
        $this->otherClass = $this->otherModel->className;
        if (($this->mirrorProp = $this->prop->getMirrorProperty()) && $this->mirrorProp->isEnabled()) {
       
            $this->mirrorSingle = $this->mirrorProp->otherModelIdInMethodsSingle;
            if ($this->mirrorProp->otherModelIdInMethodsSingle) $this->mirrorSingle  = $this->mirrorProp->otherModelIdInMethodsSingle.$sfx;
            $this->ucMirrorSingle = ucfirst($this->mirrorSingle);
            
            $this->mirrorPlural = $this->mirrorProp->otherModelIdInMethodsPlural;
            if ($this->mirrorProp->otherModelIdInMethodsPlural) $this->mirrorPlural  = $this->mirrorProp->otherModelIdInMethodsPlural.$sfx;
            $this->ucMirrorPlural = ucfirst($this->mirrorPlural);
            
            if ($this->mirrorProp->isList()) {
                $this->mirrorAddMethod = 'add'.$this->ucMirrorSingle;
                $this->mirrorRemoveMethod = 'remove'.$this->ucMirrorSingle;
            } else {
                $this->mirrorAddMethod = 'set'.$this->ucMirrorSingle;
                $this->mirrorRemoveMethod = 'clear'.$this->ucMirrorSingle;
            }
            
            $this->mirrorVar = $this->mirrorProp->varName;
            if ($this->mirrorProp->isPrivateVar && strlen($this->mirrorVar)) $this->mirrorVar = '_'.$this->mirrorVar;
            
            //$this->mirrorMethod = $this->prop->isList()? 'set'.ucfirst($this->mirrorProp->
        }
        
    }
    
    function showGenModelMethods() {
        $this->init();
        $this->_doShowGenModelMethods();
    }
    
    function showGenMapperMethods() {
        $this->init();
        $this->_doShowGenMapperMethods();
    }
    
    function _doShowGenModelMethods() {
        trigger_error("Call to abstract method", E_USER_ERROR);
    }
    
    function _doShowGenMapperMethods() {
        extract(get_object_vars($this));
?>

    /**
     * Returns (but not loads!) <?php if ($prop->thisIsUnique) { ?>one or more<?php } else { ?>several<?php } ?> <?php $this->d($this->model->plural); ?> of given one or more <?php $this->d($otherModel->plural); ?> 
     * @param <?php $this->d($thisClass); ?>|array <?php $this->d($idOtherPlural); ?>
     
     * @return <?php if ($prop->otherIsUnique) { ?><?php $this->d($thisClass); ?>|<?php } ?>array of <?php $this->d($thisClass); ?> objects  
     */
    function getOf<?php $this->d($ucOtherPlural); ?>(<?php $this->d($idOtherPlural); ?>) {
        $rel = $this->getRelation(<?php $this->str($relationId); ?>);
        $res = $rel->getSrc(<?php $this->d($idOtherPlural); ?>); 
        return $res;
    }
    
    /**
     * Loads <?php if ($prop->thisIsUnique) { ?>one or more<?php } else { ?>several<?php } ?> <?php $this->d($this->model->plural); ?> of given one or more <?php $this->d($otherModel->plural); ?> 
     * @param <?php $this->d($otherClass); ?>|array <?php $this->d($idOtherPlural); ?> of <?php $this->d($thisClass); ?> objects
     
     */
    function loadFor<?php $this->d($ucOtherPlural); ?>(<?php $this->d($idOtherPlural); ?>) {
        $rel = $this->getRelation(<?php $this->str($relationId); ?>);
        return $rel->loadSrc(<?php $this->d($idOtherPlural); ?>); 
    }

    /**
     * Loads <?php if ($prop->thisIsUnique) { ?>one or more<?php } else { ?>several<?php } ?> <?php $this->d($otherModel->plural); ?> of given one or more <?php $this->d($this->model->plural); ?> 
     * @param <?php $this->d($thisClass); ?>|array <?php $this->d($idThisPlural); ?>
     
     */
    function load<?php $this->d($ucOtherPlural); ?>For(<?php $this->d($idThisPlural); ?>) {
        $rel = $this->getRelation(<?php $this->str($relationId); ?>);
        return $rel->loadDest(<?php $this->d($idThisPlural); ?>); 
    }

<?php

    }

    function showStoreUpstandingPart() {
        $this->init();
        return $this->_doShowStoreUpstandingPart(); 
    }

    function showStoreDownstandingPart() {
        $this->init();
        return $this->_doShowStoreDownstandingPart(); 
    }

    function showStoreNNPart() {
        $this->init();
        return $this->_doShowStoreNNPart(); 
    }
    
    function _doShowStoreUpstandingPart() {
        return false;
    }
    
    function _doShowStoreDownstandingPart() {
        return false;
    }
    
    function _doShowStoreNNPart() {
        return false;
    }
    
    
}

?>
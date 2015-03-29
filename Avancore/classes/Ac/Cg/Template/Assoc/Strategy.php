<?php

class Ac_Cg_Template_Assoc_Strategy extends Ac_Cg_Template {
    
    /**
     * @var Ac_Cg_Template_ModelAndMapper
     */
    var $template = false;
    
    /**
     * @var Ac_Cg_Model
     */
    var $model = false;

    /**
     * @var Ac_Cg_Model
     */
    var $otherModel = false;
    
    /**
     * @var Ac_Cg_Property_Object
     */
    var $prop = false;
    
    /**
     * @var Ac_Cg_Property_Object
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
    var $loaded = false;
    var $loadedId = false;

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
    
    var $canLoadSrc = true;
    var $canLoadDest = true;
    var $canCreateDest = true;
    
    var $relationTargetExpression = '$this';
    
    function Ac_Cg_Template_Assoc_Strategy ($options) {
        Ac_Util::simpleBindAll($options, $this);
    }
    
    function getGuessMap() {
        $res = array(
            'loadDestObjectsMapperMethod' => 'load{Plural}For',
            'loadSrcObjectsMapperMethod' => 'loadFor{Plural}',
            'getSrcObjectsMapperMethod' => 'getOf{Plural}',
            'createDestObjectMethod' => 'create{Single}',
        );
        if (!$this->canLoadDest) {
            $res['loadDestObjectsMapperMethod'] = null;
        }
        if (!$this->canLoadSrc) {
            $res['loadSrcObjectsMapperMethod'] = null;
        }
        if (!$this->canCreateDest) {
            $res['createDestObjectMethod'] = null;
        }
        return $res;
    }
    
    function getMethodNames() {
        $this->init();
        $res = array();
        $tr = array(
            '{single}' => $this->single,
            '{Single}' => $this->ucSingle,
            '{plural}' => $this->plural,
            '{Plural}' => $this->ucPlural,
        );
        foreach ($this->getGuessMap() as $k => $v) {
            if (is_null($v)) $res[$k] = $v;
                else $res[$k] = strtr($v, $tr);
        }
        return $res;
    }
    
    function init() {

        $this->var = $this->prop->getClassMemberName();
        $this->varId = '$this->'.$this->var;
        $this->otherModel = $this->prop->getOtherModel();
        
//        $om = $prop->getOtherModel();
//        $single = Ac_Cg_Util::makeIdentifier($om->single);
//        $ucSingle = ucfirst($single).$prop->idrSuffixSingle; 
//        $singleId = '$this->'.$single; 

        $this->single = $this->prop->getOtherEntityName(true); 
        $this->ucSingle = ucfirst($this->single);
        $this->plural = $this->prop->getOtherEntityName(false);
        $this->ucPlural = ucfirst($this->plural);
        $this->singleId = '$this->'.$this->single;
        
        $this->canLoadDest = $this->prop->canLoadDest;
        $this->canLoadSrc = $this->prop->canLoadSrc;
        $this->canCreateDest = $this->prop->canCreateDest;
        
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
            $this->loaded = $this->prop->getLoadedMemberName();
            $this->loadedId = '$this->'.$this->loaded;
        }

        $this->thisPlural = Ac_Cg_Util::makeIdentifier($this->model->plural);

        if ($this->domain->addSubsystemsToMapperMethods) {
            $this->otherPlural = Ac_Cg_Util::makeIdentifier($this->prop->getOtherEntityName(false));
        } else {
            $this->otherPlural = Ac_Cg_Util::makeIdentifier($this->otherModel->plural);
        }
        
        if ($this->model->fixMapperMethodNames) {
            $this->otherPlural = Ac_Cg_Util::makeIdentifier($this->plural);
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
        $this->thisClass = $this->model->className;
        $this->otherClass = $this->otherModel->className;
        if (($this->mirrorProp = $this->prop->getMirrorProperty()) && $this->mirrorProp->isEnabled()) {
       
            $this->mirrorSingle = $this->mirrorProp->otherModelIdInMethodsSingle;
            if ($this->mirrorProp->otherModelIdInMethodsSingle) $this->mirrorSingle  = $this->mirrorProp->otherModelIdInMethodsSingle;
            $this->ucMirrorSingle = ucfirst($this->mirrorSingle);
            
            $this->mirrorPlural = $this->mirrorProp->otherModelIdInMethodsPlural;
            if ($this->mirrorProp->otherModelIdInMethodsPlural) $this->mirrorPlural  = $this->mirrorProp->otherModelIdInMethodsPlural;
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

<?php if ($this->canLoadSrc) { ?>
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
<?php } ?>
    
<?php if ($this->canLoadSrc) { ?>
    /**
     * Loads <?php if ($prop->thisIsUnique) { ?>one or more<?php } else { ?>several<?php } ?> <?php $this->d($this->model->plural); ?> of given one or more <?php $this->d($otherModel->plural); ?> 
     * @param <?php $this->d($otherClass); ?>|array <?php $this->d($idOtherPlural); ?> of <?php $this->d($thisClass); ?> objects      
     */
    function loadFor<?php $this->d($ucOtherPlural); ?>(<?php $this->d($idOtherPlural); ?>) {
        $rel = $this->getRelation(<?php $this->str($relationId); ?>);
        return $rel->loadSrc(<?php $this->d($idOtherPlural); ?>); 
    }
<?php } ?>
    
<?php if ($this->canLoadDest) { ?>
    /**
     * Loads <?php if ($prop->thisIsUnique) { ?>one or more<?php } else { ?>several<?php } ?> <?php $this->d($otherModel->plural); ?> of given one or more <?php $this->d($this->model->plural); ?> 
     * @param <?php $this->d($thisClass); ?>|array <?php $this->d($idThisPlural); ?>
     
     */
    function load<?php $this->d($ucOtherPlural); ?>For(<?php $this->d($idThisPlural); ?>) {
        $rel = $this->getRelation(<?php $this->str($relationId); ?>);
        return $rel->loadDest(<?php $this->d($idThisPlural); ?>); 
    }
<?php } ?>

<?php

    }

}


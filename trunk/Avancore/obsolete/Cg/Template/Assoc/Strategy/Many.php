<?php

class Cg_Template_Assoc_Strategy_Many extends Cg_Template_Assoc_Strategy {
    
    function _doShowGenModelMethods() {
        extract(get_object_vars($this));
        
?>
<?php if ($this->count) { ?>

    function count<?php $this->d($ucPlural); ?>() {
        if (is_array(<?php $this->d($varId); ?>)) return count(<?php $this->d($varId); ?>);
        if (<?php $this->d($countId); ?> === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocCountFor($this, <?php $this->str($relationId); ?>);
        }
        return <?php $this->d($countId); ?>;
    }
<?php } ?>

    function list<?php $this->d($ucPlural); ?>() {
        if (<?php $this->d($varId); ?> === false) {
            $mapper = $this->getMapper();
            $mapper->listAssocFor($this, <?php $this->str($relationId); ?>);
        }
        return array_keys(<?php $this->d($varId); ?>);
    }
    
    /**
     * @return <?php $this->d($prop->className); ?> 
     */
    function get<?php $this->d($ucSingle); ?>($id) {
        if (<?php $this->d($varId); ?> === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocFor($this, <?php $this->str($relationId); ?>);
        }
        if (!isset(<?php $this->d($varId); ?>[$id])) trigger_error ('No such <?php echo addcslashes($otherModel->singleCaption, '\''); ?>: \''.$id.'\'', E_USER_ERROR);
        if (<?php $this->d($varId); ?>[$id] === false) {
        }
        return <?php $this->d($varId); ?>[$id];
    }
    
    /**
     * @param <?php $this->d($prop->className); ?> $<?php $this->d($single); ?> 
     */
    function add<?php $this->d($ucSingle); ?>($<?php $this->d($single); ?>) {
        if (!is_a($<?php $this->d($this->single); ?>, <?php $this->str($prop->className); ?>)) trigger_error('$<?php $this->d($this->single); ?> must be an instance of <?php $this->d($prop->className); ?>', E_USER_ERROR);
        $this->list<?php $this->d($ucPlural); ?>();
        <?php $this->d($varId); ?>[] = $<?php $this->d($single); ?>;
<?php   $this->_showLinkBackCode(); ?>        
    }
    
    /**
     * @return <?php $this->d($prop->className); ?>  
     */
    function create<?php $this->d($ucSingle); ?>($values = array(), $isReference = false) {
        $m = $this->getMapper(<?php $this->str($this->prop->mapperClass); ?>);
        $res = $m->factory();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->add<?php $this->d($ucSingle); ?>($res);
        return $res;
    }
    
<?php

    }
    
    function _showLinkBackCode() {
?>        
<?php   if (strlen($this->mirrorVar)) { ?>
        $<?php $this->d($this->single)?>-><?php $this->d($this->mirrorVar); ?> = $this;
<?php   } ?>
<?php  
    }

    function _doShowStoreReferencingPart() {

        if ($this->prop->isIncoming) {
?>

        if (is_array($this-><?php $this->d($this->var); ?>)) {
            $rel = $mapper->getRelation(<?php $this->str($this->relationId); ?>);
            if (!$this->_autoStoreReferencing($this-><?php $this->d($this->var); ?>, $rel->fieldLinks, <?php $this->str($this->plural); ?>)) $res = false;
        }
<?php   
        return true;
        
        } else return false;
        
    }
    
    
}


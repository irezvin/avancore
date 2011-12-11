<?php

Ae_Dispatcher::loadClass('Cg_Template_Assoc_Strategy_Many');

class Cg_Template_Assoc_Strategy_ManyToMany extends Cg_Template_Assoc_Strategy_Many {
    
    function _doShowGenModelMethods() {
        parent::_doShowGenModelMethods();
        extract(get_object_vars($this));
        
?>
<?php   if (($imn = $this->prop->getIdsMemberName()) !== false) { ?>

    function get<?php $this->d($ucSingle); ?>Ids() {
        if ($this-><?php $this->d($imn) ?> === false) {
            $mapper = & $this->getMapper();
            $mapper->loadAssocNNIdsFor($this, <?php $this->str($relationId); ?>);
        }
        return $this-><?php $this->d($imn) ?>;
    }
    
    function set<?php $this->d($ucSingle); ?>Ids($<?php $this->d($single); ?>Ids) {
        if (!is_array($<?php $this->d($single); ?>Ids)) trigger_error('$<?php $this->d($single); ?>Ids must be an array', E_USER_ERROR);
        $this-><?php $this->d($imn); ?> = $<?php $this->d($single); ?>Ids;
        <?php $this->d($varId); ?> = false; 
    }
<?php   } ?>
    
    function clear<?php $this->d($ucPlural); ?>() {
        <?php $this->d($varId); ?> = array();
<?php   if (($imn = $this->prop->getIdsMemberName()) !== false) { ?>
        $this-><?php $this->d($imn) ?> = false;
<?php   }?>
    }               
<?php       
    }
    
    function _showLinkBackCode() {
?>        
<?php   if (strlen($this->mirrorVar)) { ?>
        if (is_array($<?php $this->d($this->single)?>-><?php $this->d($this->mirrorVar); ?>) && !Ae_Util::sameInArray($this, $<?php $this->d($this->single)?>-><?php $this->d($this->mirrorVar); ?>)) {
                $<?php $this->d($this->single)?>-><?php $this->d($this->mirrorVar); ?>[] = & $this;
        }
<?php   } ?>
<?php  
    }
    
    function _doShowStoreDownstandingPart() {
        return false;
    }
    
    function _doShowStoreNNPart() {

        if ($this->prop->isManyToMany()) {
?>
<?php       if (strlen($imn = $this->prop->getIdsMemberName())) { ?>
        
        if (is_array($this-><?php $this->d($this->var); ?>) || is_array($this-><?php $this->d($imn); ?>)) {
            $rel = & $mapper->getRelation(<?php $this->str($this->relationId); ?>);
            if (!$this->_autoStoreNNRecords($this-><?php $this->d($this->var); ?>, $this-><?php $this->d($imn); ?>, $rel->fieldLinks, $rel->fieldLinks2, $rel->midTableName, <?php $this->str($this->plural); ?>)) 
                $res = false;
        }
<?php       } else { ?>
        if (is_array($this-><?php $this->d($this->var); ?>)) {
            $rel = & $mapper->getRelation(<?php $this->str($this->relationId); ?>);
            $ids = false;
            if (!$this->_autoStoreNNRecords($this-><?php $this->d($this->var); ?>, $ids, $rel->fieldLinks, $rel->fieldLinks2, $rel->midTableName, <?php $this->str($this->plural); ?>)) 
                $res = false;
        }
<?php       } ?>            
<?php   
        return true;
        
        } else return false;
        
    }
    
    
}

?>
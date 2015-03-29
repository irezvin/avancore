<?php

class Ac_Cg_Template_Assoc_Strategy_ManyToMany extends Ac_Cg_Template_Assoc_Strategy_Many {
    
    function getGuessMap() {
        $res = array_merge(parent::getGuessMap(), array(
            'loadDestIdsMapperMethod' => 'load{Single}IdsFor',
            'getDestIdsMethod' => 'get{Single}Ids',
            'setDestIdsMethod' => 'set{Single}Ids',
            'clearDestObjectsMethod' => 'clear{Plural}',
        ));
        if (!$this->canLoadDest) $res['loadDestIdsMapperMethod'] = null;
        return $res;
    }
    
    function _doShowGenModelMethods() {
        parent::_doShowGenModelMethods();
        extract(get_object_vars($this));
        
?>
<?php   if (($imn = $this->prop->getIdsMemberName()) !== false) { ?>

    function get<?php $this->d(ucfirst($this->prop->getIdsPropertyName())); ?>() {
        if ($this-><?php $this->d($imn) ?> === false) {
<?php       if ($this->canLoadDest) { ?>
            $this->mapper->load<?php $this->d(ucfirst($this->prop->getIdsPropertyName())); ?>For(<?php echo $this->relationTargetExpression; ?>);
<?php       } else { ?>            
            return array();
<?php       } ?>
        }
        return $this-><?php $this->d($imn) ?>;
    }
    
    function set<?php $this->d(ucfirst($this->prop->getIdsPropertyName())); ?>($<?php $this->d($this->prop->getIdsPropertyName()); ?>) {
        if (!is_array($<?php $this->d($this->prop->getIdsPropertyName()); ?>)) trigger_error('$<?php $this->d($this->prop->getIdsPropertyName()); ?> must be an array', E_USER_ERROR);
        $this-><?php $this->d($imn); ?> = $<?php $this->d($this->prop->getIdsPropertyName()); ?>;
        <?php $this->d($this->loadedId); ?> = false;
        <?php $this->d($varId); ?> = false; 
    }
<?php   } ?>
    
    function clear<?php $this->d($ucPlural); ?>() {
        <?php $this->d($varId); ?> = array();
        <?php $this->d($this->loadedId); ?> = true;
<?php   if (($imn = $this->prop->getIdsMemberName()) !== false) { ?>
        $this-><?php $this->d($imn) ?> = false;
<?php   }?>
    }               
<?php       
    }
    
    function _showLinkBackCode() {
?>        
<?php   if (strlen($this->mirrorVar)) { ?>
        if (is_array($<?php $this->d($this->single)?>-><?php $this->d($this->mirrorVar); ?>) && !Ac_Util::sameInArray(<?php echo $this->relationTargetExpression; ?>, $<?php $this->d($this->single)?>-><?php $this->d($this->mirrorVar); ?>)) {
                $<?php $this->d($this->single)?>-><?php $this->d($this->mirrorVar); ?>[] = <?php echo $this->relationTargetExpression; ?>;
        }
<?php   } ?>
<?php  
    }
    
    function _doShowGenMapperMethods() {
        parent::_doShowGenMapperMethods();
        extract(get_object_vars($this));
?>
<?php if ($this->canLoadDest) { ?>

    /**
     * @param <?php $this->d($thisClass); ?>|array <?php $this->d($idThisPlural); ?> 
     */
     function load<?php $this->d(ucfirst($this->prop->getIdsPropertyName())); ?>For(<?php $this->d($idThisPlural); ?>) {
        $rel = $this->getRelation(<?php $this->str($relationId); ?>);
        return $rel->loadDestNNIds(<?php $this->d($idThisPlural); ?>); 
    }
    
<?php } ?>
<?php

    }
    
    
}


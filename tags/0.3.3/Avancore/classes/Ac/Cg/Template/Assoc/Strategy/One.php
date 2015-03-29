<?php

class Ac_Cg_Template_Assoc_Strategy_One extends Ac_Cg_Template_Assoc_Strategy {
    
    function getGuessMap() {
        return array_merge(parent::getGuessMap(), array(
            'getDestObjectMethod' => 'get{Single}',
            'setDestObjectMethod' => 'set{Single}',
            'clearDestObjectMethod' => 'clear{Single}',
        ));
    }
    
    function _doShowGenModelMethods() {
        extract(get_object_vars($this));
?>        
    
    /**
     * @return <?php $this->d($prop->className); ?> 
     */
    function get<?php $this->d($ucSingle); ?>() {
        if (<?php $this->d($varId); ?> === false) {
<?php       if ($this->canLoadDest) { ?>
            $this->mapper->load<?php echo $this->ucOtherPlural; ?>For(<?php echo $this->relationTargetExpression; ?>);
<?php       } else { ?>
            return null;
<?php       } ?>            
        }
        return <?php $this->d($varId); ?>;
    }
    
    /**
     * @param <?php $this->d($prop->className); ?> $<?php $this->d($this->single); ?> 
     */
    function set<?php $this->d($ucSingle); ?>($<?php $this->d($this->single); ?>) {
        if ($<?php $this->d($this->single); ?> === false) $this-><?php $this->d($this->var); ?> = false;
        elseif ($<?php $this->d($this->single); ?> === null) $this-><?php $this->d($this->var); ?> = null;
        else {
            if (!is_a($<?php $this->d($this->single); ?>, <?php $this->str($prop->className); ?>)) trigger_error('$<?php $this->d($this->single); ?> must be an instance of <?php $this->d($prop->className); ?>', E_USER_ERROR);
            if (!is_object($this-><?php $this->d($this->var); ?>) && !Ac_Util::sameObject($this-><?php $this->d($this->var); ?>, $<?php $this->d($this->single); ?>)) { 
                $this-><?php $this->d($this->var); ?> = $<?php $this->d($this->single); ?>;
            }
        }
    }
    
    function clear<?php $this->d($ucSingle); ?>() {
        $this-><?php $this->d($this->single); ?> = null;
    }

<?php   if ($this->canCreateDest) { ?>
    /**
     * @return <?php $this->d($prop->className); ?>  
     */
    function create<?php $this->d($ucSingle); ?>($values = array(), $isReference = false) {
        $m = $this->getMapper(<?php $this->str($this->prop->mapperClass); ?>);
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->set<?php $this->d($ucSingle); ?>($res);
        return $res;
    }

<?php   } ?>    
<?php        
    }
    
    function _doShowInheritedGenModelMethods() {
        extract(get_object_vars($this));
        
?>        
    
    /**
     * @return <?php $this->d($prop->className); ?> 
     */
    function get<?php $this->d($ucSingle); ?>() {
        return parent::get<?php $this->d($ucSingle); ?>();
    }
    
    /**
     * @param <?php $this->d($prop->className); ?> $<?php $this->d($this->single); ?> 
     */
    function set<?php $this->d($ucSingle); ?>($<?php $this->d($this->single); ?>) {
        if ($<?php $this->d($this->single); ?> && !is_a($<?php $this->d($this->single); ?>, <?php $this->str($prop->className); ?>)) 
            trigger_error('$<?php $this->d($this->single); ?> must be an instance of <?php $this->d($prop->className); ?>', E_USER_ERROR);
        return parent::set<?php $this->d($ucSingle); ?>($<?php $this->d($this->single); ?>);
    }
    
<?php   if ($this->canCreateDest) { ?>
    /**
     * @return <?php $this->d($prop->className); ?>  
     */
    function create<?php $this->d($ucSingle); ?>($values = array(), $isReference = false) {
        return parent::create<?php $this->d($ucSingle); ?>($values, $isReference);
    }

<?php   } ?>    
<?php        
    }

}


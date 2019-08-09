<?php

class Ac_Cg_Template_Assoc_Strategy_Many extends Ac_Cg_Template_Assoc_Strategy {
    
    function getGuessMap() {
        return array_merge(parent::getGuessMap(), array(
            'listDestObjectsMethod' => 'list{Plural}',
            'countDestObjectsMethod' => 'count{Plural}',
            'getDestObjectMethod' => 'get{Single}',
            'addDestObjectMethod' => 'add{Single}',
            'isDestLoadedMethod' => 'is{Plural}Loaded',
        ));
    }
    
    function _doShowGenModelMethods() {
        extract(get_object_vars($this));
        
?>
<?php if ($this->count) { ?>

    function count<?php $this->d($ucPlural); ?>() {
        if (is_array(<?php $this->d($varId); ?>)) return count(<?php $this->d($varId); ?>);
<?php   if ($this->canLoadDest) { ?>
        if (<?php $this->d($countId); ?> === false) {
            $this->mapper->loadAssocCountFor(<?php echo $this->relationTargetExpression; ?>, <?php $this->str($relationId); ?>);
        }
        return <?php $this->d($countId); ?>;
<?php   } else { ?>
        return 0;
<?php   } ?>        
    }
<?php } ?>

    function list<?php $this->d($ucPlural); ?>() {
<?php   if ($this->canLoadDest) { ?>
        if (!<?php $this->d($this->loadedId); ?>) {
            $this->mapper->load<?php echo $this->ucOtherPlural; ?>For(<?php echo $this->relationTargetExpression; ?>);
        }
<?php   } else { ?>
        if (!is_array(<?php $this->d($varId); ?>)) <?php $this->d($varId); ?> = array();
<?php   } ?>
        return array_keys(<?php $this->d($varId); ?>);
    }
    
    /**
     * @return bool
     */
    function is<?php $this->d($ucPlural); ?>Loaded() {
        return <?php $this->d($loadedId); ?>;
    }
    
    /**
     * @return <?php $this->d($prop->className); ?> 
     */
    function get<?php $this->d($ucSingle); ?>($id) {
<?php   if ($this->canLoadDest) { ?>
        if (!<?php $this->d($this->loadedId); ?>) {
            $this->mapper->load<?php echo $this->ucOtherPlural; ?>For(<?php echo $this->relationTargetExpression; ?>);
        }
<?php   } ?>        
        if (!isset(<?php $this->d($varId); ?>[$id])) trigger_error ('No such <?php echo addcslashes($otherModel->singleCaption, '\''); ?>: \''.$id.'\'', E_USER_ERROR);
        return <?php $this->d($varId); ?>[$id];
    }
    
    /**
     * @return <?php $this->d($prop->className); ?> 
     */
    function get<?php $this->d($ucPlural); ?>Item($id) {
        return $this->get<?php $this->d($ucSingle); ?>($id);
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

<?php   if ($this->canCreateDest) { ?>
    /**
     * @return <?php $this->d($prop->className); ?>  
     */
    function create<?php $this->d($ucSingle); ?>($values = array()) {
        $m = $this->getMapper(<?php $this->str($this->prop->mapperClass); ?>);
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->add<?php $this->d($ucSingle); ?>($res);
        return $res;
    }
    
<?php   } ?>
<?php

    }
    
    function _showLinkBackCode() {
?>        
<?php   if (strlen($this->mirrorVar)) { ?>
        $<?php $this->d($this->single)?>-><?php $this->d($this->mirrorVar); ?> = <?php echo $this->relationTargetExpression; ?>;
<?php   } ?>
<?php  
    }
    
    function _doShowInheritedGenModelMethods() {
        extract(get_object_vars($this));
        
?>        
    
    /**
     * @return <?php $this->d($prop->className); ?> 
     */
    function get<?php $this->d($ucSingle); ?>($id) {
        return parent::get<?php $this->d($ucSingle); ?>($id);
    }
    
    /**
     * @return <?php $this->d($prop->className); ?> 
     */
    function get<?php $this->d($ucPlural); ?>Item($id) {
        return parent::get<?php $this->d($ucPlural); ?>Item($id);
    }
    
    /**
     * @param <?php $this->d($prop->className); ?> $<?php $this->d($single); ?> 
     */
    function add<?php $this->d($ucSingle); ?>($<?php $this->d($single); ?>) {
        if (!is_a($<?php $this->d($this->single); ?>, <?php $this->str($prop->className); ?>))
            trigger_error('$<?php $this->d($this->single); ?> must be an instance of <?php $this->d($prop->className); ?>', E_USER_ERROR);
        return parent::add<?php $this->d($ucSingle); ?>($<?php $this->d($single); ?>);
    }
    
<?php   if ($this->canCreateDest) { ?>
    /**
     * @return <?php $this->d($prop->className); ?>  
     */
    function create<?php $this->d($ucSingle); ?>($values = array()) {
        return parent::create<?php $this->d($ucSingle); ?>($values);
    }

<?php   } ?>    

<?php        
    }
    
    
}


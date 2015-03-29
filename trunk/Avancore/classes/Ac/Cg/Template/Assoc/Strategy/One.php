<?php

class Ac_Cg_Template_Assoc_Strategy_One extends Ac_Cg_Template_Assoc_Strategy {
    
    
    
    function _doShowGenModelMethods() {
        extract(get_object_vars($this));
        
?>        
    
    /**
     * @return <?php $this->d($prop->className); ?> 
     */
    function get<?php $this->d($ucSingle); ?>() {
        if (<?php $this->d($varId); ?> === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocFor($this, <?php $this->str($relationId); ?>);
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
    
<?php        
    }

}


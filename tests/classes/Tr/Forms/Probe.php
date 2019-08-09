<?php

abstract class Tr_Forms_Probe extends Tr_Probe {
    
    /**
     * @return Ac_Form_Control
     */
    function getControl() {
        $res = $this->probeList->getNode()->getObject();
        if (!($res instanceof Ac_Form_Control)) 
            throw new Exception("Source of the probe '{$this}' is supposed to be an instance of Ac_Form_Control, turned out to be ".Ac_Util::typeClass($res));
        return $res;
    }
    
    function __toString() {
        return get_glass($this).'#'.$this->key;
    }
    
}
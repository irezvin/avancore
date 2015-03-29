<?php

class Tr_Forms_ResultProbe_HasContainer extends Tr_Forms_ResultProbe {
    
    protected $key = 'hasContainer';
    
    public function doGetResult() {
        $res = is_object($this->getSource()->getDomNode());
        return $res;
    }
    
}
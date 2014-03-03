<?php

class Tr_Forms_ObjectProbe_HasContainer extends Tr_Forms_ObjectProbe {
    
    protected $key = 'hasContainer';
    
    public function doGetResult() {
        if ($this->getControl()->enabled) $res = true;
            else $res = false;
        return $res;
    }
    
}
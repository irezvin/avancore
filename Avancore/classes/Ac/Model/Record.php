<?php

class Ac_Model_Record extends Ac_Model_Object {
    
    protected function listOwnProperties() {
        return $this->mapper->getColumnNames();
    }
    
}
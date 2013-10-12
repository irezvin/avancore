<?php

class Ac_Result_Writer_Merge extends Ac_Result_Writer_WithCharset {
    
    protected function implWrite(Ac_Result $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        // The idea behind this implementation is to avoid creation of unused placeholders

        // listPlaceholders() does not instantiate default placeholders
        if ($t) {
            $tp = $t->listPlaceholders(); 
        
            // getPlaceholders(true) returns only instantiated placeholders
            $usedPlaceholders = $r->getPlaceholders(true);
            foreach (array_intersect_key($usedPlaceholders, array_flip($tp)) as $id => $placeholder) { 
               $t->getPlaceholder($id)->mergeWith($placeholder);
            }
            if ($t && $t instanceof Ac_Result_Http_Abstract) {
                if ($t->getContentType() === false) 
                    $t->setContentType($r->getContentType());
            }
        }
        
        parent::implWrite($r, $t, $s);
    }    
    
}
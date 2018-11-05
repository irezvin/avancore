<?php

class Ac_Result_Writer_Merge extends Ac_Result_Writer_WithCharset {
    
    protected function implWrite(Ac_Result $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        // The idea behind this implementation is to avoid creation of unused placeholders

        // listPlaceholders() does not instantiate default placeholders
        if ($t) {
            $tp = $t->listPlaceholders(); 
            // getPlaceholders(true) returns only instantiated placeholders
            $usedPlaceholders = $r->getPlaceholders(true);
            $targetPlaceholders = $t->listPlaceholders();
            foreach ($usedPlaceholders as $id => $placeholder) {
                if (in_array($id, $targetPlaceholders)) {
                    $t->getPlaceholder($id)->mergeWith($placeholder);
                } else {
                    $t->addPlaceholder(clone $placeholder, $id);
                }
            }
            if ($t && $t instanceof Ac_Result_Http_Abstract && $r instanceof Ac_Result_Http_Abstract) {
                if ($t->getContentType() === false) {
                    $t->setContentType($r->getContentType());
                }
            }
        }
        
        parent::implWrite($r, $t, $s);
    }    
    
}
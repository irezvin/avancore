<?php

class Ac_Result_Writer_HtmlMerge extends Ac_Result_Writer_Plain {
    
    protected function implWrite(Ac_Result $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        // The idea behind this implementation is to avoid creation of unused placeholders

        // listPlaceholders() does not instantiate default placeholders
        $tp = $t->listPlaceholders(); 
        
        // getPlaceholders(true) returns only instantiated placeholders
        $usedPlaceholders = $r->getPlaceholders(true);
        foreach (array_intersect_key($usedPlaceholders, array_flip($tp)) as $id => $placeholder) { 
           $t->getPlaceholder($id)->mergeWith($placeholder);
        }
        parent::implWrite($r, $t, $s);
    }    
    
}
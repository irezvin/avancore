<?php

class Ac_Result_Writer_HtmlMerge extends Ac_Result_Writer_Plain {
    
    protected function implWrite(Ac_Result $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        $tp = $t->getPlaceholders();
        foreach (array_intersect_key($r->getPlaceholders(), $tp) as $id => $placeholder) {
           $tp[$id]->mergeWith($placeholder);
        }
        parent::implWrite($r, $t, $s);
    }    
    
}
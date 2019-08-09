<?php

class Ac_Result_Writer_Plain extends Ac_Result_Writer {
    
    protected function requiresTarget() {
        return false;
    }
    
    protected function implWrite(Ac_Result $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        $r->echoContent();
    }
    
}
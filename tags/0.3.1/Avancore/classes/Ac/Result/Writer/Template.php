<?php

class Ac_Result_Writer_Template extends Ac_Result_Writer {
    
    protected function requiresTarget() {
        return false;
    }
    
    protected function implWrite(Ac_Result $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        if (!$r instanceof Ac_Result_Template) 
            throw Ac_E_InvalidCall::wrongClass('r', $r, 'Ac_Result_Template');
        $renderedResult = $r->render();
        $writer = $renderedResult->getWriter();
        if ($t) $writer->setTarget($t);
        if ($s) $writer->setStage ($s);
        $writer->write();
    }
    
}
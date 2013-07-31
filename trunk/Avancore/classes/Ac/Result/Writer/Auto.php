<?php

class Ac_Result_Writer_Auto extends Ac_Result_Writer {
    
    protected function requiresTarget() {
        return false;
    }
    
    protected function applyWriter($writer, $r, $t, $s) {
        $writer = Ac_Prototyped::factory($writer, 'Ac_Result_Writer');
        if ($s) $writer->setStage($s);
        if ($t) $writer->setTarget($t);
        $writer->setSource($r);
        $writer->write();
    }
    
    protected function implWrite(Ac_Result $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        $proto = array('class' => 'Ac_Result_Writer_Merge');
        if ($r instanceof Ac_Result_Html) {
            if ($t && $t instanceof Ac_Result_Html) {
                $proto['class'] = 'Ac_Result_Writer_HtmlMerge';
            } else {
                $proto['class'] = 'Ac_Result_Writer_RenderHtml';
            }
        }
        elseif ($r instanceof Ac_Result_Http) {
            $proto['class'] = 'Ac_Result_Writer_Replace';
            if (!$t) $proto['class'] = 'Ac_Result_Writer_HttpOut';
        }
        
        return $this->applyWriter($proto, $r, $t, $s);
    }
    
}
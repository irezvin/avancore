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
        if ($r instanceof Ac_Result_Template) {
            $proto['class'] = 'Ac_Result_Writer_Template';
        } elseif ($r instanceof Ac_Result_Html && !$t) {
            $proto['class'] = 'Ac_Result_Writer_RenderHtml';
        } elseif ($r instanceof Ac_Result_Http) {
            if (!$t) $proto['class'] = 'Ac_Result_Writer_HttpOut';
            else { 
                $proto['class'] = 'Ac_Result_Writer_Replace';
                $proto['replaceAll'] = true;
            }
        }
        return $this->applyWriter($proto, $r, $t, $s);
    }
    
}
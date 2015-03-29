<?php

class Tr_Forms_Dumper implements Tr_I_Dumper {
    
    function dump(Tr_Node $node) {
        $ob = $node->getObject();
        $res = false;
        if ($ob instanceof Ac_Form_Control) {
            $res = get_class($ob);
            if (strlen($ob->name)) $res .= '#'.$ob->name;
        }
        return $res;
    }
    
}
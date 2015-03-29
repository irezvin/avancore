<?php

class Ac_Test_Tree_Dumper implements Tr_I_Dumper {
    
    var $dumpStructure = false;
    
    function dump(Tr_Node $node) {
        if ($node instanceof Ac_Test_Tree_Node) {
            $data = $node->data;
            if ($this->dumpStructure) $data['structure'] = $node->getStructure();
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            return Ac_Util::typeClass($node);
        }
    }
}

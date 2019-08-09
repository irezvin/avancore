<?php

class Tr_Forms_Scanner implements Tr_I_ScannerImpl {
    
    /**
     * @return Tr_Node
     */
    function createRootNode($object) {
        $res = new Tr_Node($object);
        $res->setDumper(new Tr_Forms_Dumper);
        return $res;
    }
    
    /**
     * @return Ac_Form_Control
     */
    function getObject(Tr_Node $node) {
        return $node->getObject();
    }
    
    function scanNode(Tr_Node $node) {
        $ob = $node->getObject();
        $obj = $this->getObject($node);
        if ($obj instanceof Ac_Form_Control_Composite) {
            foreach ($obj->listControls() as $i) {
                $node->createNode($obj->getControl($i));
            }
        }
    }

}
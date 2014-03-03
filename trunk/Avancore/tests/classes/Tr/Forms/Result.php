<?php

class Tr_Forms_Result {
    
    /**
     * @var Tr_Node
     */
    protected $node = false;
    
    /**
     * @return DOMNode
     */
    protected $domNode = false;
    
    /**
     * @var Tr_Forms_Result_Root
     */
    protected $root = false;
    
    function __construct(Tr_Forms_Result_Root $root, Tr_Node $node) {
        $this->root = $root;
        $this->node = $node;
    }
    
    /**
     * @return Tr_Forms_Result_Root
     */    
    function getRootResult() {
        return $this->root;
    }
    
    /**
     * @return Tr_Node
     */
    function getNode() {
        return $this->Node;
    }
    
    /**
     * @return Ac_Form_Control
     */
    function getControl() {
        $res = $this->node->getObject();
        if (!($res instanceof Ac_Form_Control))
            throw new Exception("Object of node '{$this->node}' is supposed to be an instance of Ac_Form_Control, turned out to be " . Ac_Util::typeClass($res));
        return $res;
    }
    
    /**
     * @return DOMNode
     */
    function getDomNode($again = false) {
        if (($this->domNode === false) || $again) {
!            $id = $this->getControl()->getId();
            if (strlen($id)) {
                $this->domNode = $this->getRootResult()->getDom()->getElementById($id);
            }
        }
        return $this->domNode;
    }
    
}
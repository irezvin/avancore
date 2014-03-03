<?php

class Tr_Forms_ResultProvider implements Tr_I_ResultProvider {
    
    /**
     * @var Tr_Node $node
     */
    protected $node = false;
    
    public function setNode(Tr_Node $node) {
        $this->node = $node;
        $this->prepareNode();
    }
    
    public function createResult() {
        if (!$this->node) throw new Exception("Cannot provideResult() with no node: setNode() first");
        if ($this->node->isRoot()) {
            $rootResult = new Tr_Forms_Result_Root($this->node);
        } else {
            $rootResult = $this->node->getRoot()->getResult()->getRootResult();
        }
        $res = new Tr_Forms_Result($rootResult, $this->node);
        return $res;
    }
    
    /**
     * @return Ac_Form_Control
     */
    function getControl() {
        $res = $this->node->getObject();
        if (!($res instanceof Ac_Form_Control)) 
            throw new Exception("Object of node '{$this->node}' is supposed to be an instance of Ac_Form_Control, turned out to be ".Ac_Util::typeClass($res));
        return $res;
    }
    
    function prepareNode() {
        if (!strlen($this->getControl()->id)) {
            $this->getControl()->autoId = true;
        }
    }
    
}
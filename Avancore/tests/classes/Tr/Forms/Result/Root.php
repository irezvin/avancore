<?php

class Tr_Forms_Result_Root {
    
    /**
     * @var DOMDocument
     */
    protected $dom = false;
    
    /**
     * @var Tr_Node
     */
    protected $node = false;
        
    function __construct(Tr_Node $node) {
        $this->node = $node;
    }
    
    function calcStrResult() {
        var_dump('!!!');
        $pres = $this->node->getObject()->fetchPresentation();
        return $pres;
    }
    
    /**
     * @return DOMDocument
     */
    function getDom() {
        if (!$this->dom) {
            $this->dom = new DOMDocument;
            $strResult = $this->calcStrResult();
            $this->dom->loadHTML('<'.'?xml encoding="UTF-8">'.$strResult);
        }
        return $this->dom;
    }
    
    
    
}
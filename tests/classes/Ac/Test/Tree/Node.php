<?php

class Ac_Test_Tree_Node extends Tr_Node {
    
    var $data = array();

    var $left = 0;
    
    var $right = 0;
    
    var $ordering = 0;
    
    var $parentTitle = '';
    
    var $canOrderUp = null;
    
    var $canOrderDown = null;
    
    protected $childClass = 'Ac_Test_Tree_Node';
    
    protected $processed = false;
    
    function __construct($object, $extra = false, $index = false, \Tr_Node $parent = null) {
        if (is_array($extra)) {
            Ac_Accessor::setObjectProperty($this, $extra);
            $extra = false;
        }
        parent::__construct($object, $extra, $index, $parent);
    }
    
    function getStructure() {
        return Ac_Accessor::getObjectProperty($this, array('left', 'right', 'ordering', 'parentTitle', 
            'canOrderUp', 'canOrderDown'));
    }
    
    function dumpPre($withPre = true, $withStructure = null) {
        $iter = new RecursiveTreeIterator($this->createSuperNode());
        if (!is_null($withStructure)) $this->getDumper ()->dumpStructure = $withStructure;
        if ($withPre) echo "<pre>";
        foreach ($iter as $line) echo("\n".$line);
        if ($withPre) echo "</pre>";
    }
    
    function prepare() {
        $root = $this->getRoot();
        if ($root->processed) return;
        $root->processed = true;
        $pro = new Ac_Test_Tree_Processor;
        $pro->doAll($root, 'process');
    }
    
    function getDepth() {
        $res = 0;
        for ($n = $this->getParent(); $n; $n = $n->getParent()) $res++;
        return $res;
    }

    function getStructureCompare(array $titleToIdMap, $strData = false, 
        $data = false) {
        
        if ($data === false) $data = $this->data;
        
        $key = 'node_'.$data['title'];
        
        if ($strData === false)
            $strData = $this->getStructure();
        
        
        if (array_key_exists('parentTitle', $strData)) {
            if (!is_null($strData['parentTitle'])) {
                $pt = $strData['parentTitle'];
                $parentId = isset($this->titleToIdMap[$pt])? 
                    $this->titleToIdMap[$pt] : false;
            } else {
                $parentId = null;
            }
            $strData['parentId'] = $parentId;
        }
            
        $match = true;
        foreach ($strData as $k => $v) {
            if (array_key_exists($k, $data)) {
                $arr[$k] = array($data[$k], $v);
                
                // relax types for string vs numeric since DB often returns arrays
                // as strings
                if (is_string($k) && is_numeric($v)) $v = (string) $v;
                elseif (is_numeric($k) && is_string($v)) $k = (string) $k;
                
                if ($data[$k] !== $v) $match = false;
            }
        }
        $arr['_match'] = $match;
        $arr['_node'] = $this;
        $res[$key] = $arr;
        return $res;
    }
    
    function testModelObject(Ac_Model_Object $item, array $strToItemMap, 
        array $titleToIdMap, 
        array & $matchData = array(),
        $compareToStructure = true) {
        
        $itemData = Ac_Accessor::getObjectProperty($item, array_values($strToItemMap));
        $strFromItemData = Ac_Test_Tree_TextScanner::remap($itemData, $strToItemMap);
        if ($compareToStructure) 
            $matchData = $this->getStructureCompare($titleToIdMap, false, $data);
        else {
            $matchData = $this->getStructureCompare($titleToIdMap, $data, false);
        }
        
        $res = (bool) $matchData['_match'];
        
        return $res;
    }
    
}

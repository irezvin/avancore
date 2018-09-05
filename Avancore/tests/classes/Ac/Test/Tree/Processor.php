<?php

class Ac_Test_Tree_Processor {
    
    var $orderingStartsAt = 1;
    
    var $left = 0;
    
    function setRight(Ac_Test_Tree_Node $node, $right) {
        $node->right = $right;
        if ($p = $node->getParent()) $this->setRight($p, $right + 1);
    }
    
    function process(Ac_Test_Tree_Node $node) {
        $parent = $node->getParent();
        if (($i = $node->getIndex()) > 0) $prev = $parent->getChild ($i - 1);
            else $prev = null;
        if (!$parent) $node->left = 0;
        elseif ($prev) {
            $node->left = $prev->right + 1;
        } elseif ($parent) {
            $node->left = $parent->left + 1;
        }
        $node->ordering = $i + $this->orderingStartsAt;
        $node->right = $node->left + 1;
        if ($parent) {
            $this->setRight($parent, $node->right + 1);
            $node->parentTitle = $parent->data['title'];
        }
        $node->canOrderUp = $i > 0;
        $node->canOrderDown = $parent && $i < is_countable($parent->getChildren()) && count($parent->getChildren());
    }
    
    function getTitleToIdMap(Ac_Test_Tree_Node $node, array $link) {
        if (isset($node->data['id'])) {
            var_dump($node->data);
            $link[0][$node->data['title']] = $node->data['id'];
        }
    }
    
    function getStructureCompare(Ac_Test_Tree_Node $node, array $titleToIdMap) {
        return $node->getStructureCompare($titleToIdMap);
    }
    
    function dumpWithIndent(Ac_Test_Tree_Node $node) {
        echo "\n".str_repeat(" ", $node->getDepth()).$node->data['title'];
    }
    
    function doAll($node, $method, $_ = null) {
        if (!is_array($method)) $method = array($this, $method);
        $iter = new RecursiveIteratorIterator($node->createSuperNode(), 
            RecursiveIteratorIterator::SELF_FIRST);
        $args = func_get_args();
        array_shift($args);
        array_shift($args);
        $res = array();
        foreach ($iter as $node) {
            $m = $args;
            array_unshift($m, $node);
            $res[] = call_user_func_array($method, $m);
        }
        return $res;
    }
    
}
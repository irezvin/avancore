<?php

class Ac_Test_Tree extends Ac_Test_Base {
    
    protected $bootSampleApp = true;
    
    function setUp() {
        $this->getAeDb()->query("DELETE FROM #__tree_combos");
        $this->getAeDb()->query("ALTER TABLE #__tree_combos AUTO_INCREMENT=1");
        
        $this->getAeDb()->query("DELETE FROM #__tree_nested_sets");
        $this->getAeDb()->query("DELETE FROM #__tree_records");
        $this->getAeDb()->query("ALTER TABLE #__tree_records AUTO_INCREMENT=1");
        
        $this->getAeDb()->query("DELETE FROM #__tree_adjacent");
        $this->getAeDb()->query("ALTER TABLE #__tree_adjacent AUTO_INCREMENT=1");
    }
    
    function assertRowsMatch(array $a, array $b, $message = '%s') {
        if (count($a) && (count($a) == count($b))) {
            foreach ($a as & $aRow) {
                $aRow = array_values($aRow);
            }
            foreach ($b as & $bRow) ksort($bRow);
        }
        return $this->assertEqual($a, $b, $message);
    }
    
    function _testCombo() {
        
        $s = $this->getSampleApp();
        $mapper = $s->getSampleTreeComboMapper();
        $mixable = $mapper->getMixable('treeMapper');
        $this->assertIsA($mixable, 'Ac_Model_Tree_NestedSetsMapper');
        if ($mixable instanceof Ac_Model_Tree_NestedSetsMapper) {
            $ns = $mixable->getNestedSets();
            $this->assertTrue($ns->idIsAutoInc);
            if ($this->assertEqual($id = $mixable->getRootNodeId(), 1)) {
                $rec = $mapper->loadById($id);
                if ($this->assertTrue($rec && $rec instanceof Ac_Model_Object)) {
                    $this->assertEqual($rec->title, 'root');
                    $this->assertEqual($rec->tag, 999);
                }
            }
            $child1 = $mapper->createRecord();
            $child1->title = 'child1';
            $child1->tag = 1;

            $child1->store();
            
            $currRows = $this->getAeDb()->fetchArray(
                'SELECT id, title, leftCol, rightCol, parentId, ordering, depth '
                . 'FROM #__tree_combos ORDER BY leftCol ASC'
            );
            
            $this->assertRowsMatch(
                $currRows,
                array(
                    array(1, 'root', 0, 3, null, 0, 0),
                    array(2, 'child1', 1, 2, 1, 0, 1),
                )
            );
            
        }
    }   
      
    function testNs() {
        
        $s = $this->getSampleApp();
        $mapper = $s->getSampleTreeRecordMapper();
        $mixable = $mapper->getMixable('treeMapper');
        $this->assertIsA($mixable, 'Ac_Model_Tree_NestedSetsMapper');
        if ($mixable instanceof Ac_Model_Tree_NestedSetsMapper) {
            $ns = $mixable->getNestedSets();
            $this->assertFalse($ns->idIsAutoInc);
            $this->assertEqual($id = $mixable->getRootNodeId(), 0);
            $child1 = $mapper->createRecord();
            $child1->title = 'child1';
            $child1->tag = 1;
            $this->assertTrue($child1->store());
            
            $currRows = $this->getAeDb()->fetchArray($q = 
                'SELECT id, comment, treeId, leftCol, rightCol, parentId, ordering, depth '
                . 'FROM #__tree_nested_sets ORDER BY leftCol ASC'
            );
            $this->assertRowsMatch(
                $currRows,
                array(
                    array(0, 'Sample_Tree_Record_Mapper', 1, 0, 3, null, 1, 0),
                    array(1, '', 1, 1, 2, 0, 1, 1),
                )
            );
            
            $child2 = $mapper->createRecord();
            $child2->title = 'child2';
            $child2->tag = 2;
            $this->assertTrue($child2->store());
            
            $currRows = $this->getAeDb()->fetchArray($q);
            $this->assertRowsMatch(
                $currRows,
                array(
                    array(0, 'Sample_Tree_Record_Mapper', 1, 0, 5, null, 1, 0),
                    array(1, '', 1, 1, 2, 0, 1, 1),
                    array(2, '', 1, 3, 4, 0, 2, 1),
                )
            );
            
            $this->assertFalse($child1->canOrderUp());
            $this->assertTrue($child1->canOrderDown());
            $this->assertTrue($child2->canOrderUp());
            $this->assertFalse($child2->canOrderDown());
            
            $child3 = $mapper->createRecord();
            $child3->title = 'child3';
            $child3->tag = 3;
            $this->assertTrue($child3->store());
            
            $this->assertFalse($child1->canOrderUp());
            $this->assertTrue($child1->canOrderDown());
            $this->assertTrue($child2->canOrderUp());
            $this->assertTrue($child2->canOrderDown());
            $this->assertTrue($child3->canOrderUp());
            $this->assertFalse($child3->canOrderDown());
            
            $child3->setOrdering(2);
            $child3->store();

            $currRows = $this->getAeDb()->fetchArray($q);
            $this->assertRowsMatch(
                $currRows,
                array(
                    array(0, 'Sample_Tree_Record_Mapper', 1, 0, 7, null, 1, 0),
                    array(1, '', 1, 1, 2, 0, 1, 1),
                    array(3, '', 1, 3, 4, 0, 2, 1),
                    array(2, '', 1, 5, 6, 0, 3, 1),
                )
            );
            
            $this->assertEqual($child2->id, 2);
            $this->assertEqual($child2->getOrdering(), 3);
            
        }
    }   
    
    function testAdjacent() {
        
        $s = $this->getSampleApp();
        $mapper = $s->getSampleTreeAdjacentMapper();
        $mixable = $mapper->getMixable('treeMapper');
        $this->assertIsA($mixable, 'Ac_Model_Tree_AdjacencyListMapper');
        if ($mixable instanceof Ac_Model_Tree_AdjacencyListMapper) {
            
            $this->assertEqual($mapper->getDefaultParentValue(), null);
            
            $child1 = $mapper->createRecord();
            $child1->title = 'child1';
            $child1->tag = 1;
            $this->assertTrue($child1->store());

            $this->assertEqual($child1->getOrdering(), 1);
            
            $child2 = $mapper->createRecord();
            $child2->title = 'child2';
            $child2->tag = 2;
            $this->assertTrue($child2->store());
            
            $q = 
                'SELECT id, title, parentId, ordering '
                . 'FROM #__tree_adjacent ORDER BY id ASC'
            ;
            
            
            $currRows = $this->getAeDb()->fetchArray($q);
            $this->assertRowsMatch(
                $currRows,
                array(
                    array(1, 'child1', null, 1),
                    array(2, 'child2', null, 2),
                )
            );
            
            $this->assertFalse($child1->canOrderUp());
            $this->assertTrue($child1->canOrderDown());
            $this->assertTrue($child2->canOrderUp());
            $this->assertFalse($child2->canOrderDown());
            
            $child3 = $mapper->createRecord();
            $child3->title = 'child3';
            $child3->tag = 3;
            $this->assertTrue($child3->store());
            
            $this->assertFalse($child1->canOrderUp());
            $this->assertTrue($child1->canOrderDown());
            $this->assertTrue($child2->canOrderUp());
            $this->assertTrue($child2->canOrderDown());
            $this->assertTrue($child3->canOrderUp());
            $this->assertFalse($child3->canOrderDown());
            
            $child3->setOrdering(2);
            
            $child3->store();

            $currRows = $this->getAeDb()->fetchArray($q);
            $this->assertRowsMatch(
                $currRows,
                array(
                    array(1, 'child1', null, 1),
                    array(2, 'child2', null, 3),
                    array(3, 'child3', null, 2),
                )
            ) ;
            
            $child2->load(); // this is required for adjacency lists!
            $this->assertEqual($child2->id, 2);
            $this->assertEqual($child2->getOrdering(), 3);
        }
    }   
    
}

abstract class ImplNsGetter extends Ac_Model_Tree_NestedSetsImpl {
    
    static function get(Ac_Model_Tree_NestedSetsImpl $foo, $prop) {
        return $foo->$prop;
    }
    
    static function call(Ac_Model_Tree_NestedSetsImpl $foo, $method, $args = array()) {
        return call_user_func_array(array($foo, $method), $args);
    }
    
}
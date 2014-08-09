<?php

class Ac_Test_Tree extends Ac_Test_Base {
    
    protected $bootSampleApp = true;

    function resetCombos() {
        $this->getAeDb()->query("DELETE FROM #__tree_combos");
        $this->getAeDb()->query("ALTER TABLE #__tree_combos AUTO_INCREMENT=1");
    }
    
    function resetNs() {
        $this->getAeDb()->query("DELETE FROM #__tree_nested_sets");
        $this->getAeDb()->query("DELETE FROM #__tree_records");
        $this->getAeDb()->query("ALTER TABLE #__tree_records AUTO_INCREMENT=1");
    }
    
    function resetAdj() {
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
    
    function assertTreeMatch($treeOrText1, $treeOrText2, $message = '%s') {
        if (!is_object($treeOrText1)) $treeOrText1 = $this->getTreeFromText($treeOrText1);
        if (!is_object($treeOrText2)) $treeOrText2 = $this->getTreeFromText($treeOrText2);
        if (!$treeOrText1 instanceof Ac_Test_Tree_Node) 
            throw new Exception("\$treeOrText1 must be Ac_Test_Tree_Node");
        if (!$treeOrText2 instanceof Ac_Test_Tree_Node) 
            throw new Exception("\$treeOrText2 must be Ac_Test_Tree_Node");
        
        $pro = new Ac_Test_Tree_Processor();
        
        ob_start();
        $pro->doAll($treeOrText1, 'dumpWithIndent');
        $a = ltrim(ob_get_clean(), "\n");
        
        ob_start();
        $pro->doAll($treeOrText2, 'dumpWithIndent');
        $b = ltrim(ob_get_clean(), "\n");
        
        if (!($res = $this->assertEqual($a, $b, $message))) {
?>
            <table>
                <tr><td style="vertical-align: top">
                    <pre><?php echo nl2br(htmlspecialchars($a)); ?></pre>
                </td></tr>
                <tr><td style="vertical-align: top">
                    <pre><?php echo nl2br(htmlspecialchars($b)); ?></pre>
                </td></tr>
            </table>
<?php
        }
        return $res;
    }
    
    function assertTreeStructureOk(Ac_Test_Tree_Node $root) {
        $pro = new Ac_Test_Tree_Processor;
        $titleToIdMap = array();
        $pro->doAll($root, 'getTitleToIdMap', array(& $titleToIdMap));
        $items = $pro->doAll($root, 'getStructureCompare', $titleToIdMap);
        $allItems = call_user_func_array('array_merge', $items);
        $ok = true;
        foreach ($allItems as $key => $strData) {
            if (!$strData['_match']) {
                $ok = false;
                foreach ($strData as $k => $v) {
                    if (is_array($v)) {
                        list ($a, $b) = $v;
                        if ($a !== $b) $this->assertIdentical($a, $b, "{$k} must match for node $key, but %s");
                    }
                }
                break;
            }
        }
        if ($ok) $this->assertTrue($ok);
        return $ok;
    }
    
    function assertTreeNodes(Ac_Test_Tree_Node $root, array $items, 
        array $strToItemMap, $compareToStructure = true) {
        
        $itemsByTitle = array();
        foreach ($items as $item) {
            $itemsByTitle['node_'.$item->title] = $item;
        }
        $pro = new Ac_Test_Tree_Processor;
        $titleToIdMap = array();
        $pro->doAll($root, 'getTitleToIdMap', array(& $titleToIdMap));
        $items = $pro->doAll($root, 'getStructureCompare', $titleToIdMap);
        $structure = call_user_func_array('array_merge', $items);
        $matching = array_intersect_key($structure, $itemsByTitle);
        if (count($itemsByTitle) != count($matching)) { // some items not found
            $notFound = array_diff_key($itemsByTitle, $structure);
        }
        $ok = true;
        if ($notFound) {
            $ok = false;
            $this->assertTrue(false, "Item(s) not found in structure: "
                .implode(", ", array_keys($notFound)));
        } else {
            $ok = true;
            foreach ($matching as $key => $strData) {
                $node = $strData['_node'];
                $match = $node->testModelObject($itemsByTitle[$key], $strToItemMap, 
                    $titleToIdMap, $matchData, $compareToStructure);
                if (!$match) {
                    $matchData = array_diff($matchData);
                    foreach ($matchData as $k => $v) {
                        if (is_array($v)) {
                            list ($a, $b) = $v;
                            if ($a !== $b) $this->assertIdentical($a, $b, "{$k} must match for node $key, but %s");
                        }
                    }
                    $ok = false;
                    break;
                }
            }
        }
        return $ok;
    }
    
    function testCombo() {
        $this->resetCombos();
        $s = $this->getSampleApp();
        $mapper = $s->getSampleTreeComboMapper();
        $mixable = $mapper->getMixable('treeMapper');
        $this->assertIsA($mixable, 'Ac_Model_Tree_ComboMapper');
        if ($mixable instanceof Ac_Model_Tree_ComboMapper) {
            $ns = $mixable->getNestedSets();
            $this->assertTrue($ns->idIsAutoInc);
            if ($this->assertEqual($rootId = $mixable->getRootNodeId(), 1)) {
                $rec = $mapper->loadById($rootId);
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
                    array(1, 'root', 0, 3, null, 1, 0),
                    array(2, 'child1', 1, 2, 1, 1, 1),
                )
            );

            $sample = array(
                'leftCol' => 1, 
                'rightCol' => 2, 
                'parentId' => $rootId, 
                'depth' => 1, 
                'ordering' => 1
            );
            
            $this->assertEqual(
                Ac_Accessor::getObjectProperty($child1, array_keys($sample)), $sample
            );
            
            $this->assertEqual($child1->getTreeImpl()->getParentNodeId(), $sample['parentId']);
            
            $child1->load();
            
            $this->assertEqual($child1->getTreeImpl()->getParentNodeId(), $sample['parentId']);
            
            $child1copy = $mapper->loadById($child1->id);
            
            $this->assertEqual($child1copy->getTreeImpl()->getParentNodeId(), $sample['parentId']);
            
            $root = $mapper->loadById($rootId);
            
            $this->assertFalse($root->canOrderUp());
            
            $this->assertFalse($root->canOrderDown());
            
        }
    }   
      
    function testNs() {
        $this->resetNs();
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
            
            $tns = $this->getTreeFromNs();
            $this->assertTreeMatch($tns, trim("
                root
                 child1
                 child2
            "));
            
            $this->assertTreeStructureOk($tns);
            
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
            
//            $tx = new Ac_Test_Tree_TextScanner();
//            $nsData = $this->fetchNsData();
//            var_dump(implode("\n", $tx->getTextFromNestedSets($nsData)));
        }
    }   
    
    function testAdjacent() {
        $this->resetAdj();
        $s = $this->getSampleApp();
        $mapper = $s->getSampleTreeAdjacentMapper();
        $mixable = $mapper->getMixable('treeMapper');
        $this->assertIsA($mixable, 'Ac_Model_Tree_AdjacencyListMapper');
        if ($mixable instanceof Ac_Model_Tree_AdjacencyListMapper) {
            
            $this->assertEqual($mapper->getDefaultParentValue(), null);
            
            $child1 = $mapper->createRecord();

            $this->assertEqual($child1->getOrdering(), Ac_Model_Tree_AbstractImpl::ORDER_LAST);
            
            $child1->title = 'child1';
            $child1->tag = 1;
            $this->assertTrue($child1->store());

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
    
    function getTreeFromText($text) {
        $tx = new Ac_Test_Tree_TextScanner();
        $scn = new Tr_Scanner($tx);
        $res = $scn->scan($text);
        $res->prepare();
        $res->setDumper(new Ac_Test_Tree_Dumper);
        return $res;
    }
    
    /**
     * @return Ac_Test_Tree_Node
     */
    function getTreeFromAdjacency($rootTitle = 'root', $tableName = '#__tree_adjacent', $rootParentId = null) {
        $tx = new Ac_Test_Tree_TextScanner();
        $db = $this->getSampleApp()->getDb();
        $txt = $tx->getTextFromAdjacency($db, $tableName, $rootParentId);
        $scn = new Tr_Scanner($tx);
        array_unshift($txt, $rootTitle);
        $res = $scn->scan($txt);
        $res->prepare();
        $res->setDumper(new Ac_Test_Tree_Dumper);
        return $res;
    }
    
    /**
     * @return Ac_Test_Tree_Node
     */
    function getTreeFromNs($rootTitle = "root") {
        $tx = new Ac_Test_Tree_TextScanner();
        $data = $this->fetchNsData();
        $txt = $tx->getTextFromNestedSets($data);
        $scn = new Tr_Scanner($tx);
        $res = $scn->scan($txt);
        if ($rootTitle !== null) $res->data['title'] = $rootTitle;
        $res->setDumper(new Ac_Test_Tree_Dumper);
        $res->prepare();
        return $res;
    }
    
    function fetchNsData() {
        $db = $this->getSampleApp()->getDb();
        $res = $db->fetchArray(
            "SELECT * FROM #__tree_nested_sets ns LEFT JOIN #__tree_records r "
            . "ON ns.id = r.id ORDER BY ns.leftCol"
        );
        return $res;
    }
    
    function _testScanner() {
        $tx = new Ac_Test_Tree_TextScanner();
        $sc = new Tr_Scanner($tx);
        $root = $sc->scan('
            Foo
              Bar
              Baz
              Qux
                Aa
                {"title": "Bb", "etc": "Bb Etc"}
                Cc
                Dd
                # Commented out
              Ee
              Moo
        ');
        $root->setDumper($dumper = new Ac_Test_Tree_Dumper);

        $root->dumpPre();
        
        $dumper->dumpStructure = true;
        
        $tp = new Ac_Test_Tree_Processor();
        $tp->doAll($root, 'process');
        
        $this->assertTreeMatch($root, $root);
        
        $root->dumpPre();
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
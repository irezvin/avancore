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
    
    function _testCombo() {
        $this->resetCombos();
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
            
            $this->getTreeFromNs()->dumpPre(true, true);
            $this->getTreeFromText('root
                child1
                child2
                child3')->dumpPre(true, true);
            
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
            
            $this->getTreeFromAdjacency()->dumpPre(true, true);
            $this->getTreeFromText('root
                child1
                child3
                child2')->dumpPre(true, true);
            
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
    function getTreeFromNs() {
        $tx = new Ac_Test_Tree_TextScanner();
        $data = $this->fetchNsData();
        $txt = $tx->getTextFromNestedSets($data);
        $scn = new Tr_Scanner($tx);
        $res = $scn->scan($txt);
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
    
    function testScanner() {
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
        
        $root->dumpPre();
    }
    
}

class Ac_Test_Tree_Node extends Tr_Node {
    
    var $data = array();

    var $left = 0;
    
    var $right = 0;
    
    var $ordering = 0;
    
    var $parentTitle = '';
    
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
        return Ac_Accessor::getObjectProperty($this, array('left', 'right', 'ordering', 'parentTitle'));
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
    
}

class Ac_Test_Tree_TextScanner implements Tr_I_ScannerImpl {
    
    var $defaultDataProperty = "title";
    
    /**
     * @return Tr_Node
     * $object a text or an array of lines:
     * 
     * node1
     *  node1.1 
     *  node1.2
     * {"title": "node2", "field2": "value2"} 
     * #lines beginning with '#' are ignored
     *  node2.1
     *   node2.1.1
     *   node2.1.2
     *  node2.2
     *  node2.3
     *  
     * # Node is considered a child if it has more indent spaces than parent
     * # First line is always considered root node and it's indent is ignored
     */
    function createRootNode($object) {
        if (!is_array($object)) { // $object should be array of lines 
            // split string into lines while removing empty lines
            $object = preg_split("/\s*[\n\r]+/", trim($object)); 
        }
        $object = preg_grep('/^\s*(#.*)?$/', $object, PREG_GREP_INVERT); // remove comments
        $object = array_values($object);
        $first = array_shift($object);
        return new Ac_Test_Tree_Node($object, $this->getExtra($first));
    }

    var $nsMap = array(
        'left' => 'leftCol',
        'right' => 'rightCol',
        'depth' => 'depth',
        'ordering' => 'ordering',
        'title' => 'title',
    );
    
    var $adjMap = array(
        'id' => 'id',
        'parentId' => 'parentId',
        'ordering' => 'ordering',
        'title' => 'title',
    );
    
    function remap($src, $map) {
        foreach ($map as $k => $v) $res[$k] = $src[$v];
        return $res;
    }
    
    /**
     * $nsData must be ordered by 'left'!!!
     * @param array $nsData
     */
    function getTextFromNestedSets(array $nsData) {
        $res = array();
        foreach ($nsData as $row) {
            $data = $this->remap ($row, $this->nsMap);
            $res[] = str_repeat(' ', $data['depth']).json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        return $res;
    }
    
    function getChildRows($adjData, $id) {
        $res = array();
        foreach ($adjData as $row) {
            if ($row['parentId'] === $id) $res[] = $row;
        }
        return $res;
    }
    
    function makeAdjText($adjData, $parentId, $depth = 0) {
        $res = array();
        foreach ($this->getChildRows($adjData, $parentId) as $data) {
            $res[] = str_repeat(' ', $depth + 1).json_encode($data, JSON_UNESCAPED_UNICODE);
            $res = array_merge($res, $this->makeAdjText($adjData, $depth + 1));
        }
        return $res;
    }
    
    function getTextFromAdjacency(Ac_Sql_Db $db, $tableName, $rootParentId = null, $depth = 0) {
        $res = array();
        $sql = Ac_Sql_Statement::create(
            'SELECT * FROM [[tableName]] ORDER BY [[ordering]]', 
            array_merge(
                $this->adjMap, 
                array(
                    'tableName' => $tableName,
                )
            )
        );
        $mapped = array();
        foreach ($db->fetchArray($sql) as $row) {
            $mapped[] = $this->remap($row, $this->adjMap);
        }
        $res = $this->makeAdjText($mapped, $rootParentId);
        return $res;
    }
    
    function scanNode(Tr_Node $node) {
        $lines = $node->getObject();
        
        $indent = 0;
        $top = false;
        $body = array();
        while (!is_null($curr = array_shift($lines))) {
            $currIndent = $this->getIndent($curr);
            if ($top === false) {
                $top = $curr;
                $indent = $currIndent;
            } 
            elseif($currIndent > $indent) $body[] = $curr;
            else {
                $node->createNode($body, $this->getExtra($top));
                $body = array();
                $top = $curr;
                $indent = $currIndent;
            }
        }
        if ($top !== false)
            $node->createNode($body, $this->getExtra($top));
    }
    
    function getIndent($string) {
        $string = str_replace("\t", "    ", $string);
        return strlen($string) - strlen(ltrim($string));
    }
    
    function getExtra($string) {
        $indent = $this->getIndent($string);
        $string = trim($string);
        $jd = json_decode($string, true);
        if (json_last_error()) $jd = $string;
        if (is_array($jd)) $res = array('data' => $jd);
            else $res = array('data' => array($this->defaultDataProperty => $jd));
        return $res;
    }
    
}

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
        $node->ordering = $node->getIndex() + $this->orderingStartsAt;
        $node->right = $node->left + 1;
        if ($parent) {
            $this->setRight($parent, $node->right + 1);
            $node->parentTitle = $parent->data['title'];
        }
    }
    
    function doAll($node, $method, $_ = null) {
        if (!is_array($method)) $method = array($this, $method);
        $iter = new RecursiveIteratorIterator($node->createSuperNode(), RecursiveIteratorIterator::SELF_FIRST);
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


abstract class ImplNsGetter extends Ac_Model_Tree_NestedSetsImpl {
    
    static function get(Ac_Model_Tree_NestedSetsImpl $foo, $prop) {
        return $foo->$prop;
    }
    
    static function call(Ac_Model_Tree_NestedSetsImpl $foo, $method, $args = array()) {
        return call_user_func_array(array($foo, $method), $args);
    }
    
}
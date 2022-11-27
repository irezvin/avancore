<?php

class Ac_Test_Collection extends Ac_Test_Base {
    
    var $bootSampleApp = true;
    
    var $checkedPeople = array();

    function testSqlCollection() {
        $c = new Ac_Model_Collection_SqlMapper(array(
            'application' => Sample::getInstance(),
            'mapperId' => 'Sample_Person_Mapper',
        ));
        $c->setSqlSelectPrototype(array(
            'where' => array('personId IN (3, 4)'),
            'orderBy' => 'personId',
        ));
        $guys = array();
        foreach ($c as $guy) {
            $guys[$guy->personId] = $guy->name;
        }
        $this->assertEqual(array_keys($guys), array(3, 4));
        $c->close();
        $c->setSqlSelectPrototype(array(
            'parts' => array(
                'names' => array(
                    'class' => 'Ac_Sql_Filter_Equals',
                    'colName' => 't.name',
                ),
            ),
            'orderBy' => 't.name',
        ));
        $c->setQuery(array('names' => array('Илья', 'Таня'), 'notTest' => true));
        $guys = array();
        foreach ($c as $guy) {
            $guys[$guy->personId] = $guy->name;
        }
        $this->assertEqual(array_values($guys), array('Илья', 'Таня'));
        $sql = $this->normalizeStatement($c->createSqlSelect());
        $this->assertTrue(preg_match("#not like '%test%'#u", $sql));
        $this->assertTrue(preg_match("#IN \('Илья', 'Таня'\)#u", $sql));
        
        $c->close();
    }
    
    function testMapperCollection() {
        $c = new Ac_Model_Collection_Mapper(array(
            'application' => Sample::getInstance(),
            'mapperId' => 'Sample_Person_Mapper',
            'query' => array('notTest' => true),
        ));
        $this->assertEqual($c->getCount(), 4);
        $c->close();
        $c->setQuery(array('birthYear' => 1981, 'notTest' => true));
        $c->setSort('name');
        $this->assertEqual($c->getCount(), 3);
        foreach ($c as $guy) {
            $guys[] = $guy->name;
        }
        $this->assertEqual($guys, array('Оля', 'Таня', 'Ян'));
        $c->close();
        $c->setQuery(array('notTest' => true));
        $c->setSort('name');
        $c->setGroupSize(2);
        $c->setCleanGroupOnAdvance(true);
        $iid = array();
        foreach ($c as $guy) {
            $iid[] = $guy->instanceId;
            unset($guy);
        }
        $c->close();
        $this->assertEqual(count($iid), 4);
        $this->assertTrue(!count(array_diff($iid, array_keys(Sample_Person::$destructed))));
        $c->setGroupSize(0);
        $c->setKeyProperty('personId');
        $itemsA = array();
        $itemsB = array();
        foreach ($c as $idx => $guy) {
            $itemsA[$idx] = $guy->name;
            $itemsB[$guy->personId] = $guy->name;
        }
        $this->assertTrue(count($itemsA));
        $this->assertEqual($itemsA, $itemsB);

        $c->close();
        $c->setSearchPrototype(array(
            'criteria' => array(
                'myCallback' => new Ac_Model_Criterion_Callback(array($this, 'myCallback'))
            )
        ));
        $this->checkedPeople = [];
        $c->setQuery(array('myCallback' => true, 'notTest' => true));
        $itemsA = array();
        foreach ($c->fetchGroup() as $guy) $itemsA[$guy->personId] = $guy->name;
        $this->assertTrue(!array_diff($itemsA, array('Оля', 'Таня')) && count($itemsA) == 2);
        $this->assertTrue(!array_diff(array_keys($itemsA), array_keys($this->checkedPeople)));
    }
    
    function testAbstractCollection() {
        $arr = range(0, 99);
        $coll = new TrivialCollection(array('items' => $arr, 'autoOpen' => true));
        
        $this->assertEqual($coll->getCount(), 100);
        
        // fetch all
        $this->assertEqual($coll->fetchGroup(), $arr);
        $this->assertEqual($coll->fetchGroup(), array());
        $this->assertEqual($coll->fetches, 1);
        
        $a2 = array();
        $coll->close();
        $coll->setGroupSize(10);
        while($g = $coll->fetchGroup()) $a2 = array_merge($a2, $g);
        $this->assertEqual($a2, $arr);
        $this->assertEqual($coll->fetches, 11);
        
        $a2 = array();
        $coll->close();
        $coll->setGroupSize(10);
        $coll->setLimit(100);
        while($g = $coll->fetchGroup()) $a2 = array_merge($a2, $g);
        $this->assertEqual($a2, $arr);
        // no last fetch because limit is set
        $this->assertEqual($coll->fetches, 10);
        
        $coll->close();
        $coll->setGroupSize(70);
        $coll->setLimit(90);
        $a1 = $coll->fetchGroup();
        $a2 = array($coll->fetchItem(), $coll->fetchItem());
        $a3 = $coll->fetchGroup();
        $this->assertEqual($a1, range(0, 69));
        $this->assertEqual($a2, range(70, 71));
        $this->assertEqual($a3, range(72, 89));
        $this->assertEqual($coll->fetches, 2);
        
        $coll->close();
        $coll->setGroupSize(10);
        $coll->setLimit(0);
        $a1 = array();
        foreach ($coll as $k => $item) {
            $a1[$k] = $item;
        }
        $this->assertEqual($a1, $arr);
        $this->assertEqual($coll->fetches, 11);
        
        $coll->close();
        $coll->setGroupSize(1);
        $coll->setLimit(0);
        $a1 = array();
        foreach ($coll as $k => $item) {
            $a1[$k] = $item;
        }
        $this->assertEqual($a1, $arr);
        $this->assertEqual($coll->fetches, 101);
        $coll->close();
        
        $coll->setGroupSize(0);
        $coll->setLimit(0);
        $a1 = array();
        $n = 0;
        foreach ($coll as $k => $item) {
            $n++;
            if ($n > 110) {
                break;
            }
            $a1[$k] = $item;
        }
        $this->assertEqual($a1, $arr);
        $this->assertEqual($coll->fetches, 1);
        $this->assertEqual($n, 100);
        $coll->close();
        
        $coll->setGroupSize(0);
        $coll->setLimit(0);
        $a1 = array();
        $n = 0;
        while (($item = $coll->fetchItem()) !== false) {
            $n++;
            if ($n > 110) {
                break;
            }
            $a1[] = $item;
        }
        $this->assertEqual($a1, $arr);
        $this->assertEqual($coll->fetches, 1);
        $this->assertEqual($n, 100);
    }
    
    function myCallback(Sample_Person $p) {
        $this->checkedPeople[$p->personId] = $p;
        return $p->name == 'Оля' || $p->name == 'Таня';
    }
    
}

class TrivialCollection extends Ac_Model_Collection_Abstract {
    
    var $items = array();
    
    var $fetches = 0;
    
    function getExtIndex() {
        return $this->extIndex;
    }
    
    function getIntIndex() {
        return $this->intIndex;
    }
    
    protected function resetState() {
        parent::resetState();
        $this->fetches = 0;
    }
    
    function hasPublicVars() {
        return true;
    }
    
    protected function doFetchGroup($offset, $length) {
        $this->fetches++;
        if (!$length) $length = null;
        if (!$offset && !$length) return $this->items;
        return array_slice($this->items, $offset, $length);
    }

    protected function doCount() {
        return count($this->items);
    }
    
}
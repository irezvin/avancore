<?php

class Ac_Test_Collection extends Ac_Test_Base {
    
    var $bootSampleApp = true;
    
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
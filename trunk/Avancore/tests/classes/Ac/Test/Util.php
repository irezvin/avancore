<?php

class Ac_Test_Util extends Ac_Test_Base {
    
    function testUtil() {
        
        $a = array('foo' => array());
        $b = array('foo'=> array(1, 2, 3));
        $c = Ac_Util::m($a, $b);
        if (!$this->assertEqual($c['foo'], $b['foo'])) var_dump($c);
        
    }
    
}
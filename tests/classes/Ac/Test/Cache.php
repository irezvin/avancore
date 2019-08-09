<?php

class Ac_Test_Cache extends Ac_Test_Base {

    function testCacheAccessor() {
        
        $cache = new Ac_Cache_Memory();
        $a = $cache->accessor('foo', 'bar');
        $this->assertFalse($a->has());
        $a->put('xx');
        $this->assertTrue($a->has());
        $b = $cache->accessor('foo', 'bar');
        $this->assertTrue($b->has());
        $b->delete();
        $c = $cache->accessor('foo', 'bar');
        $this->assertFalse($c->has());
        $d = $cache->accessor('foo2', 'bar');
        $d->put($arr = array('10', '20'));
        $e = $cache->accessor('foo2', 'bar');
        $this->assertArraysMatch($e->get(true), $arr);
        
    }
    
}

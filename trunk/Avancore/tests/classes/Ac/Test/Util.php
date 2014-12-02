<?php

class Ac_Test_Util extends Ac_Test_Base {
    
    function testUtil() {
        
        $a = array('foo' => array());
        $b = array('foo'=> array(1, 2, 3));
        $c = Ac_Util::m($a, $b);
        if (!$this->assertEqual($c['foo'], $b['foo'])) var_dump($c);
        
    }
    
    function testIndexArray() {
        
        $c = new stdClass();
        $c->foo =  10;
        $c->bar = 20;
        
        $a = array(
            array('foo' => 5, 'bar' => 6),
            array('foo' => 6, 'bar' => 7),
            $c
        );
        
        if (!$this->assertEqual($idx = Ac_Util::indexArray($a, array('foo', 'bar'), false), $need = array(
            5 => array(6 => array(array('foo' => 5, 'bar' => 6))),
            6 => array(7 => array(array('foo' => 6, 'bar' => 7))),
            10=> array(20 => array($c))
        ))) {
            var_dump($idx);
        }
        
        if (!$this->assertEqual($idx = Ac_Util::indexArray($a, array('foo', 'bar'), true), $need = array(
            5 => array(6 => array('foo' => 5, 'bar' => 6)),
            6 => array(7 => array('foo' => 6, 'bar' => 7)),
            10=> array(20 => $c)
        ))) {
            var_dump($idx);
        }
        
        if (!$this->assertEqual($idx = Ac_Util::indexArray($a, 'foo', false), $need = array(
            5 => array((array('foo' => 5, 'bar' => 6))),
            6 => array((array('foo' => 6, 'bar' => 7))),
            10=> array(($c))
        ))) {
            var_dump($idx);
        }
        
        if (!$this->assertEqual($idx = Ac_Util::indexArray($a, array('bar'), true), $need = array(
            6 => array('foo' => 5, 'bar' => 6),
            7 => array('foo' => 6, 'bar' => 7),
            20 => $c
        ))) {
            var_dump($idx);
        }
        
    }
    
    function testFlattenArray() {
        $arr = array(
            'foo',
            'bar' => array(
                'baz',
                'quux' => 'moo'
            )
        );
        $f = Ac_Util::flattenArray($arr, -1, '.');
        if (!$this->assertEqual($f = Ac_Util::flattenArray($arr, -1, '.'), array(
            'foo',
            'bar.0' => 'baz',
            'bar.quux' => 'moo',
        ))) {
            var_dump($f);
        }
    }
    
    var $report = array();
    
    /**
     * @param type $string
     * 
     *  +dir/sub/file
     *     file2
     *  dir/sub2/file3
     *     file4
     * + file5
     *  
     * "+" denotes files that should be older than $ttl seconds
     * " " between start of line or "+" and filename/dirname means file 
     *     should have same path as prev.file
     * 
     *      
     * @param type $base
     * @param type $ttl
     */
    function mkStuff($string, $base, $ttl = 3600) {
        $items = preg_split('/\s*\n\s*+/', $string, -1, PREG_SPLIT_NO_EMPTY);
        $dir = $base;
        foreach ($items as $filename) {
            $old = false;
            if ($filename{0} == '+') {
                $old = true;
                $filename = substr($filename, 1);
            }
            if ($filename{0} == ' ') {
            } else {
                $dir = $base;
            }
            $filename = trim($filename);
            $filename = trim($filename, '/\\');
            $dn = dirname($filename);
            $dir = $dir.'/'.$dn;
            if (!is_dir($dir)) mkdir($dir, 0775, true);
            $t = time();
            if ($old) $t -= $ttl + 1;
            touch($dir.'/'.basename($filename), $t);
        }
    }
    
    function deleteCb(SplFileInfo $f) {
        $this->report[$f->getPathname()] = $f->getBasename();
        return true;
    }
    
    function testDeleteOldFiles() {
        $this->report = array();
        $sa = $this->getSampleApp();
        $varDir = $sa->getAdapter()->getVarCachePath();
        $stuff = implode("\n", array(
            'foo/new1',
            ' new2',
            '+ old1',
            ' bar/new3',
            '+ old2',
            '+baz/old3',
            '+ old4',
            '+baz/old5',
            '+ quux/old6',
            '+ old7'
        ));
        $dof = new Ac_Util_DeleteOldFiles();
        $dof->setDirName($varDir);
        $dof->setDeleteEmptyDirs(true);
        $dof->setCallback(array($this, 'deleteCb'));
        $this->mkStuff($stuff, $varDir, $dof->getLifetime());
        
        $this->report = array();
        $dof->run();
        $this->assertTrue(!count(array_diff($this->report, array(
            'old1', 'old2', 'old3', 'old4', 'old5', 'old6', 'old7', 'baz', 'quux'
        ))), 'old files and directories with them should be deleted...');
        $notDeleted = array();
        clearstatcache();
        foreach ($this->report as $k => $v) {
            if (is_file($k)) $notDeleted[$k] = $v;
        }
        if (!$this->assertTrue(!count($notDeleted), 'All items intended for deletion '
            . 'were really deleted')) var_dump($notDeleted);
    }
    
}
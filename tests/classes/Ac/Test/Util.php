<?php

class Ac_Test_Util extends Ac_Test_Base {
    
    var $report = array();
    
    var $periodicRan = false;
    
    // A bit idiotic
    function testSimpliestMergePossible() {
        $a = array('foo' => array());
        $b = array('foo'=> array(1, 2, 3));
        $c = Ac_Util::m($a, $b);
        if (!$this->assertEqual($c['foo'], $b['foo'])) var_dump($c);
    }
    
    function getTrickyArray() {
        $res = array();
        $a = 7; $b = 1;
        $res[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)] = $b - 1; // 7
        $a = 6; $b = 1;
        $res[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)] = $b - 1; // 6
        $a = 5; $b = 1;
        $res[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)] = $b - 1; // 5
        $a = 4; $b = 1;
        $res[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)] = $b - 1; // 4
        $a = 3; $b = 1;
        $res[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)] = $b - 1; // 3
        $a = 2; $b = 1;
        $res[$a.'-'.($b++)][$a.'-'.($b++)] = $b - 1; // 2
        $a = 1; $b = 1;
        $res[$a.'-'.($b++)] = $b - 1; // 1
        return $res;
    }
    
    function makePath($a, $s, $open = false) {
        $res = array();
        for ($i = 0; $i < strlen($s); $i++) $res[] = $a.'-'.$s[$i];
        if ($open) $res[] = '';
        return $res;
    }
    
    function testGetSetByPath() {
        
        // ------------------ depth 7 - out of opt.range ------------------
        
        $lev = 7; 
        
        $x = $this->getTrickyArray();
        $y = $this->getTrickyArray();
        Ac_Util::setArrayByPath($x, $this->makePath($lev, '1234567'), 'x');
        $a = $lev; $b = 1;
        $y[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)] = 'x';
        $this->assertEqual($x, $y);
        
        $x = $this->getTrickyArray();
        $val = Ac_Util::getArrayByPath($x, $this->makePath($lev, '1234567'), 'def', $found);
        $this->assertEqual($val, $lev);
        $this->assertEqual($found, true);
        
        $x = $this->getTrickyArray();
        $val = Ac_Util::getArrayByPath($x, $this->makePath($lev, '12345678'), 'def', $found);
        $this->assertEqual($val, 'def');
        $this->assertEqual($found, false);
        $this->assertEqual($x, $this->getTrickyArray());
        
        
        $x = $this->getTrickyArray();
        $y = $this->getTrickyArray();
        Ac_Util::unsetArrayByPath($x, $this->makePath($lev, '1234567'), 'x');
        $a = $lev; $b = 1;
        unset($y[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)]);
        $this->assertEqual($x, $y);
        
        $x = $this->getTrickyArray();
        $y = $this->getTrickyArray();
        Ac_Util::setArrayByPath($x, $this->makePath($lev, '123456', true), 'x');
        $a = $lev; $b = 1;
        $y[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][] = 'x';
        $this->assertEqual($x, $y);
        
        $x = $this->getTrickyArray();
        $y = $this->getTrickyArray();
        $refX = & Ac_Util::getArrayByPathRef($x, $this->makePath($lev, '123456'));
        $refX['7-7'] = '1';
        $a = $lev; $b = 1;
        $y[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)] = '1';
        $this->assertEqual($x, $y);
        
        $x = $this->getTrickyArray();
        $y = $this->getTrickyArray();
        $refX = Ac_Util::getArrayByPath($x, $this->makePath($lev, '123456'));
        $refX['7-7'] = '1';
        $this->assertEqual($x, $y);
        
        $x = $this->getTrickyArray();
        $y = $this->getTrickyArray();
        $r = array();
        Ac_Util::setArrayByPathRef($x, $this->makePath($lev, '123456r'), $r);
        $a = $lev; $b = 1;
        $y[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-r'] = & $r;
        
        $r[] = 'a';
        $this->assertEqual($x, $y);
        
        $x = $this->getTrickyArray();
        $y = $this->getTrickyArray();
        $z = $this->getTrickyArray();
        $r = array();
        Ac_Util::setArrayByPath($x, $this->makePath($lev, '123456r'), $r);
        $a = $lev; $b = 1;
        $y[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-r'] = $r;
        
        $a = $lev; $b = 1;
        $z[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-r'] = array();
        
        $r[] = 'a';
        $this->assertEqual($x, $y);
        $this->assertEqual($x, $z);
        
        
        // ------------------ depth 3 - in opt.range ------------------
        
        $lev = 3; 
        
        $x = $this->getTrickyArray();
        $y = $this->getTrickyArray();
        Ac_Util::setArrayByPath($x, $this->makePath($lev, '123'), 'x');
        $a = $lev; $b = 1;
        $y[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)] = 'x';
        $this->assertEqual($x, $y);
        
        $x = $this->getTrickyArray();
        $val = Ac_Util::getArrayByPath($x, $this->makePath($lev, '123'), 'def', $found);
        $this->assertEqual($val, $lev);
        $this->assertEqual($found, true);
        
        $x = $this->getTrickyArray();
        $val = Ac_Util::getArrayByPath($x, $this->makePath($lev, '1234'), 'def', $found);
        $this->assertEqual($val, 'def');
        $this->assertEqual($found, false);
        $this->assertEqual($x, $this->getTrickyArray());
        
        $x = $this->getTrickyArray();
        $y = $this->getTrickyArray();
        Ac_Util::unsetArrayByPath($x, $this->makePath($lev, '123'), 'x');
        $a = $lev; $b = 1;
        unset($y[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)]);
        $this->assertEqual($x, $y);
        
        $x = $this->getTrickyArray();
        $y = $this->getTrickyArray();
        Ac_Util::setArrayByPath($x, $this->makePath($lev, '12', true), 'x');
        $a = $lev; $b = 1;
        $y[$a.'-'.($b++)][$a.'-'.($b++)][] = 'x';
        $this->assertEqual($x, $y);
        
        $x = $this->getTrickyArray();
        $y = $this->getTrickyArray();
        $refX = & Ac_Util::getArrayByPathRef($x, $this->makePath($lev, '12'));
        $refX['3-3'] = '1';
        $a = $lev; $b = 1;
        $y[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)] = '1';
        $this->assertEqual($x, $y);
        
        $x = $this->getTrickyArray();
        $y = $this->getTrickyArray();
        $refX = Ac_Util::getArrayByPath($x, $this->makePath($lev, '12'));
        $refX['7-7'] = '1';
        $this->assertEqual($x, $y);
        
        $x = $this->getTrickyArray();
        $y = $this->getTrickyArray();
        $r = array();
        Ac_Util::setArrayByPathRef($x, $this->makePath($lev, '12r'), $r);
        $a = $lev; $b = 1;
        $y[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-r'] = & $r;
        
        $r[] = 'a';
        $this->assertEqual($x, $y);
        
        $x = $this->getTrickyArray();
        $y = $this->getTrickyArray();
        $z = $this->getTrickyArray();
        $r = array();
        Ac_Util::setArrayByPath($x, $this->makePath($lev, '12r'), $r);
        $a = $lev; $b = 1;
        $y[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-r'] = $r;
        
        $a = $lev; $b = 1;
        $z[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-r'] = array();
        
        $r[] = 'a';
        $this->assertEqual($x, $y);
        $this->assertEqual($x, $z);
        
        // TODO: check non-array source, check non-array path, check 1 item
        
        $x = null;
        Ac_Util::setArrayByPath($x, $this->makePath(7, '1234567'), 'm');
        $y = array();
        $a = 7; $b = 1;
        $y[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)] = 'm';
        $this->assertEqual($x, $y);
        
        $x = null;
        Ac_Util::setArrayByPath($x, $this->makePath(3, '123'), 'm');
        $y = array();
        $a = 3; $b = 1;
        $y[$a.'-'.($b++)][$a.'-'.($b++)][$a.'-'.($b++)] = 'm';
        $this->assertEqual($x, $y);
        
        $x = null;
        Ac_Util::setArrayByPath($x, $this->makePath(1, '1'), 'm');
        $y = array();
        $a = 1; $b = 1;
        $y[$a.'-'.($b++)] = 'm';
        $this->assertEqual($x, $y);
        
        $x = null;
        Ac_Util::setArrayByPath($x, '1-1', 'm');
        $y = array();
        $a = 1; $b = 1;
        $y[$a.'-'.($b++)] = 'm';
        $this->assertEqual($x, $y);
        
        $x = null;
        Ac_Util::setArrayByPath($x, array(), 'm');
        $y = 'm';
        $this->assertEqual($x, $y);
    }

    function rmDir($dir) {
        if (!is_dir($dir)) return;
        $path = realpath($dir);
        $parent = realpath(Sample::getInstance()->getAdapter()->getAppRootDir());
        if (!!strncmp($path, $parent, strlen($parent))) return false;
        $directory = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($directory);
        $dirs = array();
        foreach ($iterator as $item) {
            $f = basename($item);
            if ($f === '.') {
                $dirs[] = dirname(''.$item);
                continue;
            }
            elseif ($f === '..') continue;
            if (is_dir(''.$item)) $dirs[] = ''.$item;
            else unlink(''.$item);
        }
        sort($dirs);
        foreach (array_reverse($dirs) as $d) {
            rmdir($d);
        }
    }    
    
    function testCgDirSync() {
        $sa = $this->getSampleApp();
        $dir = $sa->getAdapter()->getVarCachePath();
        $src = $dir."/dirSyncSrc";
        $dest = $dir."/dirSyncDest";
        $this->rmDir($src);
        $this->rmDir($dest);
        if (!is_dir($src)) mkdir($src, 0777, true);
        if (!is_dir($dest)) mkdir($dest, 0777, true);
        $this->mkStuff("
            Sample/First/Base/Object.php
            Sample/First/Base/Mapper.php
            Sample/Second/Base/Object.php
            Sample/Second/Base/Mapper.php
        ", $src);
        $this->mkStuff("
            Sample/First/Base/Object.php
            Sample/Obsolete/Base/Mapper.php
        ", $dest);
        $ds = new Ac_Cg_DirSync;
        
        $ds->srcDir = $src;
        $ds->destDir = $dest;
        
        $ds->clearSrc();
        
        $ds->deleteFromDest = true;
        $ds->overwriteDest = true;
        $this->assertTrue($ds->run());
        $wl = $ds->getWorkList();
        
        $ds->dryRun = false;
        $this->assertTrue($ds->run());
        $destList = $ds->listDest();
        sort($wl);
        sort($destList);
        $this->assertEqual($wl, $destList);
        $proper = preg_split("/\s*[\n\r]+\s*/", trim("Sample/
            Sample/First/
            Sample/First/Base/
            Sample/First/Base/Object.php
            Sample/First/Base/Mapper.php
            Sample/Second/
            Sample/Second/Base/
            Sample/Second/Base/Object.php
            Sample/Second/Base/Mapper.php"
        ));
        sort($proper);
        $this->assertEqual($destList, $proper);
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
            if ($filename[0] == '+') {
                $old = true;
                $filename = substr($filename, 1);
            }
            if ($filename[0] == ' ') {
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
            $f = $dir.'/'.basename($filename);
            
            // ugly hack since PHP's touch() allows to change mtime for files which match current owner
            if (!@touch($f, $t)) {
                $tmp = file_get_contents($f);
                unlink($f);
                file_put_contents($f, $tmp);
                chmod($f, 0775);
                touch ($f, $t);
            }
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
    
    function getSampleFlagsDir() {
        return dirname(__FILE__).'/../../../sampleApp/var/flags';
    }
    
    function testLock() {
        $dir = $this->getSampleFlagsDir();
        $foo = new Ac_Util_Lock(array('dirName' => $dir, 'fileName' => 'test_lock'));
        $bar = new Ac_Util_Lock(array('dirName' => $dir, 'fileName' => 'test_lock'));
        $path = $foo->getPath();
        if (is_file($path)) unlink($path);
        $dp = realpath($dir);
        $fp = realpath(dirname($path));
        $this->assertEqual($dp, $fp);
        $this->assertEqual($foo->getPath(), $bar->getPath());
        $this->assertTrue($foo->acquire());
        $this->assertTrue(file_exists($foo->getPath()));
        $this->assertFalse($bar->acquire());
        $this->assertTrue($foo->release());
        $this->assertTrue($bar->acquire());
        $this->assertTrue($bar->release());
    }
    
    function runCallback() {
        $this->periodicRan++;
    }
    
    function testPeriodic() {
        $a = new Ac_Util_Periodic(array(
            'id' => 'foo',
            'intervalSeconds' => 2,
            'flags' => new Ac_Flags(array('dir' => $this->getSampleFlagsDir())),
        ));
        $this->assertTrue($a->run());
        $this->assertFalse($a->run());
        sleep(2);
        $this->assertTrue($a->run());
        
        // Test with lock & callback
        $lock = new Ac_Util_Lock(array('dirName' => $this->getSampleFlagsDir(), 'fileName' => $a->getFlagName()));
        $a->setLock($lock);
        $lock->acquire();
        $a->setCallback(array($this, 'runCallback'));
        $this->periodicRan = 0;
        $this->assertFalse($a->run());
        $this->assertEqual($this->periodicRan, 0);
        $a->setIntervalSeconds(1);
        $lock->release();
        sleep(1);
        $this->assertTrue($a->run());
        $this->assertEqual($this->periodicRan, 1);
        $this->assertFalse($lock->has());
    }
    
    function testOverriddenMethods() {
        require_once(dirname(__FILE__).'/assets/classesWithOverrides.php');
        $this->assertFalse(Ac_Util::isMethodOverridden('aBaseClass', 'aBaseClass', 'foo'));
        $this->assertFalse(Ac_Util::isMethodOverridden('aBaseClass', 'aBaseClass', 'bar'));
        $this->assertFalse(Ac_Util::isMethodOverridden('aBaseClass', 'aBaseClass', 'baz'));
        $this->assertTrue(!Ac_Util::isMethodOverridden('aBaseClass', 'aBaseClass'));
        
        $this->assertTrue(Ac_Util::isMethodOverridden('aDescendantClass', 'aBaseClass', 'foo'));
        $this->assertFalse(Ac_Util::isMethodOverridden('aDescendantClass', 'aBaseClass', 'bar'));
        $this->assertFalse(Ac_Util::isMethodOverridden('aDescendantClass', 'aBaseClass', 'baz'));
        $this->assertTrue(!array_diff($a = Ac_Util::isMethodOverridden('aDescendantClass', 'aBaseClass'), array('foo')));
        
        $this->assertTrue(Ac_Util::isMethodOverridden('aNotherDescendantClass', 'aBaseClass', 'foo'));
        $this->assertTrue(Ac_Util::isMethodOverridden('aNotherDescendantClass', 'aBaseClass', 'bar'));
        $this->assertFalse(Ac_Util::isMethodOverridden('aNotherDescendantClass', 'aBaseClass', 'baz'));
        $this->assertTrue(!array_diff(Ac_Util::isMethodOverridden('aNotherDescendantClass', 'aBaseClass'), array('foo', 'bar')));
    }
    
}

<?php

class Ac_Test_ModelValues extends Ac_Test_Base {

    protected $bootSampleApp = true;

    var $list = array(
            'fileA.ext1',
            'fileB.ext2',
            'fileC.ext1',
            'dirA/subdirA1/fileAA1.ext1',
            'dirA/subdirA1/fileAA2.ext1',
            'dirA/subdirA1/fileAA3.ext2',
            'dirB/fileB1.ext1',
            'dirB/fileB2.ext2',
        );
    
    var $subDir = 'someFiles';
    
    protected function getBasePathForFiles($check = false) {
        $app = $this->getSampleApp();
        $varDir = $app->getAdapter()->getVarPath();
        if ($check) {
            if (!is_dir($varDir) || !is_writable($varDir)) 
                throw new Exception("Dir '$varDir' need to exist and be writeable to proceed");
        }
        $basePath = $varDir.'/'.$this->subDir;
        return $basePath;
    }


    protected function createSampleStructure() {
        $p = $this->getBasePathForFiles(true);
        $bp = $p.'/';
        foreach ($this->list as $item) {
            $fullPath = $bp.$item;
            $d = dirname($fullPath);
            if (!is_dir($d)) mkdir($d, 0777, true);
            if (!is_file($fullPath)) touch($fullPath);
        }
        return $p;
    }
    
    function testValuesFiles() {
        $path = $this->createSampleStructure();
        $v = new Ac_Model_Values_Files(array(
            'dirName' => $path,
            'sort' => true,
        ));
        if (!$this->assertEqual($files = $v->getValueList(), $proper = array(
            'fileA.ext1' => 'fileA.ext1',
            'fileB.ext2' => 'fileB.ext2',
            'fileC.ext1' => 'fileC.ext1',
        ))) var_dump($files, $proper);

        $v->setRecursive(true);
        if (!$this->assertEqual($files = $v->getValueList(), $proper = array(
            'dirA/subdirA1/fileAA1.ext1' => 'dirA/subdirA1/fileAA1.ext1',
            'dirA/subdirA1/fileAA2.ext1' => 'dirA/subdirA1/fileAA2.ext1',
            'dirA/subdirA1/fileAA3.ext2' => 'dirA/subdirA1/fileAA3.ext2',
            'dirB/fileB1.ext1' => 'dirB/fileB1.ext1',
            'dirB/fileB2.ext2' => 'dirB/fileB2.ext2',
            'fileA.ext1' => 'fileA.ext1',
            'fileB.ext2' => 'fileB.ext2',
            'fileC.ext1' => 'fileC.ext1',
        ))) var_dump($files, $proper);
        
        $v->setIncludeDirs(true);
        if (!$this->assertEqual($files = $v->getValueList(), $proper = array(
            'dirA' => 'dirA',
            'dirA/subdirA1/fileAA1.ext1' => 'dirA/subdirA1/fileAA1.ext1',
            'dirA/subdirA1/fileAA2.ext1' => 'dirA/subdirA1/fileAA2.ext1',
            'dirA/subdirA1/fileAA3.ext2' => 'dirA/subdirA1/fileAA3.ext2',
            'dirB' => 'dirB',
            'dirB/fileB1.ext1' => 'dirB/fileB1.ext1',
            'dirB/fileB2.ext2' => 'dirB/fileB2.ext2',
            'fileA.ext1' => 'fileA.ext1',
            'fileB.ext2' => 'fileB.ext2',
            'fileC.ext1' => 'fileC.ext1',
        ))) var_dump($files, $proper);
        
        $v->setStripExtensions(true);
        $v->setIncludeDirs(false);
        if (!$this->assertEqual($files = $v->getValueList(), $proper = array(
            'dirA/subdirA1/fileAA1' => 'dirA/subdirA1/fileAA1',
            'dirA/subdirA1/fileAA2' => 'dirA/subdirA1/fileAA2',
            'dirA/subdirA1/fileAA3' => 'dirA/subdirA1/fileAA3',
            'dirB/fileB1' => 'dirB/fileB1',
            'dirB/fileB2' => 'dirB/fileB2',
            'fileA' => 'fileA',
            'fileB' => 'fileB',
            'fileC' => 'fileC',
        ))) var_dump($files, $proper);
        
        $v->setDirNameCallback(array($this, 'callbackWithDataDirA'));
        $this->assertEqual($v->getDirName(true), $this->callbackWithDataDirA());
        
        $v->setData($this);
        $v->setDirNameCallback(array(true, 'callbackWithDataDirB'));
        $this->assertEqual($v->getDirName(true), $this->callbackWithDataDirB());
        
    }
    
    function callbackWithDataDirA() {
        return $this->getBasePathForFiles().'/dirA';
    }
    
    function callbackWithDataDirB() {
        return $this->getBasePathForFiles().'/dirB';
    }
    
    function testValuesMapper() {
        $v = new Ac_Model_Values_Mapper();
        $m = $this->getSampleApp()->getSamplePersonMapper();
        $v->setMapper($m);

        $query = array(Ac_I_Search_FilterProvider::IDENTIFIER_CRITERION => array(7, 6, 4, 3));
        $v->setQuery($query);
        $ref = $m->getTitles($query);
        if (!$this->assertEqual($res = $v->getValueList(), $ref)) var_dump($res, $ref);
        
        $query = array('gender' => 'F', 'notTest' => true);
        $v->setQuery($query);
        $ref = $m->getTitles($query);
        if (!$this->assertEqual($res = $v->getValueList(), $ref)) var_dump($res, $ref);

        $query = array('notTest' => true);
        $sort = array('birthYear', 'name');
        $titleFieldName = 'birthYear';
        $v->setQuery($query);
        $v->setSort($sort);
        $v->setTitleFieldName($titleFieldName);
        $query = array(Ac_I_Search_FilterProvider::IDENTIFIER_CRITERION => array(7, 6, 4, 3));
        $ref = $m->getTitles($query, $sort, $titleFieldName);
        if (!$this->assertEqual($res = $v->getValueList(), $ref)) var_dump($res, $ref);
        
    }
    
}
<?php

class Ac_Test_CgImport extends Ac_Test_Base {
    
    var $reuse = false;
    
    function getConfigPath() {
        return dirname(__FILE__).'/../../../codegen/codegen.config.php';
    }
    
    function getOut2() {
        return dirname(__FILE__).'/../../../codegen/output2';
    }
    
    function getOut3() {
        return dirname(__FILE__).'/../../../codegen/output3';
    }
    
    function getLog2() {
        return dirname($this->getOut2()).'/log2.txt';
    }
    
    function getLog3() {
        return dirname($this->getOut3()).'/log3.txt';
    }
    
    function setUp() {
        if (!$this->reuse) {
            if (is_dir($this->getOut2())) Ac_Cg_Util::cleanDir ($this->getOut2());
            if (is_dir($this->getOut3())) Ac_Cg_Util::cleanDir ($this->getOut3());
        }
    }
    
    function testImport() {
        if (!$this->reuse) {
            $gen1 = new Ac_Cg_Generator($this->getConfigPath());
            $gen1->outputDir = $this->getOut2();
            $gen1->logFileName = $this->getLog2();
            $gen1->prepare();
            $gen1->genEditable = 1;
            $gen1->genNonEditable = 1;
            $gen1->clearOutputDir = 1;
            $gen1->run();
        }
        if ($this->assertTrue(is_file($samFile = $this->getOut2().'/gen/data/Sample.json'))) {
            $gen2 = new Ac_Cg_Generator(array());
            $gen2->outputDir = $this->getOut3();
            $gen2->logFileName = $this->getLog3();
            $gen2->genEditable = 1;
            $gen2->genNonEditable = 1;
            $gen2->clearOutputDir = 1;
            $dom = $gen2->importDomain($samFile, true);
            $this->assertIsA($dom, 'Ac_Cg_Domain');
            
            if ($this->assertTrue(is_file($childFile = $this->getOut2().'/gen/data/Child.json'))) {
                $dom = $gen2->importDomain($childFile, true);
                $this->assertIsA($dom, 'Ac_Cg_Domain');
            }
                
            $gen2->run();
        }
    }
    
}
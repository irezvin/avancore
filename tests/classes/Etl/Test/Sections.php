<?php

class Etl_Test_Sections extends Etl_Test_Class_Abstract {
    
    /**
     * @var Ac_Sql_Db
     */
    var $db = false;
    
    function testCsvWithSections() {
        
        $f = fopen(dirname(__FILE__).'/../samples/sections.csv', 'r');
        
        $csv = new Ac_Util_Csv_Sections(array(
            'delimiter' => ',',
            'useHeader' => true
        ));
        
        while (($line = fgets($f)) !== false) {
            $csv->pushLine($line);
        }
        
        if (!$this->assertEqual($r = $csv->getResult(), array ( 
            'types' => array ( 
                array ( 
                    'name' => 'typeC', 
                    'description' => 'typeC description', 
                ), 
                array ( 
                    'name' => 'typeD', 
                    'description' => 'typeD description', 
                ), 
            ), 
            'items' => array ( 
                array ( 
                    'name' => 'item1',
                    'type' => 'typeC', 
                ), 
                array ( 
                    'name' => 'item2', 
                    'type' => 'typeD', 
                    ),
                ), 
            )
        )) var_dump($r);
    }
    
    function testSectionsLoad() {
        $importer = new Etl_Test_Class_Importer();
        $importer->setDb($this->db);
        $importer->setSections(array(
           'types' => array(
               'loaderId' => 'types'
           ),
           'items' => array(
               'loaderId' => 'items'
           )
        ));
        $importer->setImportId(1);
        
        $f = fopen(dirname(__FILE__).'/../samples/sections.csv', 'r');
        
        $csv = new Ac_Util_Csv_Sections(array(
            'delimiter' => ',',
            'useHeader' => true
        ));
        
        $lineNo = 0;
        
        while (($line = fgets($f)) !== false) {
            $csv->pushLine($line);
            $lineNo++;
            $res = $csv->getResult(true);
            foreach ($res as $sectionId => $rows) {
                foreach ($rows as $row) 
                    $importer->getSection($sectionId)->pushLine($row, $lineNo);
            }
        }
        
        $this->assertSame($importer->getSection('types')->getLoader(), $importer->getLoader('types'));
        $this->assertSame($importer->getSection('items')->getLoader(), $importer->getLoader('items'));
        
        $this->assertEqual($importer->getSection('types')->getNumReceivedLines(), 2);
        $this->assertEqual($importer->getSection('items')->getNumReceivedLines(), 2);
        
        $importer->getSection('types')->endLoad();
        $importer->getSection('items')->endLoad();
        
        $this->assertEqual(
            $this->db->fetchArray("SELECT title, description from #__test_classifiers_import WHERE classifierType = 'type' ORDER BY title ASC"),
            array(
                array ( 
                    'title' => 'typeC', 
                    'description' => 'typeC description', 
                ), 
                array ( 
                    'title' => 'typeD', 
                    'description' => 'typeD description', 
                ), 
            )
        );
        
        $this->assertEqual(
            $this->db->fetchArray("SELECT name, type from #__test_items_import ORDER BY name ASC"),
            array(
                array ( 
                    'name' => 'item1',
                    'type' => 'typeC', 
                ), 
                array ( 
                    'name' => 'item2', 
                    'type' => 'typeD', 
                ),
            )
        );
        $importer->getSection('types')->process();
        $importer->getSection('items')->process();
        
    }
    
}
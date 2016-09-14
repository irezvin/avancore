<?php

class Etl_Test_Importer extends Etl_Test_Class_Abstract {
    
    /**
     * @var Test_Importer
     */
    var $importer = false;
    
    function setUp() {

        parent::setUp();
        $this->importer = new Etl_Test_Class_Importer;
        //$this->getSampleApp()->getDb()->getPdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->importer->setApplication($this->getSampleApp());
        $this->importer->setDb($this->db);
        $this->importer->setImportId(1);
        $this->importer->setImporterDbPrefix('im_');
        $this->importer->setTargetDbPrefix('im_');
        
    }

    function loadData($fName = "testData.csv") {
        
        $f = fopen(dirname(__FILE__).'/../samples/'.$fName, 'r');
        
        $csv = new Ac_Util_Csv(array(
            'delimiter' => ',',
            'useHeader' => true
        ));
        
        while (($line = fgets($f)) !== false) {
            $csv->pushLine($line);
        }
        
        $this->importer->getLoader('items')->begin();
        $this->importer->getLoader('items')->pushLines($csv->getResult(true));
        $this->importer->getLoader('items')->end();
        
    }
    
    function _testDbException() { // Todo: fix this
        $db = new Ac_Sql_Db_Ae();
        $e = null;
        try {
            $db->query("fepjfepfepof ekfeopkfepok fe");
        } catch (Exception $e) {
        }
        $this->assertTrue($e instanceof Ac_E_Database);
        
        $this->importer->setOperations(array(
            new Ac_Etl_Operation_Query(array(
                'sql' => 'FJEJFEJFOEJF EFKEOJFE'
            ))
        ));
        $this->importer->setLogger($lg = new Ac_Etl_Logger_Collector);
        $this->importer->setCatchExceptions(true);
        
        $e = null;
        
        try {
        } catch (Exception $e) {
        }
        $res = $this->importer->process();
        $this->assertIdentical($res, false);
        $this->assertIdentical($e, null);
        
    }
    
    function testImporter() {
        
        $this->loadData();
        
        // Basic data load test
        if (!$this->assertEqual(
            $arr = $this->db->fetchArray('SELECT name, description, type, importStatus, 
                relatedText1, pictureUrl, importId, lineNo FROM #__test_items_import ORDER BY id ASC'),
            array(
                array(
                    'name' => 'Item 1',
                    'description' => 'Item 1 description',
                    'type' => 'typeA',
                    'importStatus' => 'unprocessed',
                    'relatedText1' => 'Blah blah',
                    'pictureUrl' => 'https://www.google.com.ua/images/srpr/logo3w.png',
                    'importId' => 1,
                    'lineNo' => 0,
                ),
                array(
                    'name' => 'Item 2',
                    'description' => 'Item 2 description',
                    'type' => 'typeB',
                    'importStatus' => 'unprocessed',
                    'relatedText1' => 'blah blah blah',
                    'pictureUrl' => 'https://www.linux.com/templates/linuxcom_v2/images/lflogo_white.png',
                    'importId' => 1,
                    'lineNo' => 1,
                ),
                array(
                    'name' => 'Item 3',
                    'description' => 'Item 3 description',
                    'type' => 'typeA',
                    'importStatus' => 'unprocessed',
                    'relatedText1' => 'More text',
                    'pictureUrl' => 'http://noSuchAddress/noSuchImage.gif',
                    'importId' => 1,
                    'lineNo' => 2,
                ),
            )
        )) var_dump($arr);
        
        if (!$this->assertEqual(
            $arr = $this->db->fetchArray('SELECT categoryName, lineNo FROM #__test_item_categories_import WHERE importId = 1 ORDER BY id ASC'),
            array(
                array('categoryName' => 'subCat1.1', 'lineNo' => 0),
                array('categoryName' => 'cat2', 'lineNo' => 0),
                array('categoryName' => 'subCat3.1.1', 'lineNo' => 0),
                array('categoryName' => 'subCat1.1', 'lineNo' => 1),
                array('categoryName' => 'cat3', 'lineNo' => 1),
                array('categoryName' => 'subCat3.1.2', 'lineNo' => 2),
                array('categoryName' => 'subCat1.2', 'lineNo' => 2),
                )
        )) var_dump($arr);        
        
        if (!$this->assertEqual(
            $arr = $this->db->fetchArray('SELECT categoryName, parentName FROM #__test_categories_import WHERE importId = 1 ORDER BY id ASC'),
            array(
                array('categoryName' => 'cat1', 'parentName' => null),
                array('categoryName' => 'subCat1.1', 'parentName' => 'cat1'),
                array('categoryName' => 'cat2', 'parentName' => null),
                array('categoryName' => 'cat3', 'parentName' => null),
                array('categoryName' => 'subCat3.1', 'parentName' => 'cat3'),
                array('categoryName' => 'subCat3.1.1', 'parentName' => 'subCat3.1'),
                array('categoryName' => 'cat1', 'parentName' => null),
                array('categoryName' => 'subCat1.1', 'parentName' => 'cat1'),
                array('categoryName' => 'cat3', 'parentName' => null),
                array('categoryName' => 'cat3', 'parentName' => null),
                array('categoryName' => 'subCat3.1', 'parentName' => 'cat3'),
                array('categoryName' => 'subCat3.1.2', 'parentName' => 'subCat3.1'),
                array('categoryName' => 'cat1', 'parentName' => null),
                array('categoryName' => 'subCat1.2', 'parentName' => 'cat1'),
            )
         )) var_dump($arr);        
       
        $proc = $this->importer->getOperations();
        $proc['typeImporter']->process();
       
        if (!$this->assertEqual(
            $arr = $this->db->fetchColumn('SELECT name, id FROM #__test_types ORDER BY id ASC', 0, 'id'),
            array(
                1 => 'typeA',
                2 => 'typeB',
            )
        )) var_dump($arr);
       
        if (!$this->assertEqual(
            $arr = $this->db->fetchArray('SELECT `type`, typeId FROM #__test_items_import ORDER BY id ASC'),
            array(
                array('type' => 'typeA', 'typeId' => 1),
                array('type' => 'typeB', 'typeId' => 2),
                array('type' => 'typeA', 'typeId' => 1),
            )
        )) var_dump($arr);
        
        $exception = null;
        
        try {
        
            $proc['categoryOperation']->process();
            
        } catch (Ac_Etl_Exception $e) {
            
            $exception = $e;
            
        }
        
        if (!$this->assertIdentical($exception, null)) var_dump(''.$exception);        
        
        $arr = $this->db->fetchArray(
            'SELECT itemId, categoryId FROM #__test_item_categories' 
        );

        
        // This will FAIL since we haven't create any items yet
        // $this->assertTrue(count($arr));
        
        // @TODO: import items BEFORE categoryOperation 
        // and check category-to-item relations then
        
       
        $this->writeStats($this->importer);
       
    }
    
}
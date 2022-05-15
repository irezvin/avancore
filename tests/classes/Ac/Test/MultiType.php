<?php

class Ac_Test_MultiType extends Ac_Test_Base {
    
    protected $bootSampleApp = true;
    
    function testMixableTyperSeveralMappers () {
        $typer = new Ac_Model_Typer_ExtraTable (array(
            'app' => Sample::getInstance(),
            'objectTypeField' => 'sharedObjectType',
            'tableName' => '#__publish',
        ));
        $rows = $this->getAeDb()->fetchArray("SELECT * FROM #__publish WHERE id IN (118, 117, 1) ORDER BY id DESC");
        $objects = $typer->instantiateSet($rows);
        $objInfo = array();
        foreach ($objects as $k => $v) {
            $objInfo[$v->pubId] = array('class' => get_class($v), 'id' => $v->getIdentifier());
        }
        $this->assertEqual(count($objInfo), 3);
        if (!$this->assertArraysMatch($objInfo, array(
            1 => array('class' => 'Sample_Shop_Product', 'id' => 1),
            117 => array('class' => 'Sample_Person_Post', 'id' => 3),
            118 => array('class' => 'Sample_Person_Post', 'id' => 2),
        ))) {
            var_dump($objInfo);
            Ac_Debug::drr($objects);
        }
        if (!$this->assertEqual(array_keys($objInfo), array(118, 117, 1))) var_dump(array_keys($objects));
    }
    
    function testMixableTyperOneMapper () {
        $typer = new Ac_Model_Typer_ExtraTable (array(
            'app' => Sample::getInstance(),
            'uniformTypeId' => 'Sample_Shop_Product_Mapper',
            'tableName' => '#__shop_product_notes',
        ));
        $rows = $this->getAeDb()->fetchArray("SELECT * FROM #__shop_product_notes ORDER BY productId ASC", 'productId');
        $objects = Ac_Util::indexArray($typer->instantiateSet($rows), 'id', true);
        $cl = array();
        foreach ($objects as $o) $cl[get_class($o)] = true;
        $this->assertEqual(array_keys($objects), array_keys($rows));
        $this->assertEqual(array_keys($cl), array('Sample_Shop_Product'));
    }
    
    function testExtraTableMixin() {
        $pub = Sample::getInstance()->getSamplePublishImplMapper();
        $pub->reset();
        $items = $pub->loadRecordsArray(array(117, 118, 1));
        if (!$this->assertArraysMatch(array(
                array('__class' => 'Sample_Person_Post', 'pubId' => 117, 'id' => 3),
                array('__class' => 'Sample_Person_Post', 'pubId' => 118, 'id' => 2),
                array('__class' => 'Sample_Shop_Product', 'pubId' => 1, 'id' => 1),
        ), $r = Ac_Debug::dr($items), '%s')) var_dump($r);
        
        $typer = $pub->getMixable('Ac_Model_Typer_Abstract');
        $tmp = $typer->getMapperHandlerEnabled();
        // now disable mapper handler and see if it will return Ac_Model_Record instances
        $typer->setMapperHandlerEnabled(false);
        $items = $pub->loadRecordsArray(array(117, 118, 1));
        if (!$this->assertArraysMatch(array(
                array('__class' => 'Ac_Model_Record', 'sharedObjectType' => 'Sample_Person_Post_Mapper', 'id' => 117),
                array('__class' => 'Ac_Model_Record', 'sharedObjectType' => 'Sample_Person_Post_Mapper', 'id' => 118),
                array('__class' => 'Ac_Model_Record', 'sharedObjectType' => 'Sample_Shop_Product_Mapper', 'id' => 1),
        ), $r = Ac_Debug::dr($items), '%s')) var_dump($r);
        $typer->setMapperHandlerEnabled($tmp);
        
        $pMapper = Sample::getInstance()->getSamplePersonPostMapper();
        $p = $pMapper->loadRecordsArray(array(2, 3), true);
        
        // fails when there is a regression caused by handling of Ac_Model_Relation_Abstract::RESULT_ORIGINAL_KEYS mode
        if (!$this->assertArraysMatch(array(
                2 => array('__class' => 'Sample_Person_Post', 'id' => 2, 'authorId' => 3, 'editorId' => 3, 'pubId' => 118),
                3 => array('__class' => 'Sample_Person_Post', 'id' => 3, 'authorId' => 3, 'editorId' => 7, 'pubId' => 117),
        ), $r = Ac_Debug::dr($p), '%s')) var_dump($r);
    }
    
    function testExtraTableMixinCollection() {
        // now the hard part: test the collection
        $pub = Sample::getInstance()->getSamplePublishImplMapper();
        $pub->reset();
        // now the hard part: test the collection
        //$pub->setIdentifierPublicField('Sample_Publish::id');
        $pub->reset();
        
        $productMapper = Sample::getInstance()->getSampleShopProductMapper();
        $postMapper = Sample::getInstance()->getSamplePersonPostMapper();
        $tmp = array(
            $productMapper->useRecordsCollection, 
            $postMapper->useRecordsCollection,
            $pub->useRecordsCollection
        );
        $productMapper->reset();
        $postMapper->reset();
        list(
            $productMapper->useRecordsCollection, 
            $postMapper->useRecordsCollection, 
            $pub->useRecordsCollection
        ) = array(true, true, true);
        
        $items1 = $pub->loadRecordsArray(array(117, 118, 1), true);
        if (!$this->assertArraysMatch(array(
                117 => array('__class' => 'Sample_Person_Post', 'pubId' => 117, 'id' => 3),
                118 => array('__class' => 'Sample_Person_Post', 'pubId' => 118, 'id' => 2),
                1 => array('__class' => 'Sample_Shop_Product', 'pubId' => 1, 'id' => 1),
        ), $r = Ac_Debug::dr($items1), '%s')) var_dump($r);
        
        $items2 = $pub->loadRecordsArray(array(117, 118, 1), true);
        $this->assertTrue($items1[1] === $items2[1] && $items1[117] === $items2[117] && $items1[118] === $items2[118]);
        
        $items2 = $pub->find(array(new Ac_Sql_Expression('id IN (1, 117, 118)')), true);
        $this->assertTrue($items1[1] === $items2[1] && $items1[117] === $items2[117] && $items1[118] === $items2[118]);

        $items2 = array(
            117 => $postMapper->loadById($items1[117]->id),
            118 => $postMapper->loadById($items1[118]->id),
            1 => $productMapper->loadById($items1[1]->id),
        );
        $this->assertTrue($items1[1] === $items2[1] && $items1[117] === $items2[117] && $items1[118] === $items2[118]);

        $prod = $items1[1];
        
        $found = 0;
        foreach ($prod->getRegisteredObjectCollections() as $item) {
            if ($item === $pub || $item === $productMapper) $found++;
        }
        $this->assertTrue($found === 2);
        
        $this->assertEqual($pub->getIdentifierOfObject($items1[117]), 117);
        $this->assertEqual($postMapper->getIdentifierOfObject($items1[117]), 3);
        $this->assertSame($items1[117]->getMapper(), $postMapper);
        
        $this->assertEqual(array(
            count($prod->getRegisteredObjectCollections()), 
            count($postMapper->getRegisteredObjects()), 
            count($productMapper->getRegisteredObjects()),
            count($pub->getRegisteredObjects())
        ), array(2, 2, 1, 3));
        $prod->cleanupMembers();
        $this->assertEqual(array(
            count($prod->getRegisteredObjectCollections()), 
            count($postMapper->getRegisteredObjects()), 
            count($productMapper->getRegisteredObjects()),
            count($pub->getRegisteredObjects())
        ), array(0, 2, 0, 2));
        
        list(
            $productMapper->useRecordsCollection, 
            $postMapper->useRecordsCollection, 
            $pub->useRecordsCollection
        ) = $tmp;
        
        $pub->reset();
        $productMapper->reset();
        $postMapper->reset();
        
        
        $a = Sample::getInstance()->getSamplePersonMapper()->loadRecord(3);
        $publications = array();
        foreach ($a->listAuthorPublish() as $i) {
            $p = $a->getAuthorPublish($i);
            $publications[$p->pubId] = $p;
        }
        if (!$this->assertArraysMatch(array(
                117 => array('__class' => 'Sample_Person_Post', 'pubId' => 117, 'id' => 3),
                118 => array('__class' => 'Sample_Person_Post', 'pubId' => 118, 'id' => 2),
                1 => array('__class' => 'Sample_Shop_Product', 'pubId' => 1, 'id' => 1),
        ), $r = Ac_Debug::dr($publications), '%s')) var_dump($r);
    }
    
    function testExtraTableMixinRelation() {
        // now the hard part: test the collection
        $pub = Sample::getInstance()->getSamplePublishImplMapper();
        $pub->reset();
        
        // now the hard part: test the collection
        //$pub->setIdentifierPublicField('Sample_Publish::id');
        $pub->reset();
        
        $a = Sample::getInstance()->getSamplePersonMapper()->loadRecord(3);
        $publications = array();
        foreach ($a->listAuthorPublish() as $i) {
            $p = $a->getAuthorPublish($i);
            $publications[$p->pubId] = $p;
        }
        if (!$this->assertArraysMatch(array(
                117 => array('__class' => 'Sample_Person_Post', 'pubId' => 117, 'id' => 3),
                118 => array('__class' => 'Sample_Person_Post', 'pubId' => 118, 'id' => 2),
                1 => array('__class' => 'Sample_Shop_Product', 'pubId' => 1, 'id' => 1),
        ), $r = Ac_Debug::dr($publications), '%s')) var_dump($r);
    }
    
    function testSimpleMultiType() {
        $m = Sample::getInstance()->getSampleShopProductMapper();
        $p = $m->find(array(new Ac_Sql_Expression("sku LIKE 'mc%'")));
        $m->loadShopSpecsFor($p);
        $s = array();
        foreach ($p as $prod) {
            if ($sp = $prod->getShopSpec()) $s[$sp->productId] = $sp;
        }
        if (!$this->assertArraysMatch(array( 
            10 => array ('__class' => 'Sample_Shop_Spec', 'productId' => '10', 'detailsUrl' => 'http://www.example.com/food1', 'specsType' => 'Sample_Shop_Spec_Mapper_Food', 'storageType' => 'frozen', 'storageTerm' => '6', 'storageTermUnit' => 'months', ), 
            11 => array ('__class' => 'Sample_Shop_Spec', 'productId' => '11', 'detailsUrl' => 'http://www.example.com/computer1', 'specsType' => 'Sample_Shop_Spec_Mapper_Computer', 'hdd' => '1024', 'ram' => '16', 'os' => 'Ubuntu Linux', ), 
            12 => array (
                '__class' => 'Sample_Shop_Spec', 'productId' => '12', 'detailsUrl' => 'http://www.example.com/laptop1', 
                'specsType' => 'Sample_Shop_Spec_Mapper_Laptop', 
                'hdd' => '512', 'ram' => '8', 'os' => 'Windows 10', 
                'diagonal' => '15.0', 'hRes' => '1280', 'vRes' => '768', //'matrixType' => 'LED', 
                'weight' => 2, 'battery' => 6,
            ), 
            13 => array (
                '__class' => 'Sample_Shop_Spec', 'productId' => '13', 'detailsUrl' => 'http://www.example.com/laptop2', 
                'specsType' => 'Sample_Shop_Spec_Mapper_Laptop', 
                'hdd' => '256', 'ram' => '8', 'os' => 'Arch Linux', 
                'diagonal' => '17.0', 'hRes' => '1680', 'vRes' => '1050', //'matrixType' => 'LCD', 
                'weight' => 3, 'battery' => 4,
            ), 
        ), $r = Ac_Debug::dr($s), '%s')) var_dump($r);
        $lapMap = $s[12]->getMapper();
        $this->assertEqual($lapMap->getId(), 'Sample_Shop_Spec_Mapper_Laptop');
        $laps = $lapMap->getAllRecords();
        $this->assertEqual(count($laps), 2);
        if (!$this->assertArraysMatch(array( 
            12 => array ('__class' => 'Sample_Shop_Spec', 'productId' => '12', 'detailsUrl' => 'http://www.example.com/laptop1', 'specsType' => 'Sample_Shop_Spec_Mapper_Laptop', 'hdd' => '512', 'ram' => '8', 'os' => 'Windows 10', 'diagonal' => '15.0', 'hRes' => '1280', 'vRes' => '768', /*'matrixType' => 'LED',*/ ), 
            13 => array ('__class' => 'Sample_Shop_Spec', 'productId' => '13', 'detailsUrl' => 'http://www.example.com/laptop2', 'specsType' => 'Sample_Shop_Spec_Mapper_Laptop', 'hdd' => '256', 'ram' => '8', 'os' => 'Arch Linux', 'diagonal' => '17.0', 'hRes' => '1680', 'vRes' => '1050', /*'matrixType' => 'LCD',*/ ), 
        ), $r = Ac_Debug::dr($laps), '%s')) var_dump($r);
        $new = $lapMap->createRecord();
        $this->assertTrue(count($new->listMixables('Sample_Shop_Spec_Computer')) == 1);
        $this->assertTrue(count($new->listMixables('Sample_Shop_Spec_Monitor')) == 1);
        $this->assertTrue(count($new->listMixables('Sample_Shop_Spec_Laptop')) == 1);
    }
    
}
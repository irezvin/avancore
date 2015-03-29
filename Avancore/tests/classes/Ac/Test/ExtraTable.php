<?php

class Ac_Test_ExtraTable extends Ac_Test_Base {
    
    protected $bootSampleApp = true;
    
    function testExtraTable() {
        $mapper = Sample::getInstance()->getSampleShopProductMapper();
        
        // Not directly related to the ExtraTable... but saw that bug when running the tests
        $mixable = $mapper->getMixable('upc');
        $this->assertEqual($mixable->getMixableId(), 'upc');
        
        // Works when loading one object (peLoad)
        $prod = $mapper->loadById(1);
        $this->assertEqual($prod->metaDescription, 'Страница товара 1');
        $this->assertEqual($prod->upcCode, '1234');
        
        // Works when loading two objects (loadFromRows)
        $twoProds = $mapper->loadRecordsArray(array(1, 2), true);
        $this->assertEqual($twoProds[1]->metaDescription, 'Страница товара 1');
        $this->assertEqual($twoProds[1]->upcCode, '1234');
        
        // Second object has default values
        $this->assertEqual($twoProds[2]->metaId, null);
        $this->assertEqual($twoProds[2]->metaDescription, null);
        $this->assertEqual($twoProds[2]->metaNoindex, '0');
        $this->assertEqual($twoProds[2]->upcCode, '');
        
        $prodId = $this->resetAi($mapper->tableName);
        $metaId = $this->resetAi('#__shop_meta');
        
        $newProd = $mapper->createRecord();
        $data = array(
            'id' => $prodId,
            'title' => 'Product3',
            'sku' => 'PROD03',
            'pageTitle' => '-=product-03-title=-',
            'upcCode' => '5678',
        );
        $newProd->bind($data);
        $res = $newProd->store();
        $db = $mapper->getDb();
        if ($this->assertTrue($res, 'Owner record row must report it is saved')) {
            $this->assertEqual($newProd->metaId, $metaId);
            $prodRow = $db->args($prodId)->fetchRow('SELECT * FROM #__shop_products WHERE id = ?');
            if ($this->assertTrue(is_array($prodRow), 'Owner record row must appear in the primary table')) {
                $this->assertEqual($prodRow['title'], $data['title']);
                $this->assertEqual($prodRow['metaId'], $metaId);
            }
            $metaRow = $db->args($metaId)->fetchRow('SELECT * FROM #__shop_meta WHERE id = ?');
            if ($this->assertTrue(is_array($metaRow), 'Extra row must appear in referenced extra table')) {
                $this->assertEqual($metaRow['pageTitle'], $data['pageTitle']);
                $this->assertEqual($metaRow['sharedObjectType'], 'product');
            }
            $upcRow = $db->args($prodId)->fetchRow('SELECT * FROM #__shop_product_upc WHERE productId = ?');
            if ($this->assertTrue(is_array($upcRow), 'Extra row must appear in referencing extra table')) {
                $this->assertEqual($upcRow['upcCode'], $data['upcCode']);
            }

            $res = $newProd->delete();
            $this->assertTrue($res);
            $this->assertEqual(
                $db->args($metaId)->fetchValue("SELECT COUNT(*) FROM #__shop_meta WHERE id = ?"),
                0,
                "Referenced extra row must be deleted after the record is deleted"
            );
            $this->assertEqual(
                $db->args($prodId)->fetchValue("SELECT COUNT(*) FROM #__shop_product_upc WHERE productId = ?"),
                0,
                "Referencing extra row must be deleted after the record is deleted"
            );
            // clean up
        }
        $db->args($data['pageTitle'])->query("DELETE FROM #__shop_meta WHERE pageTitle = ?");
        $db->args($data['upcCode'])->query("DELETE FROM #__shop_product_upc WHERE upcCode = ?");
    }
    
}
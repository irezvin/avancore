<?php

class Ac_Test_ExtraTable extends Ac_Test_Base {
    
    protected $bootSampleApp = true;
    
    function testExtraTable() {
        $mapper = Sample::getInstance()->getSampleShopProductMapper();
        $this->resetAi('#__shop_meta');
        $this->resetAi('#__publish');
        
        // Not directly related to the ExtraTable... but saw that bug when running the tests
        $mixable = $mapper->getMixable('upc');
        $this->assertEqual($mixable->getMixableId(), 'upc');
        
        // Works when loading one object (peLoad)
        $prod = $mapper->loadById(1);
        $this->assertEqual($prod->getField('id'), 1);
        $this->assertEqual($prod->metaDescription, 'Страница товара 1');
        $this->assertEqual($prod->upcCode, '1234');
        
        // Works when loading two objects (loadFromRows)
        $twoProds = $mapper->loadRecordsArray(array(1, 2), true);
        
        //Ac_Debug::drr($twoProds);
        
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
    
    function testMixableExtraTable() {
        $mapper = Sample::getInstance()->getSampleShopProductMapper();
        $prod = $mapper->loadById(1);
        $author = $prod->getAuthorPerson();
        if ($this->assertTrue(is_object($author))) {
            $this->assertEqual($author->personId, $prod->authorId);
        }
        $editor = $prod->getEditorPerson();
        if ($this->assertTrue(is_object($editor))) {
            $this->assertEqual($editor->personId, $prod->editorId);
        }
        
        $this->resetAi('#__shop_products');
        $this->deleteProducts("p.title LIKE '%test%'");
        $this->getAeDb()->query("DELETE FROM #__people WHERE name LIKE '%test%'");
        $this->resetAi('#__people');
        
        $testProd = $mapper->createRecord();
        $testProd->bind(array('title' => 'test product', 'sku' => '1337'));
        
        $author = $testProd->createAuthorPerson();
        $author->bind(array('name' => 'test author', 'birthDate' => date('1990-01-01')));
        
        $editor = $testProd->createEditorPerson();
        $editor->bind(array('name' => 'test editor', 'birthDate' => date('1990-02-02')));
        
        if ($this->assertTrue(!!$testProd->store())) {
            $row = $this->getAeDb()->args($testProd->id)->fetchRow("SELECT * FROM #__shop_products WHERE id = ?");
            if ($this->assertTrue(is_array($row))) {
                $pubId = $row['pubId'];
                $this->assertEqual($testProd->pubId, $pubId, 'Master object field that stores reference '
                    . 'to an extra table field must contain valid extra table key after save');
                if ($pubId) {
                    $pubRow = $this->getAeDb()->args($pubId)->fetchRow("SELECT * FROM #__publish WHERE id = ?");
                    if ($this->assertTrue(is_array($pubRow))) {
                        // actually, that shouldn't work at the moment
                        $this->assertEqual($pubRow['sharedObjectType'], 'product');
                    }
                }
                $metaId = $row['metaId'];
                $this->assertEqual($testProd->metaId, $metaId, 'Master object field that stores reference '
                    . 'to an extra table field must contain valid extra table key after save');
                if ($metaId) {
                    $metaRow = $this->getAeDb()->args($metaId)->fetchRow("SELECT * FROM #__shop_meta WHERE id = ?");
                    if ($this->assertTrue(is_array($metaRow))) {
                        // actually, that shouldn't work at the moment
                        $this->assertEqual($metaRow['sharedObjectType'], 'product');
                    }
                }
            }
        }
        
    }
    
    function testAssocThatCantLoadDest() {
        $persons = $this->getSampleApp()->getSamplePersonMapper();
        $prods =  $this->getSampleApp()->getSampleShopProductMapper();
        $persons->useRecordsCollection = true;
        
        $p3 = $persons->loadByPersonId(3);
        $this->assertEqual($p3->_extraCodeShopProducts, false);
        $p3->listExtraCodeShopProducts();
        $this->assertEqual($p3->_extraCodeShopProducts, array());
        
        $prod = $prods->loadById(2);
        $prodPerson = $prod->getExtraCodePerson();
        if ($this->assertTrue(is_object($prodPerson))) {
            $this->assertSame($prodPerson, $p3);
            if ($this->assertEqual(count($prodPerson->listExtraCodeShopProducts()), 1)) {
                $this->assertSame($prodPerson->getExtraCodeShopProduct(0), $prod);
            }
        }
            
        $persons->useRecordsCollection = false;
        $persons->reset();
    }
    
    function testReferencingAssoc() {
        $db = $this->getSampleApp()->getDb();
        
        $this->getAeDb()->query("DELETE p.*, pub.* FROM #__shop_products p LEFT JOIN #__publish pub ON pub.id = p.pubId WHERE p.title = 'test prod 2'");
        $db->query("DELETE FROM #__people WHERE name = 'test prod author'");
        
        // We can create the record that is referenced by the extra table and it will 
        // be saved by cascade
        
        $persons = $this->getSampleApp()->getSamplePersonMapper();
        $prods = $this->getSampleApp()->getSampleShopProductMapper();
        $newProd = $prods->createRecord();
        $newProd->bind($a = array(
            'sku' => 'f00',
            'title' => 'test prod 2',
            'ean' => 'A',
            'asin' => 'B',
            'gtin' => 'C',
        ));

        $author = $newProd->createExtraCodePerson();
        $author->bind($b = array(
            'name' => 'test prod author',
            'gender' => 'M', 
            'birthDate' => '2015-02-05',
        ));
        $newProd->store();
        
        $this->assertTrue($newProd->isPersistent());
        $this->assertTrue($author->isPersistent());
        $prodRow = $db->args($a['title'])->fetchRow(
            $pQuery = 'SELECT p.sku, p.title, c.ean, c.asin, c.gtin, u.name, u.gender, u.birthDate '
            . 'FROM #__shop_products p INNER JOIN #__shop_product_extraCodes c ON c.productId = p.id '
            . 'INNER JOIN #__people u ON u.personId = c.responsiblePersonId '
            . 'WHERE p.title = ?');
        $this->assertEqual($prodRow, array_merge($a, $b));
        
        $this->deleteProducts("p.title = 'test prod 3'");
        $db->query("DELETE FROM #__people WHERE name = 'test prod author 2'");

        // We cannot load product through extra table, but we can successfully save it from referencing record
        $girl = $persons->createRecord();
        $girl->bind($c = array(
            'name' => 'test prod author 2',
            'gender' => 'F',
            'birthDate' => '2015-02-05',
        ));
        $prod2 = $girl->createExtraCodeShopProduct($d = array(
            'sku' => 'f01',
            'title' => 'test prod 3',
            'ean' => 'A1',
            'asin' => 'B1',
            'gtin' => 'C1',
        ));
        $girl->store();
        
        $this->assertTrue($prod2->isPersistent());
        $this->assertTrue($girl->isPersistent());
        
        $prodRow = $db->args($d['title'])->fetchRow(
            $pQuery = 'SELECT p.sku, p.title, c.ean, c.asin, c.gtin, u.name, u.gender, u.birthDate '
            . 'FROM #__shop_products p INNER JOIN #__shop_product_extraCodes c ON c.productId = p.id '
            . 'INNER JOIN #__people u ON u.personId = c.responsiblePersonId '
            . 'WHERE p.title = ?');
        $this->assertEqual($prodRow, array_merge($d, $c));
    }
    
    function testExtraTableSqlSelect() {
        $prodMap = $this->getSampleApp()->getSampleShopProductMapper();
        $catMap = $this->getSampleApp()->getSampleShopCategoryMapper();
        $sql = $catMap->createSqlSelect();
        $this->assertTrue($sql->hasTable($alias = 'shopProducts[extra__upc]'));
        $sql->useAlias($alias);
        $stmt = ''.$sql;
        
        $prodSql = $prodMap->createSqlSelect();
        if ($this->assertTrue($prodSql->hasTable($alias = 'extra__publish'))) 
            $prodSql->useAlias($alias);
        $stmt = ''.$prodSql;
        //var_dump($stmt);
    }
    
    function testInlineGenExtraTable() {
        $prodMap = $this->getSampleApp()->getSampleShopProductMapper();
        $db = $this->getAeDb();
        $this->deleteProducts("p.sku = 'PROD_NOTE'");
        $db->query("DELETE FROM #__people WHERE name = 'Author of a note'");
        $prod = $prodMap->createRecord();
        $prod->bind($a = array(
            'sku' => 'PROD_NOTE',
            'title' => 'product with a note',
            'note' => 'foobar',
        ));
        $author = $prod->createNotePerson();
        $author->bind($b = array(
            'name' => 'Author of a note', 
            'gender' => 'M',
            'birthDate' => '2014-02-14',
        ));
        $prod->store();
        $this->assertTrue($prod->isPersistent());
        $this->assertTrue($author->isPersistent());
        $prodRow = $db->args($a['title'])->fetchRow(
            $pQuery = 'SELECT p.sku, p.title, n.note, n.noteAuthorId, u.name, u.gender, u.birthDate '
            . 'FROM #__shop_products p INNER JOIN #__shop_product_notes n ON n.productId = p.id '
            . 'INNER JOIN #__people u ON u.personId = n.noteAuthorId '
            . 'WHERE p.title = ?');
        $a['noteAuthorId'] = $prod->noteAuthorId;
        $this->assertEqual($prodRow, array_merge($a, $b));
//        if ($this->assertTrue(in_array(0, $author->listNoteShopProducts())))
//            $this->assertSame($author->getNoteShopProduct(0), $prod);
//       
    }
    
}
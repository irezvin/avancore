<?php

class Ac_Test_Relation extends Ac_Test_Base {
    
    protected $bootSampleApp = true;

    function testLoadNonSql() {
        /*$pm = Sample::getInstance()->getSamplePersonMapper();
        $pm->reset();
        $rel = clone $pm->getRelation('_portraitPersonPhoto');
        $peop = $pm->find(array('notTest' => true), true);
        $rel->setDestNonSql(true);
        $dest = $rel->loadDest($peop);
        if ($this->assertIsA($p = $peop[3]->getPortraitPersonPhoto(), 'Sample_Person_Photo')) {
            $this->assertEqual($p->personId, $peop[3]->personId);
            $this->assertEqual($p->photoId, $peop[3]->portraitId);
        }
        if ($this->assertIsA($p = $peop[4]->getPortraitPersonPhoto(), 'Sample_Person_Photo')) {
            $this->assertEqual($p->personId, $peop[4]->personId);
            $this->assertEqual($p->photoId, $peop[4]->portraitId);
        }*/
        $pm = Sample::getInstance()->getSamplePersonMapper();
        $pm->reset();
        $rel = clone $pm->getRelation('_tags');
        $rel->setSrcLoadNNIdsMethod(false);
        $peop = $pm->find(array('notTest' => true), true);
        $rel->setDestNonSql(true);
        $dest = $rel->loadDest($peop);
        $ok = true;
        foreach ($peop as $p) {
            $ok = $ok && is_array($p->_tagIds) && is_array($p->_tags) && count($p->_tags) == count($p->_tagIds)
                && Ac_Util::getObjectProperty($p->_tags, 'tagId') == $p->_tagIds;
        }
        $this->assertTrue($ok, 'All tags are loaded by non-sql relation');
    }
    
    function testRelationProviderConfig() {
        $pm = Sample::getInstance()->getSamplePersonMapper();
        $rel = Sample::getInstance()->getSampleReligionMapper();
        if (!$this->assertIsA($pm->getRelation('_religion')->getDestProvider(), 'Ac_Model_Relation_Provider_Mapper_Pk')) {
            var_dump($a);
        }
        if (!$this->assertIsA($pm->getRelation('_religion')->getSrcProvider(), 'Ac_Model_Relation_Provider_Mapper_Omni')) {
            var_dump($b);
        }
    }
    
    function testCardinality() {
        $a = new Ac_Model_Relation(
            array(
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Religion_Mapper',
                'fieldLinks' => array('religionId' => 'religionId'),
            )
        );
        $this->assertFalse($a->getSrcIsUnique());
        $this->assertTrue($a->getDestIsUnique());
    }
    
    function testLoadNoSrcVar() {
        $pm = Sample::getInstance()->getSamplePersonMapper();
        $person = $pm->loadByPersonId(3);
        $rel = clone $pm->getRelation('_religion');
        $rel->srcVarName = false;
        $pp = array($person);
        $r = $rel->loadDest($pp);
        $this->assertTrue(is_array($r));
        $person = $pm->loadByPersonId(3);
        $rel = clone $pm->getRelation('_religion');
        $rel->srcVarName = false;
        $pp = array($person);
        $r = $rel->loadDest($person);
        $this->assertTrue(is_array($r));
        
        $person = $pm->loadByPersonId(3);
        $rel = clone $pm->getRelation('_religion');
        $rel->destVarName = false;
        $pp = array($person);
        $r = $rel->loadDest($pp);
        $this->assertTrue(is_array($r));
        $person = $pm->loadByPersonId(3);
        $rel = clone $pm->getRelation('_religion');
        $rel->srcVarName = false;
        $pp = array($person);
        $r = $rel->loadDest($person);
        $this->assertTrue(is_array($r));
        
        $person = $pm->loadByPersonId(3);
        $rel = clone $pm->getRelation('_religion');
        $rel->destVarName = false;
        $rel->srcVarName = false;
        $pp = array($person);
        $r = $rel->loadDest($pp);
        $this->assertTrue(is_array($r));
        $person = $pm->loadByPersonId(3);
        $rel = clone $pm->getRelation('_religion');
        $rel->srcVarName = false;
        $pp = array($person);
        $r = $rel->loadDest($person);
        $this->assertTrue(is_array($r));
        
    }
    
    function testPartialLoad() {
        $pm = Sample::getInstance()->getSamplePersonMapper();
        $rm = Sample::getInstance()->getSampleReligionMapper();
        
        $pers = $pm->loadByPersonId(3);
        $pers2 = $pm->loadByPersonId(4);
        $rel1 = $pers->getReligion();
        $rel2 = $pers2->getReligion();
        
        $this->assertTrue($pers->isAssocLoaded('religion'));
        $this->assertFalse($rel1->isAssocLoaded('people'));
        
        $lp1 = $rel1->listPeople();
        $lp2 = $rel2->listPeople();
        
        $rel1a = $rm->loadByReligionId($rel1->religionId);
        $rel2a = $rm->loadByReligionId($rel1->religionId);
        $lp1a = $rel1a->listPeople();
        $lp2a = $rel2a->listPeople();
        
        $this->assertEqual($lp1, $lp1a);
        $this->assertEqual($lp2, $lp2a);
        
        $pers = $pm->loadByPersonId(3);
        $this->assertSame($pers->getReligion()->_people[0], $pers);
        $this->assertFalse($pers->getReligion()->isAssocLoaded('people'));
        $recs = $pm->loadForReligion(array($pers->getReligion()));
        $this->assertTrue($pers->getReligion()->isAssocLoaded('people'));
        $tmp = $pers->getReligion();
        $this->assertSame($tmp, $pers->getReligion());
        $this->assertTrue($pers->isAssocLoaded('religion'));
        $this->assertSame($pers->getReligion()->_people[0], $pers);
        
        $rel1a = $rm->loadByTitle('christian');
        $list = $rel1a->listPeople();
        
        $rel1 = $rm->loadByTitle('christian');
        $pers = $rel1->createPerson();
        $this->assertEqual(count($rel1->listPeople()), count($rel1a->listPeople()) + 1, 
            'Ensure in-memory records are not touched by association\' load');
        
        $rel = $rm->loadByTitle('christian');
        $p1 = $rel->getPerson(0);
        $this->assertTrue($rel->isAssocLoaded('people'));
        $iid = $p1->instanceId;
        $p1->cleanupMembers();
        unset($p1);
        $this->assertTrue(isset(Sample_Person::$destructed[$iid]));
        $this->assertFalse($rel->isAssocLoaded('people'));
    }
    
    function testLoadReturnsRecords() {
        $pm = Sample::getInstance()->getSamplePersonMapper();
        
        $pers = $pm->loadByPersonId(3);
        $pers2 = $pm->loadByPersonId(4);
        $pers->setTagIds(array(3));
        $records = $pm->loadTagsFor(array($pers, $pers2));
        $records = Ac_Util::indexArray(Ac_Util::flattenArray($records), 'tagId', true);
        ksort($records);
        $this->assertEqual(array_keys($records), array(1, 2, 3));
        
        $pers = $pm->loadByPersonId(3);
        $pers2 = $pm->loadByPersonId(4);
        $pers->setTagIds(array(1, 3));
        $pers2->setTagIds(array());
        
        $records = $pm->loadTagsFor(array($pers, $pers2));
        $records = Ac_Util::indexArray(Ac_Util::flattenArray($records), 'tagId', true);
        ksort($records);
        $this->assertEqual(array_keys($records), array(1, 3));

        // second load
        $records = $pm->loadTagsFor(array($pers, $pers2));
        $this->assertEqual(array_keys($records), array('__alreadyLoaded'));
        $records = Ac_Util::indexArray(Ac_Util::flattenArray($records), 'tagId', true);
        ksort($records);
        $this->assertEqual(array_keys($records), array(1, 3));
        
        $pers = $pm->loadByPersonId(3);
        $pers->setTagIds(array());
        $pers2 = $pm->loadByPersonId(4);
        $pm->loadTagsFor($pers);
        $records = $pm->loadTagsFor(array($pers, $pers2));
        $this->assertTrue(!array_key_exists('__alreadyLoaded', $records));
        
        $pers = $pm->loadByPersonId(3);
        $pers->setTagIds(array(1, 3));
        $pers2 = $pm->loadByPersonId(4);
        $pm->loadTagsFor($pers);
        $records = $pm->loadTagsFor(array($pers, $pers2));
        $this->assertTrue(isset($records['__alreadyLoaded']) && count($records['__alreadyLoaded'][0]) == 2);
    }

    function testAmrPlainResult() {
        $pm = Sample::getInstance()->getSamplePersonMapper();
        
        $a = $pm->loadByPersonId(3);
        $b = $pm->loadByPersonId(4);
        
        $rel = clone $pm->getRelation('_tags');
        $rel->setDestOrdering('tagId DESC');
        $a->setTagIds(array(3));
        $d = $rel->getDest(array($a, $b), Ac_Model_Relation_Abstract::RESULT_PLAIN);
        if (!$this->assertArraysMatch(array(
            array('tagId' => 3, '__class' => 'Sample_Tag'),
            array('tagId' => 2, '__class' => 'Sample_Tag'),
            array('tagId' => 1, '__class' => 'Sample_Tag'),
        ), $d))
            Ac_Debug::drr($d);
        
        $a = $pm->loadByPersonId(3);
        $b = $pm->loadByPersonId(4);
        $rel = clone $pm->getRelation('_tags');
        $rel->setDestOrdering('tagId DESC');
        $a->setTagIds(array(1));
        $b->setTagIds(array(3));
        $d = $rel->getDest(array($a, $b), Ac_Model_Relation_Abstract::RESULT_PLAIN);
        
        if (!$this->assertArraysMatch(array(
            array('tagId' => 3, '__class' => 'Sample_Tag'),
            array('tagId' => 1, '__class' => 'Sample_Tag'),
        ), $d))
            Ac_Debug::drr($d);
        
        
        $d = $rel->getDest($b, Ac_Model_Relation_Abstract::RESULT_PLAIN);
        if (!$this->assertArraysMatch(array(
            array('tagId' => 3, '__class' => 'Sample_Tag'),
        ), $d))
            Ac_Debug::drr($d);
        
        $this->assertEqual($d = $rel->countDest($a, true, Ac_Model_Relation_Abstract::RESULT_PLAIN), 1);
        $this->assertEqual($d = $rel->countDest($b, true, Ac_Model_Relation_Abstract::RESULT_PLAIN), 1);
        
    }
    
    function testGetOrCountRecordKeys() {
        
        $am = Sample::getInstance()->getSamplePersonAlbumMapper();
        $a1 = $am->loadByAlbumId(1);
        $a2 = $am->loadByAlbumId(2);
        $rel = $am->getRelation('_personPhotos');
        
        $d = $rel->getDest(array($a1, $a2), Ac_Model_Relation_Abstract::RESULT_RECORD_KEYS);
        if (!$this->assertArraysMatch($a = array(
            $a1->personId => array(
                $a1->albumId => array(
                    array('__class' => 'Sample_Person_Photo', 'photoId' => 1, 'personId' => $a1->personId),
                    array('__class' => 'Sample_Person_Photo', 'photoId' => 2, 'personId' => $a1->personId),
                ),
                $a2->albumId => array(
                    array('__class' => 'Sample_Person_Photo', 'photoId' => 1, 'personId' => $a2->personId),
                )
            )
        ), $d)) 
            Ac_Debug::drr($d);
        
        $a1 = $am->loadByAlbumId(1);
        $a2 = $am->loadByAlbumId(2);
        $a1->setPersonPhotoIds(array(
            array('personId' => $a1->personId, 'photoId' => 1),
        ));
        $a2->setPersonPhotoIds(array(
            array('personId' => $a2->personId, 'photoId' => 1),
        ));
        $d = $rel->getDest(array($a1, $a2), Ac_Model_Relation_Abstract::RESULT_RECORD_KEYS);
        if (!$this->assertArraysMatch($a = array(
            $a1->personId => array(
                $a1->albumId => array(
                    array('__class' => 'Sample_Person_Photo', 'photoId' => 1, 'personId' => $a1->personId),
                ),
                $a2->albumId => array(
                    array('__class' => 'Sample_Person_Photo', 'photoId' => 1, 'personId' => $a2->personId),
                )
            )
        ), $d)) 
            Ac_Debug::drr($d);
        
        $a1 = $am->loadByAlbumId(1);
        $a2 = $am->loadByAlbumId(2);
        $a1->setPersonPhotoIds(array(
            array('personId' => $a1->personId, 'photoId' => 2),
        ));
        $d = $rel->getDest(array($a1, $a2), Ac_Model_Relation_Abstract::RESULT_RECORD_KEYS);
        if (!$this->assertArraysMatch($a = array(
            $a1->personId => array(
                $a1->albumId => array(
                    array('__class' => 'Sample_Person_Photo', 'photoId' => 2, 'personId' => $a1->personId),
                ),
                $a2->albumId => array(
                    array('__class' => 'Sample_Person_Photo', 'photoId' => 1, 'personId' => $a2->personId),
                )
            )
        ), $d)) 
            Ac_Debug::drr($d);
    }
    
    function testGetOrCountRecords() {
        $pm = Sample::getInstance()->getSamplePersonMapper();
        
        $a = $pm->loadByPersonId(3);
        $b = $pm->loadByPersonId(4);
        
        $rel = $pm->getRelation('_tags');
        //Ac_Debug::drr($rel->getDest(array($a, $b), Ac_Model_Relation_Abstract::RESULT_RECORD_KEYS));
        //Ac_Debug::drr($rel->getDest(array($a, $b), Ac_Model_Relation_Abstract::RESULT_PLAIN));
        $d = $rel->getDest(array($a, $b), Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS);
        $this->assertTrue(is_array($d[0]) && !count($d[0])); // first person does not have tags
        
        
        /*
        $am = Sample::getInstance()->getSamplePersonAlbumMapper();
        $a1 = $am->loadByAlbumId(1);
        $a2 = $am->loadByAlbumId(2);
        $rel = $am->getRelation('_personPhotos');
         */
        
        //Ac_Debug::drr($rel->getDest(array($a1, $a2), Ac_Model_Relation_Abstract::RESULT_RECORD_KEYS));
        //Ac_Debug::drr($rel->getDest(array($a1, $a2), Ac_Model_Relation_Abstract::RESULT_PLAIN));
        //Ac_Debug::drr($rel->getDest(array($a1, $a2), Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS));
        
    }
    
    function testCountNNRecords() {
        $pm = Sample::getInstance()->getSamplePersonMapper();
        
        $a = $pm->loadByPersonId(3);
        $b = $pm->loadByPersonId(4);
        
        $pm->loadAssocCountFor($a, '_tags');
        $pm->loadAssocCountFor($b, '_tags');
        if (!$this->assertEqual($a->_tagsCount, 0)) {
            var_dump($a->_tagsCount);
        }
        $this->assertEqual($b->_tagsCount, 2);
        
        $a = $pm->loadByPersonId(3);
        $b = $pm->loadByPersonId(4);
        $this->assertEqual($a->countTags(), 0);
        $this->assertEqual($b->countTags(), 2);
        
        $rel = $pm->getRelation('_tags');
        $a = $pm->loadByPersonId(3);
        $b = $pm->loadByPersonId(4);
        $rel->loadDestCount(array($a, $b));
        $this->assertEqual($a->_tagsCount, 0);
        $this->assertEqual($b->_tagsCount, 2);
        
        $a = $pm->loadByPersonId(3);
        $b = $pm->loadByPersonId(4);
        $this->assertEqual($rel->countDest($a), 0);
        
        if (!$this->assertEqual($res = $rel->countDest($b), 2)) {
            var_dump($res);
        }
        
        $a = $pm->loadByPersonId(3);
        $b = $pm->loadByPersonId(4);
        
        if (!$this->assertEqual($c = $rel->countDest(array($a, $b), true, Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS), array(0, 2))) {
            var_dump($c);
        }
        if (!$this->assertEqual($c = $rel->countDest(array($a, $b), false, Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS), 2)) {
            var_dump($c);
        }
        
        
        
        $a = $pm->loadByPersonId(3);
        $b = $pm->loadByPersonId(4);
        
        $a->setTagIds(array(3));

        if (!$this->assertEqual($c = $rel->countDest(array($a, $b), true, Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS), array(1, 2))) {
            var_dump($c);
        }
        if (!$this->assertEqual($c = $rel->countDest(array($a, $b), false, Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS), 3)) {
            var_dump($c);
        }
        
        $a = $pm->loadByPersonId(3);
        $b = $pm->loadByPersonId(4);
        
        $a->setTagIds(array(1));
        $b->setTagIds(array());

        if (!$this->assertEqual($c = $rel->countDest(array($a, $b), true, Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS), array(1, 0))) {
            var_dump($c);
        }
        if (!$this->assertEqual($c = $rel->countDest(array($a, $b), false, Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS), 1)) {
            var_dump($c);
        }
        
    }
    
    function testMemNNRecordsCK() {
        $am = Sample::getInstance()->getSamplePersonAlbumMapper();
        
        $a1 = $am->loadByAlbumId(1);
        $a2 = $am->loadByAlbumId(2);
        if ($this->assertEqual(count($a2->listPersonPhotos()), 1)) {
            $this->assertEqual($a2->getPersonPhoto(0)->photoId, 1);
        }
        
        $a1 = $am->loadByAlbumId(1);
        $a2 = $am->loadByAlbumId(2);
        $rel = $am->getRelation('_personPhotos');
        $rel->loadDestCount(array($a1, $a2));
        $this->assertEqual($a1->_personPhotosCount, 2);
        $this->assertEqual($a2->_personPhotosCount, 1);
        
        $a1 = $am->loadByAlbumId(1);
        $a2 = $am->loadByAlbumId(2);
        $a1->setPersonPhotoIds(array(array('personId' => $a1->personId, 'photoId' => 1)));
        $a2->setPersonPhotoIds(array());
        $rel = $am->getRelation('_personPhotos');
        $rel->loadDestCount(array($a1, $a2));
        $this->assertEqual($a1->_personPhotosCount, 1);
        $this->assertEqual($a2->_personPhotosCount, 0);
        
        $a1 = $am->loadByAlbumId(1);
        $a2 = $am->loadByAlbumId(2);
        
        $a1 = $am->loadByAlbumId(1);
        $a2 = $am->loadByAlbumId(2);
        $rel = clone $am->getRelation('_personPhotos');
        $a1->setPersonPhotoIds(array(array('personId' => $a1->personId, 'photoId' => 2)));
        
        $rel->destOrdering = 'photoId ASC';
        $d = $rel->getDest(array($a1, $a2));
        if (!$this->assertArraysMatch(array(
            array('__class' => 'Sample_Person_Photo', 'photoId' => 1),
            array('__class' => 'Sample_Person_Photo', 'photoId' => 2),
        ), $d))
            Ac_Debug::drr($d);
        
        $d = $rel->getDest($a1);
        if (!$this->assertArraysMatch(array(
            array('__class' => 'Sample_Person_Photo', 'photoId' => 2)
        ), $d))
            Ac_Debug::drr($d);
        
        $a1 = $am->loadByAlbumId(1);
        $a2 = $am->loadByAlbumId(2);
        $a1->setPersonPhotoIds(array(array('personId' => $a1->personId, 'photoId' => 1)));
        $rel = $am->getRelation('_personPhotos');
        $rel->loadDestCount(array($a1, $a2));
        $this->assertEqual($a1->_personPhotosCount, 1);
        $this->assertEqual($a2->_personPhotosCount, 1);
        
        $a2 = $am->loadByAlbumId(2);
        $am->loadPersonPhotosFor(array($a2));
        if ($this->assertEqual(count($a2->listPersonPhotos()), 1)) {
            $this->assertEqual($a2->getPersonPhoto(0)->photoId, 1);
        }
        
        $a2 = $am->loadByAlbumId(2);
        $a2->setPersonPhotoIds(array(array('personId' => $a2->personId, 'photoId' => 2)));
        $this->assertEqual($a2->getPersonPhoto(0)->photoId, 2);
        
        $a2 = $am->loadByAlbumId(2);
        $a2->setPersonPhotoIds(array());
        $this->assertEqual($a2->listPersonPhotos(), array());
        
        $a2 = $am->loadByAlbumId(2);
        $a2->setPersonPhotoIds(array());
        $this->assertEqual($a2->listPersonPhotos(), array());
        
        $a2 = $am->loadByAlbumId(2);
        $a2->setPersonPhotoIds(array(array('personId' => $a2->personId, 'photoId' => 2)));
        $am->loadPersonPhotosFor(array($a2));
        $this->assertEqual($a2->getPersonPhoto(0)->photoId, 2);
        
        $a2 = $am->loadByAlbumId(2);
        $a2->setPersonPhotoIds(array());
        $am->loadPersonPhotosFor(array($a2));
        $this->assertEqual($a2->listPersonPhotos(), array());
    }

    function testMemNNRecords() {
        $pm = Sample::getInstance()->getSamplePersonMapper();
        
        $pers = $pm->loadByPersonId(3);
        $pers->setTagIds(array(1));
        $pers2 = $pm->loadByPersonId(4);
        $this->assertEqual($pers->countTags(), 1);
        $this->assertEqual($pers2->countTags(), 2);
        
        // TODO: fix (aren't countSrc / countDest used ???)
        
        /*$rel = $pm->getRelation('_tags');
        $pers = $pm->loadByPersonId(3);
        $pers->setTagIds(array(1));
        $pers2 = $pm->loadByPersonId(4);
        $this->assertEqual($rel->countDest($pers), 1);
        $this->assertEqual($rel->countDest($pers2), 1);*/
        
        $pers = $pm->loadByPersonId(3);
        $pers2 = $pm->loadByPersonId(4);
        $pers->setTagIds(array(1, 2));
        
        $this->assertEqual($pers->countTags(), 2);
        $this->assertEqual($pers2->countTags(), 2);
        
        $pers = $pm->loadByPersonId(3);
        $pers2 = $pm->loadByPersonId(4);
        
        $pers->setTagIds(array(1, 2));
        $rel = $pm->getRelation('_tags');
        $rel->loadDestCount(array($pers, $pers2));
        $this->assertEqual($pers->_tagsCount, 2);
        $this->assertEqual($pers2->_tagsCount, 2);
        
        $this->assertEqual($pers->countTags(), 2);
        $this->assertEqual($pers2->countTags(), 2);
        
        $pers = $pm->loadByPersonId(3);
        $pers2 = $pm->loadByPersonId(4);
        $pers->setTagIds(array(3));
        $this->assertEqual(count($pers->listTags()), 1, 'objects are in list after NN ids are set');
        
        $pers = $pm->loadByPersonId(3);
        $pers2 = $pm->loadByPersonId(4);
        $pers->setTagIds(array(1, 2));
        $pm->loadTagsFor($aa = array($pers, $pers2));
        $this->assertEqual(count($pers->listTags()), 2, 'objects are in list after NN ids are set');
        $this->assertEqual(count($pers2->listTags()), 2, 'objects are in list after NN ids are set');
        $tagsByPersonsByIds = array();
        foreach ($aa as $p) {
            foreach ($p->listTags() as $ti) {
                $t = $p->getTag($ti);
                $tagsByPersonsByIds[$p->personId][$t->tagId] = $t;
            }
        }
        $this->assertSame($tagsByPersonsByIds[3][1], $tagsByPersonsByIds[4][1]);
        
        $pers = $pm->loadByPersonId(3);
        $pers2 = $pm->loadByPersonId(4);
        $pers->setTagIds(array(1));
        $pers2->setTagIds(array(2));
        $pm->loadTagsFor($pers);
        $this->assertTrue(count($pers->listTags()) == 1);
        $this->assertTrue(count($pers2->listTags()) == 1);

        $pers = $pm->loadByPersonId(3);
        $pers2 = $pm->loadByPersonId(4);
        $pers->setTagIds(array());
        $pers2->setTagIds(array(2));
        $pm->loadTagsFor(array($pers, $pers2));
        $this->assertTrue(count($pers->listTags()) == 0);
        $this->assertTrue(count($pers2->listTags()) == 1);

        $pers = $pm->loadByPersonId(3);
        $pers2 = $pm->loadByPersonId(4);
        $pers->setTagIds(array());
        $pers2->setTagIds(array());
        $pm->loadTagsFor(array($pers, $pers2));
        $this->assertTrue(count($pers->listTags()) == 0);
        $this->assertTrue(count($pers2->listTags()) == 0);
        
        $pers = $pm->loadByPersonId(3);
        $pers2 = $pm->loadByPersonId(4);
        $pers->setTagIds(array(1));
        $rel = clone $pm->getRelation('_tags');
        $rel->destOrdering = 'tagId';
        $pp = array('first' => $pers, 'second' => $pers2);
        $dd = $rel->getDest($pp, Ac_Model_Relation_Abstract::RESULT_PLAIN);
        ksort($dd);
        $this->assertEqual(array_keys($dd), array(0, 1));
        $this->assertEqual($dd[0]->tagId, 1);
        $this->assertEqual($dd[1]->tagId, 2);

        $dd = $rel->getDest($pp, Ac_Model_Relation_Abstract::RESULT_ORIGINAL_KEYS);
        $this->assertArraysMatch(array(
            'first' => array(
                0 => array('__class' => 'Sample_Tag', 'tagId' => 1)
            ),
            'second' => array(
                0 => array('__class' => 'Sample_Tag', 'tagId' => 1),
                1 => array('__class' => 'Sample_Tag', 'tagId' => 2)
            )
        ), $dd);
        
        /*$dd = $rel->getDest($pp, Ac_Model_Relation_Abstract::RESULT_RECORD_KEYS);
        Ac_Debug::drr($dd);
        $this->assertArraysMatch(array(
            $pers->personId => array(
                0 => array('__class' => 'Sample_Tag', 'tagId' => 1),
                1 => array('__class' => 'Sample_Tag', 'tagId' => 2)
            ),
        ), $dd);*/
        
        $pers = $pm->loadByPersonId(3);
        $pers2 = $pm->loadByPersonId(4);
        $pers->setTagIds(array());
        $rel = clone $pm->getRelation('_tags');
        $rel->destOrdering = 'tagId';
        $pp = array('first' => $pers, 'second' => $pers2);
        $dd = $rel->getDest($pp, Ac_Model_Relation_Abstract::RESULT_ORIGINAL_KEYS);
        $this->assertArraysMatch(array(
            'second' => array(
                0 => array('__class' => 'Sample_Tag', 'tagId' => 1),
                1 => array('__class' => 'Sample_Tag', 'tagId' => 2)
            )
        ), $dd);
        $dd = $rel->getDest($pp, Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS);
        $this->assertArraysMatch(array(
            'first' => array(),
            'second' => array(
                0 => array('__class' => 'Sample_Tag', 'tagId' => 1),
                1 => array('__class' => 'Sample_Tag', 'tagId' => 2)
            )
        ), $dd);
        
        $pers = $pm->loadByPersonId(3);
        $pers2 = $pm->loadByPersonId(4);
        $pers->setTagIds(array(1));
        $pers2->setTagIds(array(2));
        $rel = clone $pm->getRelation('_tags');
        $rel->destOrdering = 'tagId';
        $pp = array('first' => $pers, 'second' => $pers2);
        $dd = $rel->getDest($pp);
        $this->assertArraysMatch(array(
            0 => array('__class' => 'Sample_Tag', 'tagId' => 1),
            1 => array('__class' => 'Sample_Tag', 'tagId' => 2)
        ), $dd);
        
        $pers = $pm->loadByPersonId(3);
        $pers2 = $pm->loadByPersonId(4);
        $pers->setTagIds(array());
        $pers2->setTagIds(array(2));
        $rel = clone $pm->getRelation('_tags');
        $rel->destOrdering = 'tagId';
        $pp = array('first' => $pers, 'second' => $pers2);
        $dd = $rel->getDest($pp, Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS);
        $this->assertArraysMatch(array(
            'first' => array(),
            'second' => array(
                0 => array('__class' => 'Sample_Tag', 'tagId' => 2)
            )
        ), $dd);
        
        $pers = $pm->loadByPersonId(3);
        $pers2 = $pm->loadByPersonId(4);
        $pers->setTagIds(array(2));
        $pers2->setTagIds(array());
        $rel = clone $pm->getRelation('_tags');
        $rel->destOrdering = 'tagId';
        $pp = array('first' => $pers, 'second' => $pers2);
        $this->assertTrue(!count($rel->getDest($pers2)));
        $this->assertArraysMatch(array(
            0 => array('__class' => 'Sample_Tag', 'tagId' => 2)
        ), $dd = $rel->getDest($pers));
    }

    function testIgnoreLoaded() {
        $pm = Sample::getInstance()->getSamplePersonMapper();
        $pers = $pm->loadByPersonId(3);
        $pers2 = $pm->loadByPersonId(4);
        $this->assertNotEqual($oldTitle = $pers->getReligion()->title, 'foobar');
        $pers->getReligion()->title = 'foobar';
        $this->assertEqual($pers->getReligion()->title, 'foobar');
        $pm->loadReligionFor(array($pers, $pers2));
        $this->assertEqual($pers->getReligion()->title, 'foobar');
    }

    function testLoadNNRecords() {
        
        $pm = Sample::getInstance()->getSamplePersonMapper();
        
        $allTags = Sample::getInstance()->getDb()->fetchColumn('
            SELECT title FROM #__tags t 
            INNER JOIN #__people_tags pt ON t.tagId = pt.idOfTag
        ');
        sort($allTags);
        
        $allTagIds = Sample::getInstance()->getDb()->fetchColumn('
            SELECT DISTINCT idOfTag FROM #__people_tags pt
        ');
        sort($allTagIds);
        
        $recs = $pm->getAllRecords();
        $tags = Ac_Util::flattenArray($pm->loadTagsFor($recs));

        foreach ($tags as $tag) $allTags2[] = $tag->title;
        sort($allTags2);
        
        $ppl = $pm->loadRecordsArray($pm->listRecords());
        
        if (!$this->assertEqual($allTags, $allTags2)) {
            Ac_Debug::drr($allTags, $allTags2);
        }
        
        $tm = Sample::getInstance()->getSampleTagMapper();
        $recs2 = $pm->loadRecordsArray($pm->listRecords());
        $tags2 = Ac_Util::flattenArray($tm->loadForPeople($recs2));
        
        $allTags2 = array();
        foreach ($tags2 as $tag) $allTags2[] = $tag->title;
        sort($allTags2);
        $this->assertEqual($allTags, $allTags2);
        
        $pm->loadTagIdsFor($recs2);
        $a = array();
        foreach ($recs2 as $rec) {
            $a = array_merge($a, $rec->_tagIds);
        }
        $a = array_unique($a);
        sort($a);
        
        $this->assertEqual($a, $allTagIds);
    }
    
    function testRelArray() {
        $rel = new Ac_Model_Relation(array(
                'srcTableName' => false,
                'destTableName' => '#__people_tags',
                'fieldLinks' => array(
                    'personId' => 'idOfPerson',
                ),
                'srcVarName' => 'tagIds',
                'srcIsUnique' => true,
                'destIsUnique' => false,
                'database' => $this->getAeDb()
        ));
        $origSrc = $src = array(
            'a' => array('personId' => 4),
            'b' => array('personId' => 3, 'tagIds' => false),
            'c' => array('personId' => -2, 'tagIds' => null),
            'd' => array('personId' => -1, 'tagIds' => array('foo')),
        );
        $rel->loadDest($src);
        
        if (!$this->assertTrue(isset($src['a']['tagIds']) && is_array($src['a']['tagIds']) 
            && count($src['a']['tagIds']))) {
            var_dump($src['a']);
        }
        
        $this->assertTrue(isset($src['b']['tagIds']) && is_array($src['b']['tagIds']));
        $this->assertTrue(array_key_exists('tagIds', $src['c']) && $src['c']['tagIds'] === null);
        $this->assertTrue(isset($src['d']['tagIds']) && is_array($src['d']['tagIds']) 
            && implode('/', $src['d']['tagIds']) == 'foo');
        
        // Now check $dontOverwriteLoaded := false (overwriteLoaded)
        $src = $origSrc;
        $rel->loadDest($src, false);
        
        $this->assertTrue(isset($src['a']['tagIds']) && is_array($src['a']['tagIds']));
        $this->assertTrue(isset($src['b']['tagIds']) && is_array($src['b']['tagIds']));
        $this->assertTrue(isset($src['c']['tagIds']) && is_array($src['c']['tagIds']));
        $this->assertTrue(isset($src['d']['tagIds']) && is_array($src['d']['tagIds']) 
            && !in_array('foo', $src['d']['tagIds']));
    }
    
        
    protected function _getRefIds(Sample_Shop_Product $prod) {
        $res = array();
        foreach ($prod->listReferencedShopProducts() as $i) $res[] = $prod->getReferencedShopProduct($i)->id;
        return $res;
    }
    
    function testMidWhere() {
        $pm = $this->getSampleApp()->getSampleShopProductMapper();
        // assumes following relation structure:
        //"productId","relatedProductId","ignore"
        //"1","2","0"
        //"1","3","0"
        //"1","4","1"
        //"2","1","1"
        //"2","3","1"
        //"2","4","0"
        //"4","1","1"
        //"4","2","1"
        //"4","3","1"
        $prods = $pm->loadRecordsArray(array(1, 2, 3, 4), true);
        $pm->loadReferencedShopProductIdsFor($prods);
        $this->assertArraysMatch($prods[1]->getReferencedShopProductIds(), array(2, 3), 'Load IDs with midWere applied', 'sort');
        $this->assertArraysMatch($prods[2]->getReferencedShopProductIds(), array(4), 'Load IDs with midWere applied', 'sort');
        $this->assertArraysMatch($prods[3]->getReferencedShopProductIds(), array(), 'Load IDs with midWere applied', 'sort');
        $this->assertArraysMatch($prods[4]->getReferencedShopProductIds(), array(), 'Load IDs with midWere applied', 'sort');
        
        foreach ($prods as $prod) $prod->cleanupMembers ();
        $rel = clone $pm->getRelation('_referencedShopProducts');
        $rel->setMidWhere(false);
        $prods = $pm->loadRecordsArray(array(1, 2, 3, 4), true);
        $rel->loadDestNNIds($prods);
        $this->assertArraysMatch($prods[1]->getReferencedShopProductIds(), array(2, 3, 4), 'Load IDs without midWere applied', 'sort');
        $this->assertArraysMatch($prods[2]->getReferencedShopProductIds(), array(1, 3, 4), 'Load IDs without midWere applied', 'sort');
        $this->assertArraysMatch($prods[3]->getReferencedShopProductIds(), array(), 'Load IDs with midWere applied', 'sort');
        $this->assertArraysMatch($prods[4]->getReferencedShopProductIds(), array(1, 2, 3), 'Load IDs without midWere applied', 'sort');

        foreach ($prods as $prod) $prod->cleanupMembers ();
        $rel = clone $pm->getRelation('_referencedShopProducts');
        $prods = $pm->loadRecordsArray(array(1, 2, 3, 4), true);
        $rel->loadDest($prods);
        $this->assertArraysMatch($a = $this->_getRefIds($prods[1]), array(2, 3), $s = 'Load items with midWere applied', 'sort');
        $this->assertArraysMatch($this->_getRefIds($prods[2]), array(4), $s, 'sort');
        $this->assertArraysMatch($this->_getRefIds($prods[3]), array(), $s, 'sort');
        $this->assertArraysMatch($this->_getRefIds($prods[4]), array(), $s, 'sort');
 
        foreach ($prods as $prod) $prod->cleanupMembers ();
        $rel = clone $pm->getRelation('_referencedShopProducts');
        $prods = $pm->loadRecordsArray(array(1, 2, 3, 4), true);
        $prods[1]->setReferencedShopProductIds(array(3, 4));
        $rel->loadDest($prods);
        $this->assertArraysMatch($a = $this->_getRefIds($prods[1]), array(3, 4), $s = 'Load items with midWere applied, IDs loaded', 'sort');
 
        foreach ($prods as $prod) $prod->cleanupMembers ();
        $prods = $pm->loadRecordsArray(array(1, 2, 3, 4), true);
        $rel = clone $pm->getRelation('_referencedShopProducts');
        $rel->loadDestNNIds($prods[1]);
        $rel->loadDest($prods);
        foreach ($prods as $prod) $prod->cleanupMembers ();
        $rel = clone $pm->getRelation('_referencedShopProducts');
        $prods = $pm->loadRecordsArray(array(1, 2, 3, 4), true);
        $rel->loadDest($prods);
        $this->assertArraysMatch($a = $this->_getRefIds($prods[1]), array(2, 3), $s = 'Load items with midWere applied, IDs loaded for sone instances', 'sort');
        $this->assertArraysMatch($this->_getRefIds($prods[2]), array(4), $s, 'sort');
        $this->assertArraysMatch($this->_getRefIds($prods[3]), array(), $s, 'sort');
        $this->assertArraysMatch($this->_getRefIds($prods[4]), array(), $s, 'sort');
 
        foreach ($prods as $prod) $prod->cleanupMembers ();
        $rel = clone $pm->getRelation('_referencedShopProducts');
        $prods = $pm->loadRecordsArray(array(1, 2, 3, 4), true);
        $rel->loadDestCount($prods);
        $this->assertEqual($a = $prods[1]->countReferencedShopProducts(), count(array(2, 3)), $s = 'Load items with midWere applied, IDs loaded for sone instances', 'sort');
        $this->assertEqual($a = $prods[2]->countReferencedShopProducts(), count(array(4)), $s);
        $this->assertEqual($a = $prods[3]->countReferencedShopProducts(), count(array()), $s);
        $this->assertEqual($a = $prods[4]->countReferencedShopProducts(), count(array()), $s);
 
        foreach ($prods as $prod) $prod->cleanupMembers ();
        $rel = clone $pm->getRelation('_referencedShopProducts');
        $prods = $pm->loadRecordsArray(array(1, 2, 3, 4), true);
        $rel->loadDestNNIds($prods);
        $this->assertEqual($a = $prods[1]->countReferencedShopProducts(), count(array(2, 3)), $s = 'Count items with midWere applied');
        $this->assertEqual($a = $prods[2]->countReferencedShopProducts(), count(array(4)), $s);
        $this->assertEqual($a = $prods[3]->countReferencedShopProducts(), count(array()), $s);
        $this->assertEqual($a = $prods[4]->countReferencedShopProducts(), count(array()), $s);
 
        foreach ($prods as $prod) $prod->cleanupMembers ();
        $rel = clone $pm->getRelation('_referencedShopProducts');
        $prods = $pm->loadRecordsArray(array(1, 2, 3, 4), true);
        $rel->loadDestNNIds($prods[1]);
        $this->assertEqual($a = $prods[1]->countReferencedShopProducts(), count(array(2, 3)), $s = 'Count items with midWere applied, IDs loaded for sone instances');
        $this->assertEqual($a = $prods[2]->countReferencedShopProducts(), count(array(4)), $s);
        $this->assertEqual($a = $prods[3]->countReferencedShopProducts(), count(array()), $s);
        $this->assertEqual($a = $prods[4]->countReferencedShopProducts(), count(array()), $s);
        
    }
   
}
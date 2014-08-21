<?php

class Ac_Test_Relation extends Ac_Test_Base {
    
    protected $bootSampleApp = true;
    
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
        $rel = $am->getRelation('_personPhotos');
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
        $pers->setTagIds(array(1, 2));
        $this->assertEqual(count($pers->listTags()), 2, 'objects are in list after NN ids are set');
        $pm->loadTagsFor(array($pers, $pers2));
        
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
        $rel = $pm->getRelation('_tags');
        $rel->destOrdering = 'tagId';
        $pp = array('first' => $pers, 'second' => $pers2);
        $dd = $rel->getDest($pp, AMR_PLAIN_RESULT);
        ksort($dd);
        $this->assertEqual(array_keys($dd), array(0, 1));
        $this->assertEqual($dd[0]->tagId, 1);
        $this->assertEqual($dd[1]->tagId, 2);

        $dd = $rel->getDest($pp, AMR_ORIGINAL_KEYS);
        $this->assertArraysMatch(array(
            'first' => array(
                0 => array('__class' => 'Sample_Tag', 'tagId' => 1)
            ),
            'second' => array(
                0 => array('__class' => 'Sample_Tag', 'tagId' => 1),
                1 => array('__class' => 'Sample_Tag', 'tagId' => 2)
            )
        ), $dd);
        
        /*$dd = $rel->getDest($pp, AMR_RECORD_KEYS);
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
        $rel = $pm->getRelation('_tags');
        $rel->destOrdering = 'tagId';
        $pp = array('first' => $pers, 'second' => $pers2);
        $dd = $rel->getDest($pp, AMR_ORIGINAL_KEYS);
        $this->assertArraysMatch(array(
            'second' => array(
                0 => array('__class' => 'Sample_Tag', 'tagId' => 1),
                1 => array('__class' => 'Sample_Tag', 'tagId' => 2)
            )
        ), $dd);
        $dd = $rel->getDest($pp, AMR_ALL_ORIGINAL_KEYS);
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
        $rel = $pm->getRelation('_tags');
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
        $rel = $pm->getRelation('_tags');
        $rel->destOrdering = 'tagId';
        $pp = array('first' => $pers, 'second' => $pers2);
        $dd = $rel->getDest($pp, AMR_ALL_ORIGINAL_KEYS);
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
        $rel = $pm->getRelation('_tags');
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
        
        //restore_exception_handler();
        //restore_error_handler();
        
        $recs = $pm->getAllRecords();
        $tags = Ac_Util::flattenArray($pm->loadTagsFor($recs));

        foreach ($tags as $tag) $allTags2[] = $tag->title;
        sort($allTags2);
        
        $ppl = $pm->loadRecordsArray($pm->listRecords());
        
        $this->assertEqual($allTags, $allTags2);

        
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
        $loaded = array();
        
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
    
        
    function testRelArrayQualifiers() {
        $rel = new Ac_Model_Relation(array(
                'srcTableName' => false,
                'destTableName' => '#__people_tags',
                'fieldLinks' => array(
                    'personId' => 'idOfPerson',
                ),
                'srcVarName' => 'tagIds',
                'destQualifier' => 'idOfTag',
                'srcIsUnique' => true,
                'destIsUnique' => false,
                'database' => $this->getAeDb()
        ));
        $src = array(
            'a' => array('personId' => 4),
            'b' => array('personId' => 3, 'tagIds' => false),
            'c' => array('personId' => -2, 'tagIds' => null),
        );
        $rel->loadDest($src);

        foreach ($src as $foo) {
            if (isset($foo['tagIds']) && is_array($foo['tagIds'])) {
                foreach ($foo['tagIds'] as $key => $id) {
                    $this->assertEqual($key, $id['idOfTag']);
                }
            }
        }
    }
    
}
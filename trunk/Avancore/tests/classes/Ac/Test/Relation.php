<?php

class Ac_Test_Relation extends Ac_Test_Base {
    
    protected $bootSampleApp = true;

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
        $src = array(
            'a' => array('personId' => 4),
            'b' => array('personId' => 3, 'tagIds' => false),
            'c' => array('personId' => -2, 'tagIds' => null),
            'd' => array('personId' => -1, 'tagIds' => array('foo')),
        );
        $rel->loadDest($src);
        $loaded = array();
        
        // TODO: think out how $ignoreLoaded property should behave
        $this->assertTrue(isset($src['a']['tagIds']) && is_array($src['a']['tagIds']) 
            && count($src['a']['tagIds']));
        
        $this->assertTrue(isset($src['b']['tagIds']) && is_array($src['b']['tagIds']));
        $this->assertTrue(isset($src['c']['tagIds']) && is_array($src['c']['tagIds']));
        $this->assertTrue(isset($src['d']['tagIds']) && is_array($src['d']['tagIds']));
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
            'd' => array('personId' => -1, 'tagIds' => array('foo')),
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
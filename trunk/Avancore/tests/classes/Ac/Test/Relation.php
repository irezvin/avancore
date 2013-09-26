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
        foreach ($recs2 as $rec) $a = array_merge($a, $rec->_tagIds);
        $a = array_unique($a);
        sort($a);
        
        $this->assertEqual($a, $allTagIds);
        
    }
    
}
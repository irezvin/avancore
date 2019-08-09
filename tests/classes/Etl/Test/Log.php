<?php

class Etl_Test_Log extends Etl_Test_Class_Abstract {
        
    function testLog() {
    
        $logger = new Ac_Etl_Logger_Collector;
        
        $item1 = new Ac_Etl_Log_Item('item1', 'profile', array(
            'all',
            'tag1/tag1.1',
            'tag2/aaa',
        ), array('spentTime' => 10));
        
        $item2 = new Ac_Etl_Log_Item('item2', 'profile', array(
            'all',
            'tag1/tag1.2',
            'tag2/aaa',
        ), array('spentTime' => 20));
        
        $item3 = new Ac_Etl_Log_Item('item3', 'profile', array(
            'all',
            'tag1',
            'tag2/bbb',
        ), array('spentTime' => 5));
        
        $item4 = new Ac_Etl_Log_Item('item4', 'profile', array(
            'all',
            'tag1/tag1.1',
            'tag2/ccc',
        ), array('spentTime' => 15));
        
        
        $logger->acceptItem($item1);
        $logger->acceptItem($item2);
        $logger->acceptItem($item3);
        $logger->acceptItem($item4);
        
        $stats = new Ac_Etl_Log_Stats;
        $stats->setItems($logger->items);
        $a1 = array_keys($stats->getTags());
        $a2 = array('all', 'tag1', 'tag1/tag1.1', 'tag1/tag1.2', 'tag2', 'tag2/aaa', 'tag2/bbb', 'tag2/ccc');
        sort($a1);
        sort($a2);
        if (!$this->assertEqual(
            $a1, $a2
        )) var_dump($a1);
        
        
        /*Ac_Util::showCoolTable(
            $ss = $stats->getExtendedStats(),
            array_combine(array_keys($ss['all']), array_keys($ss['all'])),
            array_combine(array_keys($ss), array_keys($ss))
        );*/
        
        $ws = new Ac_Etl_Log_Stats_Html_Stats($stats);
        $wr = new Ac_Etl_Log_Stats_Html_Writer;
        $wr->assetPlaceholders = Ac_Avancore::getInstance()->getAssetPlaceholders(true);
        $wr->writeWidget($ws);
        
    }
    
    function testTools() {
        //$tools = new Ac_Etl_Tools();
        //$copyMap = Im
    }
    
}

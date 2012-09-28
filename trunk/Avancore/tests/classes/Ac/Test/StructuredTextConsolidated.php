<?php

class Ac_Test_StructuredTextConsolidated extends Ac_Test_Base {
   
    function testStructuredTextConsolidated() {
        $st = new Ac_Content_StructuredText;
        
        $st->setRegistry(array(
            '<div class="main">', 
            'body' => array(), 
            '</div>',
        ));
        
        $widget1 = new Ac_Response_Html();
        $widget1->setAssetLibs(array('{FOO}/test.js'));
        $widget1->setContent(array('<div class="widget">Widget 1</div>'));
        
        $st->addRegistry($widget1, 'body');
        
        $st->addRegistry('<hr />', 'body');
        
        $widget2 = new Ac_Response_Html();
        $widget2->setAssetLibs(array('{FOO}/test.js', '{FOO}/test2.js', '{FOO}/test.css'));
        $widget2->setContent(array('<div class="widget">Widget 2</div>'));
        
        $widget2->setCacheConsolidated(false);
        
        $st->addRegistry($widget2, 'body');
        
        $cons = $st->getConsolidated(array('content'));

        if (!$this->assertIdentical(implode('', $cons['content']), 
            $sample = 
            '<div class="main">'.
            '<div class="widget">Widget 1</div>'.
            '<hr />'.
            '<div class="widget">Widget 2</div>'.
            '</div>'
        )) var_dump($cons['content']);
        
        
        
        
        $hp = new Ac_Response_Writer_HtmlPage;
        $hp->setEnvironment($e = new Ac_Response_Environment_Dummy);
        
        $resp = new Ac_Response();
        
        
        // This is a BAAAD (imperative) way that should be done AUTOMATICALLY
        $resp->mergeRegistry($stc = $st->getConsolidated(array('content')));
        
        $cons = new Ac_Response_Consolidated();
        $cons->mergeRegistry($resp->getConsolidated());

        $hp->writeResponse($resp);
        
/**
 * Two problems here
 * 
 * 1. We can forget that setContent should NOT be used with string parameter
 * in most cases, it shoud have string parameter (or addContent should be
 * preferred instead)
 * 
 * 2. Ac_Registry_Consolidated does not unroll consolidated sub-responses 
 * such as one that Ac_Content_StructuredText is
 *  
 */        
        
        if (!$this->assertEqual(str_replace("\n", "", $e->responseText), 
            $fullSample = str_replace("\n", "", '<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="{FOO}/test.css" />
        <script type="text/javascript" src="{FOO}/test.js"> </script>
        <script type="text/javascript" src="{FOO}/test2.js"> </script> 
    </head>
    <body>'.$sample.'</body>
</html>')
        )) var_dump($e->responseText);
        

        $resp = new Ac_Response();
        
        //$resp->mergeRegistry($stc = $st->getConsolidated(array('content')));
        
        // TODO: The line below should give the same effect as commented code above... but it DOES NOT        
        $resp->setRegistry($st, 'content');
        
        $cons = new Ac_Response_Consolidated();
        $cons->mergeRegistry($resp->getConsolidated());

        $hp->writeResponse($resp);
        
        if (!$this->assertEqual(str_replace("\n", "", $e->responseText), $fullSample)) var_dump($e->responseText);        
        
        
    }
    
    
}
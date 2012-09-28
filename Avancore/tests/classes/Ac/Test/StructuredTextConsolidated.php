<?php

class Ac_Test_StructuredTextConsolidated extends Ac_Test_Base {


    function testStructuredText() { 
        $resp = new Ac_Response();
        
        $resp->setRegistry($st = new Ac_Content_StructuredText, 'content');
        $resp->addRegistry('<p>Header</p>', 'content');
        $resp->addRegistry('', 'content', 'widgetContent');
        $resp->addRegistry('<p>Footer</p>', 'content');
        
        $widgetResponse = new Ac_Response();
        $widgetResponse->setRegistry(array(
            'content' => array('widgetContent' => array('<div class="widget">A widget</div>')),
        ));
        
        $resp->mergeRegistry($widgetResponse);
        
        $x = $resp->exportRegistry();
        
        $w = new Ac_Response_Writer_HtmlPage();
        $e = new Ac_Response_Environment_Dummy;
        $w->setEnvironment($e);

        $w->writeResponse($resp);
        
        $text = $e->responseText;
            
        if (!$this->assertEqual(trim($text), trim('
<!DOCTYPE html>
<html>
    <head>
    </head>
    <body><p>Header</p><div class="widget">A widget</div><p>Footer</p></body>
</html>
')))  echo '<pre>'.htmlspecialchars($text).'</pre>';
        
    }  
    
    
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

        $sample = 
            '<div class="main">'.
            '<div class="widget">Widget 1</div>'.
            '<hr />'.
            '<div class="widget">Widget 2</div>'.
            '</div>';
        
        $fullSample = '<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="{FOO}/test.css" />
        <script type="text/javascript" src="{FOO}/test.js"> </script>
        <script type="text/javascript" src="{FOO}/test2.js"> </script> 
    </head>
    <body>'.$sample.'</body>
</html>';
        
        $hp = new Ac_Response_Writer_HtmlPage;
        $hp->setEnvironment($e = new Ac_Response_Environment_Dummy);
        
        
        $contentCons = $st->getConsolidated(array('content'));
        
        if (!$this->assertIdentical(implode('', $contentCons['content']), 
            $sample
        )) var_dump($contentCons['content']);
        
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
            str_replace("\n", "", $fullSample)
        )) var_dump($e->responseText);
        

        $resp2 = new Ac_Response();
        
        //$resp->mergeRegistry($stc = $st->getConsolidated(array('content')));
        
        $resp2->setRegistry($st, 'content');
        
        $cons2 = new Ac_Response_Consolidated();
        $cons2->mergeRegistry($resp2);
        
        $before = $cons2->exportRegistry(true);
        
        //$cons->deb = 1;
        
        $cc = $cons2->getConsolidated();
        
        $after = $cons2->exportRegistry(true);
        
        if (!$this->assertEqual(count($cc['assetLibs']), 3)) var_dump($cc['assetLibs']);

        $hp->writeResponse($resp);
        
        if (!$this->assertEqual(str_replace("\n", "", $e->responseText), str_replace("\n", "", $fullSample))) var_dump($e->responseText, $fullSample);        
        
        if (!$this->assertIdentical($before, $after, "getConsolidated() MUST NOT have side effects")) {
            echo "<table><tr><td valign='top'>";
            var_dump($before);
            echo "</td><td valign='top'>";
            var_dump($after);
            echo "</td></tr></table>";
        }
        
    }
    
    
}
<?php

class Ac_Test_Response extends Ac_Test_Base {

    function testSliceAndConsolidation() {
        
        $resp1 = new Ac_Response();
        $resp1->mergeRegistry(array('foo' => array('resp1.foo'), 'content' => array('resp1.content'), 'bar' => array('resp1.bar')));
        
        $resp2 = new Ac_Response();
        $resp2->mergeRegistry(array('foo' => array('resp2.foo'), 'content' => array('resp2.content'), 'bar' => array('resp2.bar')));
        
        $resp3 = new Ac_Response();
        $resp3->mergeRegistry(array('foo' => array('resp3.foo'), 'content' => array('resp3.content'), 'bar' => array('resp3.bar', 'main.bar.1' => array('resp3.bar.1.1'))));
        
        $main = new Ac_Response();
        
        $main->mergeRegistry(array(
            'foo' => array('main.foo.0', 'main.foo.1'),
            'content' => array(
                'main.content.first', $resp1, 'main.content.middle', $resp2, 'main.content.another' => array('xxx', $resp3, 'zzz'), 'main.content.last'
            ),
            'bar' => array(
                'main.bar.0', 'main.bar.1' => array('main.bar.1.1')
            )
        ));
        
        $r = $main->exportRegistry(false);
        
        $slices = Ac_Response::sliceWithConsolidatedObjects($r);
        
        $this->assertIdentical($slices, array(
            array(
                'foo' => array(
                    'main.foo.0',
                    'main.foo.1',
                ),
                'content' => array(
                    'main.content.first',
                ),
                'bar' => array(
                    'main.bar.0',
                    'main.bar.1' => array('main.bar.1.1',),
                ),
            ),
            array(
                'foo' => array('resp1.foo'),
                'content' => array('resp1.content'),
                'bar' => array('resp1.bar',),
            ),
            array('content' => array(2 => 'main.content.middle',),),
            array(
                'foo' => array('resp2.foo'),
                'content' => array('resp2.content'),
                'bar' => array('resp2.bar'),
            ),
            array(
                'content' => array(
                    'main.content.another' => array('xxx'),
                    4 => 'main.content.last',
                ),
            ),
            array(
                'foo' => array('resp3.foo'),
                'content' => array('resp3.content'),
                'bar' => array(
                    'resp3.bar',
                    'main.bar.1' => array('resp3.bar.1.1'),
                ),
            ),
            array('content' => array('main.content.another' => array(2 => 'zzz'))),
        ));
        
        $this->assertIdentical($main->getConsolidated(), array(
            'foo' => array (
                'main.foo.0',
                'main.foo.1',
                'resp1.foo',
                'resp2.foo',
                'resp3.foo',
            ),
            'content' => array (
                'main.content.first',
                'resp1.content',
                'main.content.middle',
                'resp2.content',
                'main.content.another' => array ('xxx', 'zzz'),
                'main.content.last',
                'resp3.content',
            ),
            'bar' => array (
                'main.bar.0',
                'main.bar.1' => array ('main.bar.1.1', 'resp3.bar.1.1'),
                'resp1.bar',
                'resp2.bar',
                'resp3.bar',
            ),
        ));
        
    }
    
    function testSimpleConsolidation() {

        $widget = new Ac_Response();
        
        $widget->setCacheConsolidated(false);
        
        $widget->mergeRegistry(array(
            'assetLibs' => array(
                'widget.js',
            ),
            'content' => array(
                '<div id="widget">A widget</div>'
            ),
            'debug' => array(
                '-- widget debug data --'
            ),
        ));
        
        $controller = new Ac_Response();
        
        $controller->mergeRegistry(array(
            'assetLibs' => array(
                'controller.css',
            ),
            'headTags' => array(
                '<meta name="robors" content="index,follow" />',
                '<title>The Page</title>'
            ),
            'content' => array(
                '<div class="controller"><h2>Page title</h2>',
                $widget,
                '</div>',
            ),
            'debug' => array('- controller debug data -'),
        ));
        
        $resp = new Ac_Response();
        
        $resp->mergeRegistry(array(
            'headers' => array('Content-Type' => 'text/html; charset=utf-8'),
            'headTags' => array(
                '<!-- Powered by Avancore 0.4 -->',
            ),
            'assetLibs' => array(
                'style.css',
            ),
            'content' => array(
                '<h1>hello, world!</h1>',
                $controller, 
                '<div class="footer">A footer here</div>'
            ),
            
            'debug' => array(
                'A debug message here',
            ),
        ));
        
        if (!$this->assertIdentical($con = $resp->getConsolidated(), array(
            'headers' => array('Content-Type' => 'text/html; charset=utf-8'),
            'headTags' => array(
                '<!-- Powered by Avancore 0.4 -->',
                '<meta name="robors" content="index,follow" />',
                '<title>The Page</title>'
            ),
            'assetLibs' => array(
                'style.css',
                'controller.css',
                'widget.js',
            ),
            'content' => array(
                '<h1>hello, world!</h1>',
                '<div class="controller"><h2>Page title</h2>',
                '<div id="widget">A widget</div>',
                '</div>',
                '<div class="footer">A footer here</div>'
            ),
            
            'debug' => array(
                'A debug message here',
                '- controller debug data -',
                '-- widget debug data --',
            ),
        ))) var_dump($con);
        
    }
    
    function testConsolidatedRegistries() {
        
        $response = new Ac_Response();
        $response->setRegistry(
            new Ac_Registry_Consolidated(array('singleValue' => 'svFirst')),
            'pageTitle'
        );
        $response->setRegistry(
            new Ac_Registry_Consolidated(array('unique' => true)),
            'assetLibs'
        );
        $response->setRegistry(
            new Ac_Registry_Consolidated(array('implode' => "\n")),
            'content'
        );
        $response->mergeRegistry(array(
            'pageTitle' => array('First title'),
            'assetLibs' => array('first.css', 'first.js'),
            'content' => array('text 1'),
        ));
        $response->mergeRegistry(array(
            'pageTitle' => array('Second title'),
            'assetLibs' => array('first.css', 'second.js'),
            'content' => array('text 2'),
        ));
        if (!$this->assertIdentical($con = $response->getConsolidated(), array(
            'pageTitle' => array('First title'),
            'assetLibs' => array(0 => 'first.css', 1=> 'first.js', 3 => 'second.js'),
            'content' => array('text 1'."\n".'text 2'),
        ))) var_dump($con);
    }
    
    function getMyPerfectResponse() {
        return array(
            'headers' => array(
                'X-Powered-By' => 'X-Powered-By: Avancore 0.3',
                'Content-Type' => 'Content-Type: text/html; charset=utf-8',
            ),
            'headContent' => array(
                '<!-- loudly and proudly powered by Avancore 0.3 -->',
            ),
            'assetLibs' => array(
                '{AE}/foo.css',
                '{AE}/bar.js',
            ),
            'title' => 'My perfect HTML document',
            'metaKeywords' => array(
                'foo bar', 'baz', array('quux'),
            ),
            'metaDescription' => array(
                'some interesting text',
                array('subKey' => 'about something good'),
            ),
            'content' => array(
                '<h1>Foo</h2>',
                '<p>Some text here</p>',
            ),
            'debug' => array(
                'Lets dump some debug data here',
            ),
        );  
    }
    
    function testConsolidatedResponse() {
        $resp = new Ac_Response_Consolidated();
        $resp->mergeRegistry($this->getMyPerfectResponse());
        if (!$this->assertEqual($con = $resp->getConsolidated(), array(
            'headers' => array(
                'X-Powered-By: Avancore 0.3',
                'Content-Type: text/html; charset=utf-8',
            ),
            'metaKeywords' => 'foo bar, baz, quux',
            'metaDescription' => 'some interesting text; about something good',
            'title' => 'My perfect HTML document',
            'docType' => 'html',
            'rootTagAttribs' => array(),
            'bodyTagAttribs' => array(),
            'headContent' => array(
                '<!-- loudly and proudly powered by Avancore 0.3 -->',
            ),
            'assetLibs' => array(
                '{AE}/foo.css',
                '{AE}/bar.js',
            ),
            'headScripts' => array(
            ),
            'initScripts' => array(
                
            ),
            'debug' => array(
                'Lets dump some debug data here',
            ),
            'content' => array(
                '<h1>Foo</h2>',
                '<p>Some text here</p>',
            ),

        ))) var_dump($con);
    }
    
    function testResponseWriter() {
        $w = new Ac_Response_Writer_HtmlPage();
        $e = new Ac_Response_Environment_Dummy;
        $w->setEnvironment($e);
        $w->setShowDebugInfo(true);
        $resp = new Ac_Response_Consolidated();
        $resp->mergeRegistry($this->getMyPerfectResponse());
        $w->setAssetPlaceholders(array(
           '{AE}' => 'http://cdn.avancore.org',
        ));
        $w->writeResponse($resp);
        
        if (!$this->assertEqual(
            $e->responseText, 
            '<!DOCTYPE html>
<html>
    <head>
        <title>My perfect HTML document</title>
        <meta name="keywords" content="foo bar, baz, quux" /> 
        <meta name="description" content="some interesting text; about something good" /> 
        <link rel="stylesheet" type="text/css" href="http://cdn.avancore.org/foo.css" />
        <script type="text/javascript" src="http://cdn.avancore.org/bar.js"> </script> 
<!-- loudly and proudly powered by Avancore 0.3 -->
    </head>
    <body><h1>Foo</h2>
<p>Some text here</p></body>
</html>
<!-- Debug:
                                                                               
Lets dump some debug data here 
                                                                               
-->'
        )) echo '<pre>'.htmlspecialchars($e->responseText).'</pre>';
    
        // Empty response should create no errors
        
        $resp = new Ac_Response;
        $w->writeResponse($resp);
        
        // Unfold asset string wthout assets proived
        
        $w2 = new Ac_Response_Writer_HtmlPage();
        $resp = new Ac_Response;
        $resp->addRegistry('test.css', 'assetLibs');
        $w2->writeResponse($resp);
        
    }
    
    function testStructuredText() {
        $resp = new Ac_Response();
        
        $resp->setRegistry(new Ac_Content_StructuredText, 'content');
        $resp->addRegistry('<p>Header</p>', 'content');
        
        $w = new Ac_Response_Writer_HtmlPage();
        $e = new Ac_Response_Environment_Dummy;
        $w->setEnvironment($e);
        $w->writeResponse($resp);
        echo '<pre>'.htmlspecialchars($e->responseText).'</pre>';
    }  
    
    
}
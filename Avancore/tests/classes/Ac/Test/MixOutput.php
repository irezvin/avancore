<?php

/**
 * Tests compatibility between Ac_Legacy_Output and Ac_Result* classes
 */
class Ac_Test_MixOutput extends Ac_Test_Base { 
    
    protected $bootSampleApp = true;
    
    /**
     * @return Ac_Result_Html
     */
    function createHtmlResult() {
        $outer = new Ac_Result_Html();
        $outer->getPlaceholder('comments')->addItems(array('line1', 'line2', 'line3'));
        $widget1 = new Ac_Result_Html(array(
            'content' =>  $w1content = '<div><p>Widget 1</p><div>Widget 1 body</div></div>',
            'title' => 'Sub',
            'assets' => array(
                '{FOO}/first.js',
                '{FOO}/first.css',
            )
        ));
        $widget2 = new Ac_Result_Html(array(
            'content' =>  $w2content = '<div><p>Widget 2</p><div>Widget 2 body</div></div>',
            'assets' => array(
                '{FOO}/first.js',
                '{FOO}/first.css',
                '{FOO}/second.js',
            )
        ));
        $widget3 = new Ac_Result_Html(array(
            'content' =>  $w3content = '<div><p>Widget 3</p><div>Widget 3 body</div></div>',
            'assets' => array(
                '{FOO}/second.js',
                '{FOO}/second.css',
            )
        ));
        $widget23 = new Ac_Result_Html(array(
            'content' =>  "<div class='group'>{$widget2} {$widget3}</div>",
        ));
            
        $outer->put("{$widget1} {$widget23}");
        $outer->getPlaceholder('title')->addItems(array('Parent'));
        
        $outer->getPlaceholder('doctype')->addItems(array(Ac_Result_Html::DOCTYPE_STRICT));
        $outer->getPlaceholder('doctype')->addItems(array(Ac_Result_Html::DOCTYPE_HTML5));
        $outer->initScripts[] = "console.log('foo');";
        $outer->initScripts[] = new Ac_Js_Script("console.log('bar');");
        return $outer;
    }
    
    function testResultToResponse() {
        
        $r = $this->createHtmlResult();
        $r->meta['description'] = 'Some descr';
        $r->meta['keywords'] = 'Some keyw';
        $resp = new Ac_Legacy_Controller_Response_Html;
        $resp->content = "<p>First</p>\n\n{$r}\n\n<p>Last</p>";
        $resp->replaceResultsInContent();
        $pg = new Ac_Legacy_Template_HtmlPage;
        $pg->htmlResponse = $resp;
        ob_start();
        $pg->show();
        $resp = ob_get_clean();
        
        if (!$this->assertEqual($a = $this->normalizeHtml($resp), $b = $this->normalizeHtml('
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <!-- powered by Avancore '.Ac_Avancore::version.' -->
                <title>Parent - Sub</title>
                <link rel="stylesheet" type="text/css" href="{FOO}/first.css" />
                <link rel="stylesheet" type="text/css" href="{FOO}/second.css" />
                <script type="text/javascript" src="{FOO}/first.js"> </script>
                <script type="text/javascript" src="{FOO}/second.js"> </script>
                <meta content="Some descr" name="description" />
                <meta content="Some keyw" name="keywords" />
            </head> 
            <body>
            <p>First</p>

            <div><p>Widget 1</p><div>Widget 1 body</div></div> <div class=\'group\'><div><p>Widget 2</p><div>Widget 2 body</div></div> <div><p>Widget 3</p><div>Widget 3 body</div></div></div>

            <p>Last</p>

            <script type="text/javascript">
                console.log(\'foo\');

            console.log(\'bar\');
            </script>
            </body>
            </html>            
        '))) {
            echo '<pre>'.htmlspecialchars($resp).'</pre>';
            var_dump($a, $b);
        }
        
        $r2 = new Ac_Result_Redirect(array('url' => 'http://www.example.com/', 'statusCode' => 301));
        $resp = new Ac_Legacy_Controller_Response_Html;
        $resp->content = "<p>First</p>\n\n{$r2}\n\n<p>Last</p>";
        $resp->replaceResultsInContent();
        
        if (!$this->assertEqual($resp->redirectUrl, "http://www.example.com/")) var_dump($resp->redirectUrl);
        if (!$this->assertEqual($resp->redirectType, 301)) var_dump($resp->redirectType);
        
        $json = new Ac_Result_Json(array('data' => array('foo' => array(1, 2, 3), 'bar' => array('john' => 'doe', 'not true' => false, 'dig' => 123.456), 'baz')));
        $resp = new Ac_Legacy_Controller_Response_Html;
        $resp->content = "AAA {$json} BBB";
        $resp->replaceResultsInContent();
        
        if (!$this->assertEqual($resp->content, json_encode($json->getData()))) var_dump($resp->content);
        $this->assertTrue($resp->noHtml);
        $this->assertTrue($resp->noWrap);
    }
    
    function testResponseToResult() {
        $resp = new Ac_Legacy_Controller_Response_Html;
        $resp->addAssetLibs(array('{FOO}/foo.css', '{BAR}/bar.js'));
        $resp->addPageTitle('xxx');
        $resp->appendPathway(array('http://www.example.com'), 'home');
        $resp->appendPathway(false, 'this one');
        $resp->content = '<p>The text</p>';
        $rh = new Ac_Result_Writer_RenderHtml;
        $rh->setEnvironment($e = new Ac_Result_Environment_Dummy);
        $r = new Ac_Result_Html;
        $r->put(''.$resp);
        $r->setWriter($rh);
        $r->write();
        
        if (!$this->assertEqual($a = $this->normalizeHtml($e->responseText), $b = $this->normalizeHtml('
            <!DOCTYPE html>
            <html>
            <head>
                <title>xxx</title>
                <link rel="stylesheet" type="text/css" href="{FOO}/foo.css" />
                <script type="text/javascript" src="{BAR}/bar.js"> </script>
            </head>
            <body>
            <p>The text</p> 
            </body>
            </html>            
        '))) {
            echo '<pre>'.htmlspecialchars($e->responseText).'</pre>';
        }
    }
    
    function testResponseToResponse() {
        $resp = new Ac_Legacy_Controller_Response_Html;
        $resp->addAssetLibs(array('{FOO}/foo.css'));
        $resp->addPageTitle('xxx');
        $resp->appendPathway('http://www.example.com', 'home');
        $resp->content = "<p>The text</p>\n";
        
        $resp2 = new Ac_Legacy_Controller_Response_Html;
        $resp2->addAssetLibs(array('{BAR}/bar.js'));
        $resp2->addPageTitle('yyy');
        $resp2->appendPathway('http://www.example.com/foo.html', 'current');
        $resp2->appendPathway(false, 'this one');
        $resp2->content = "<p>More text</p>\n";
        $resp2->initScripts[] = "console.log('foo');";
        
        $resp->content .= $resp2;
        $resp->content .= "<p>The end</p>";
        
        ini_set('html_errors', 0);
        $resp->replaceResultsInContent();
        $pg = new Ac_Legacy_Template_HtmlPage;
        $pg->htmlResponse = $resp;
        ob_start();
        $pg->show();
        $resp = ob_get_clean();
        
        if (!$this->assertEqual($this->normalizeHtml($resp), $this->normalizeHtml('
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <!-- powered by Avancore '.Ac_Avancore::version.' -->
                <title>xxx - yyy</title>
                <link rel="stylesheet" type="text/css" href="{FOO}/foo.css" />
                <script type="text/javascript" src="{BAR}/bar.js"> </script>
            </head> 
            <body>
            <p>The text</p>
            <p>More text</p>
            <p>The end</p>

            <script type="text/javascript">
                console.log(\'foo\');
            </script>
            </body>
            </html>            
        '))) 
        echo '<pre>'.htmlspecialchars($resp).'</pre>';
    }
    
    
}
<?php

require(dirname(__FILE__).'/bootstrap.php');

$resp = new Ac_Response();
$resp->mergeRegistry(array(
    'title' => 'Hello, Avancore',
    'assetLibs' => array(
        'examples.css',
    ),
    'headers' => array('X-Powered-By' => 'Avancore 0.3'),
    'content' => array(
        '<h1>Hello, Avancore!</h1>',
        '<p>Small example using Ac_Response and Ac_Response_Writer_HtmlPage<p>',
        '<div class="example">'.highlight_string(file_get_contents(__FILE__), true).'</div>',
    ),
    'debug' => array(
        'Some debug data',
    ),
));

$writer = new Ac_Response_Writer_HtmlPage();
$writer->setShowDebugInfo(true);
$writer->writeResponse($resp);
        
?>

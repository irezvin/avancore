<?php

require(dirname(__FILE__).'/bootstrap.php');

$r = new Ac_Result_Html();

$r->doctype = Ac_Result_Html::DOCTYPE_HTML5;
$r->title = 'Hello, Avancore';
$r->assets[] = 'examples.css';
$r->headers[] = 'X-Powered-By: Avancore 0.3';
$r->put("
        <h1>Hello, Avancore!</h1>
        <p>Small example using Ac_Result_Html<p>
        <div class='example'>".highlight_string(file_get_contents(__FILE__), true)."</div>
");
$r->comments[] = 'See HTML source for comments';

$r->write();


#!/bin/php
<?php

$s = <<<'EOT'
Ac_Legacy_Controller_Exception::CLASS => Ac_Controller_Exception::CLASS,
Ac_Legacy_Controller_Response::CLASS => Ac_Controller_Response::CLASS,
Ac_Legacy_Controller_Response_Json::CLASS => Ac_Controller_Response_Json::CLASS,
Ac_Legacy_Controller_Response_JsonPart::CLASS => Ac_Controller_Response_JsonPart::CLASS,
Ac_Legacy_Controller_Response_Global::CLASS => Ac_Controller_Response_Global::CLASS,
Ac_Legacy_Controller_Response_Html::CLASS => Ac_Controller_Response_Html::CLASS,
Ac_Legacy_Controller_Response_Http::CLASS => Ac_Controller_Response_Http::CLASS,
Ac_Legacy_Controller_Response_Part::CLASS => Ac_Controller_Response_Part::CLASS,
Ac_Legacy_Controller_Context_Http::CLASS => Ac_Controller_Context_Http::CLASS,
Ac_Legacy_Controller_Context::CLASS => Ac_Controller_Context::CLASS,
Ac_Legacy_Output_Joomla3::CLASS => Ac_Controller_Output_Joomla3::CLASS,
Ac_Legacy_Output_Debug::CLASS => Ac_Controller_Output_Debug::CLASS,
Ac_Legacy_Output_Joomla::CLASS => Ac_Controller_Output_Joomla::CLASS,
Ac_Legacy_Output_Native::CLASS => Ac_Controller_Output_Native::CLASS,
Ac_Legacy_Output_Joomla15::CLASS => Ac_Controller_Output_Joomla15::CLASS,
Ac_Legacy_Controller::CLASS => Ac_Controller::CLASS,
Ac_Legacy_Output::CLASS => Ac_Output::CLASS,
Ac_Legacy_Template_Helper_Html::CLASS => Ac_Template_Helper_Html::CLASS,
Ac_Legacy_Template_HtmlPage::CLASS => Ac_Template_HtmlPage::CLASS,
Ac_Legacy_Template_Html::CLASS => Ac_Template_Html::CLASS,
Ac_Legacy_Template_JoomlaPage::CLASS => Ac_Template_JoomlaPage::CLASS,
Ac_Legacy_Template_Helper::CLASS => Ac_Template_Helper::CLASS,
Ac_Legacy_Template::CLASS => Ac_Template::CLASS,
EOT;

$dirs = [];
$mv = [];
$sed = [];
foreach (explode("\n", trim($s)) as $row) {

    $row = preg_replace('/::CLASS,?/', '', trim($row));
    list($search, $replace) = explode(' => ', $row);
    $targetPhp = "classes/".str_replace("_", "/", $replace).".php";
    $srcPhp = "obsolete/".str_replace("_", "/", $search).".php";
    $dirs[] = dirname($targetPhp);
    $mv[] = "git mv '{$srcPhp}' '{$targetPhp}'";
    $sed[] = "s/\b{$search}\b/{$replace}/g";
    
}

foreach (array_unique($dirs) as $dir) {
    echo "\nif [ ! -d '{$dir}' ]; then mkdir -p {$dir}; fi";
}
foreach ($mv as $cmd) echo "\n$cmd";

$sedScript = basename(__FILE__, '.php').'.sed';

file_put_contents($sedScript, implode("\n", $sed));

echo "\nfind classes tests obsolete -type f -name '*.php' -print0 | xargs -0 sed -rf '{$sedScript}' -i";



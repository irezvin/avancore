<?php

require(dirname(__FILE__).'/testsStartup.php');

$f = new Ac_Form(null, array(
    'controls' => array(
        'foo' => array(
            'class' => 'Ac_Form_Control_Text',
            'type' => 'rte',
        ),
        'submit' => array(
            'class' => 'Ac_Form_Control_Button',
        ),
)));


$ctx = new Ac_Form_Context();
$ctx->populate(array('get', 'post'));
$response = new Ac_Controller_Response_Html($ctx);
$response->content = $f->fetchPresentation();
$response->content .= '<hr />'.$f->getControl('foo')->getValue();
$output = new Ac_Controller_Output_Native();
$output->showOuterHtml = true;
$output->outputResponse($response);

<?php

require(dirname(__FILE__).'/bootstrap.php');

class ExampleController extends Ac_Cr_Controller {
    
    function defaultAction() {
        echo '<h1>Default Action</h1>';
        echo 'some content here';
        $this->result['response']['title'] = 'Controller Example';
    }
    
    function actionResultInstance() {
        $r = new Ac_Cr_Result_Response;
        $r->setResponse($resp = new Ac_Response_HtmlPage);
        $resp->setTitle('Another Title');
?>
        <h1>Result Instance</h1>
        <script type="text/javascript">
            <!-- foo bar -->
            console.log("Foo");
        </script>
<?php
        $this->result = $r;
    }
    
    function actionJson() {
        echo "Lets output some json";
        return array('foo' => 'bar');
    }
    
}

$cr = new ExampleController();
$res = $cr->getResult();
$resp = $res->getResponse();
$writer = new Ac_Response_Writer_HtmlPage();
$writer->setShowDebugInfo(true);
$writer->writeResponse($resp);
        
?>

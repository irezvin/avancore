<?php

class Ac_Test_OldResult extends Ac_Test_Base {

    function testResultResponse() {
        
        $rr = new Ac_Cr_Result_Response();
        $rr->setMethodOutput('text');
        $this->assertEqual($rr->getResponse()->getContent(), 'text');
        
        $rr = new Ac_Cr_Result_Response();
        $rr->setMethodReturnValue('foo');
        $rr->setMethodOutput('bar');
        $this->assertEqual($rr->getResponse()->exportRegistry(true), array(
            'content' => 'foo',
            'debug' => 'bar',
        ));
        
        $rr = new Ac_Cr_Result_Response();
        $rr->getObjectResponse()->setRegistry('A Title', 'title');
        $rr->setMethodOutput('bar');
        if (!$this->assertEqual($x = $rr->getResponse()->exportRegistry(true), array(
            'title' => 'A Title',
            'content' => 'bar',
        ))) var_dump($x);
        
        $rr = new Ac_Cr_Result_Response();
        $rr->getObjectResponse()->setRegistry('A Title', 'title');
        $rr->setMethodReturnValue('foo');
        $rr->setMethodOutput('bar');
        if (!$this->assertEqual($x = $rr->getResponse()->exportRegistry(true), array(
            'title' => 'A Title',
            'content' => 'foo',
            'debug' => 'bar',
        ))) var_dump($x);

//        $r1 = new Ac_Response;
//        $r2 = new Ac_Response;
//        $r1->setContent('foo');
//        $r2->setContent('bar');
//        $r1->mergeWith($r2);
//        var_dump($r1->exportRegistry());
        
    }
    
}

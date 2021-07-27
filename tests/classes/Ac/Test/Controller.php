<?php

class Ac_Test_Controller extends Ac_Test_Base {

    function testSimpleCaching() {

        $base = 'http://localhost.local/sampleApp/';
        $self = '/sampleApp/index.php';
        
        $sam = $this->getSampleApp();
        if (!$sam->hasComponent('firstController')) $sam->addComponent ('Sample_FirstCon', 'firstController');
        $sam->getFrontController()->setDefaultController('firstController');
        
        // BEGIN: prepare the test case
        $output = new Ac_Controller_Output_Debug();
        $sam->getFrontController()->setOutput($output);
        
        $context = new Ac_Controller_Context_Http;
        $context->populate(null, false, $url = 'http://localhost.local/sampleApp/', $self);
        
        $cache = new Ac_Cache_Memory();
        $con = $sam->getController('firstController');
        $con->setCache($cache);
        $con->simpleCaching = true;
        
        // END: prepare the test case

        $sam->handleRequest($context);
        
        $this->assertFalse($con->getHitCache(), 'First time - cache not hit');
        
        $c2 = clone $context;
        $sam->handleRequest($c2);
        $this->assertTrue($con->getHitCache(), 'Second time - cache hit');

        $c3 = clone $context;
        $c3->requestMethod = 'post';
        $sam->handleRequest($c3);
            $this->assertFalse($con->getHitCache(), 'POST - cache not hit');
        
        $base = 'http://otherhost.local/sampleApp/';
        $self = '/sampleApp/index.php';
        
        $sam = $this->getSampleApp();
        if (!$sam->hasComponent('firstController')) $sam->addComponent ('Sample_FirstCon', 'firstController');
        $sam->getFrontController()->setDefaultController('firstController');
        
        // BEGIN: prepare the test case
        $output = new Ac_Controller_Output_Debug();
        $sam->getFrontController()->setOutput($output);
        
        $context = new Ac_Controller_Context_Http;
        $context->populate(null, false, $base, $self);
        $sam->handleRequest($context);
        $this->assertFalse($con->getHitCache(), 'Other HTTP_HOST - cache not hit');
        
        $c4 = clone $context;
        $sam->handleRequest($c4);
        $this->assertTrue($con->getHitCache(), 'Other HTTP_HOST, second time - cache hit');
        
        $c5 = clone $context;
        $con->xxx = true;
        $con->simpleCacheExtra[] = 'xxx';
        $sam->handleRequest($c5);
        $this->assertFalse($con->getHitCache(), 'Other HTTP_HOST, added simpleCacheExtra - cache not hit');
        
        $c6 = clone $context;
        $sam->handleRequest($c6);
        $this->assertTrue($con->getHitCache(), 'Other HTTP_HOST, added simpleCacheExtra, second try - cache hit');
        
    }
    
}

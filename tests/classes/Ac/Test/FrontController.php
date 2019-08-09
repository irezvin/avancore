<?php

class Ac_Test_FrontController extends Ac_Test_Base {

    function testFrontController() {
        
        $sample = $this->getSampleApp();
        if (!$sample->hasComponent('firstController')) {
            $sample->addComponent(array('class' => 'Sample_FirstCon'), 'firstController');
            $sample->addComponent(array('class' => 'Sample_SecondCon'), 'secondController');
        }
        $urlMapper = $sample->getUrlMapper();
        $sample->getFrontController()->setDefaultController('firstController');
        
        if (!$this->assertEqual(
            $result = 
                $urlMapper->stringToParams(
            $input = 
                '/secondController/otherMethod/10'), 
            $expected = 
                array(
                    'controller' => 'secondController', 
                    'action' => 'otherMethod',
                    'argument' => '10'
                )
        ))
            var_dump(compact('input', 'result', 'expected'));
        
        if (!$this->assertEqual(
                $result = 
                    $urlMapper->paramsToString(
                $input = 
                    array('controller' => '', 'action' => '')), 
                $expected = 
                    '/')
        )
            var_dump(compact('input', 'result', 'expected'));
        
        if (!$this->assertEqual(
                $result = 
                    $urlMapper->paramsToString(
                $input = 
                    array('action' => 'otherMethod', 'argument' => 10)), 
                $expected = 
                    '/otherMethod/10/')
        )
            var_dump(compact('input', 'result', 'expected'));
        
        if (!$this->assertEqual(
                $result = 
                    $urlMapper->paramsToString(
                $input = 
                    array('controller' => 'secondController', 'action' => 'otherMethod', 'argument' => 10)), 
                $expected = 
                    '/secondController/otherMethod/10/')
        )
            var_dump(compact('input', 'result', 'expected'));
    }
    
    function testRunWithUrlMapper() {
        
        $sam = $this->getSampleApp();

        $base = 'http://localhost.local/sampleApp/';
        $self = '/sampleApp/index.php';
        
        // BEGIN: prepare the test case
        $output = new Ac_Legacy_Output_Debug();
        $sam->getFrontController()->setLegacyOutput($output);
        
        $context = new Ac_Cr_Context;
        $request = new Ac_Request;
        $context->setRequest($request);
        $context->setBaseUrl($base);
        $request->populate($url = 'http://localhost.local/sampleApp/', $self);
        // END: prepare the test case
        
        $sam->handleRequest($context);
        if (!$this->assertEqual($result = $output->resOutput, $proper = "Sample.FirstCon.DefaultMethod http://localhost.local/sampleApp/")) {
            var_dump(compact('url', 'result', 'proper'));
        }
        
        $context = new Ac_Cr_Context;
        $request = new Ac_Request;
        $context->setRequest($request);
        $context->setBaseUrl($base);
        $request->populate($url = 'http://localhost.local/sampleApp/otherMethod/10', $self);
        
        $sam->handleRequest($context);
        if (!$this->assertEqual($result = $output->resOutput, $proper = "Sample.FirstCon.OtherMethod.10 http://localhost.local/sampleApp/")) {
            var_dump(compact('url', 'result', 'proper'));
        }
        
        $context = new Ac_Cr_Context;
        $request = new Ac_Request;
        $context->setRequest($request);
        $context->setBaseUrl($base);
        $request->populate($url = 'http://localhost.local/sampleApp/secondController/otherMethod/10', $self);
        $sam->handleRequest($context);
        if (!$this->assertEqual($result = $output->resOutput, $proper = "Sample.SecondCon.OtherMethod.10 http://localhost.local/sampleApp/secondController/")) {
            var_dump(compact('url', 'result', 'proper'));
        }
        
        $sam->getFrontController()->setUseUrlMapper(false);
        
        $context = new Ac_Cr_Context;
        $request = new Ac_Request;
        $context->setRequest($request);
        $context->setBaseUrl($base);
        $request->populate($url = 'http://localhost.local/sampleApp/index.php?controller=secondController&action=otherMethod&argument=10', $self);
        $sam->handleRequest($context);
        if (!$this->assertEqual($result = $output->resOutput, $proper = "Sample.SecondCon.OtherMethod.10 http://localhost.local/sampleApp/?controller=secondController")) {
            var_dump(compact('url', 'result', 'proper'));
        }
        
        $sam->getFrontController()->setUseUrlMapper(true);
        
        $context = new Ac_Cr_Context;
        $request = new Ac_Request;
        $context->setRequest($request);
        $context->setBaseUrl($base);
        $request->populate($url = 'http://localhost.local/sampleApp/oneArg/10', $self);
        $sam->handleRequest($context);
        if (!$this->assertEqual($result = $output->resOutput, $proper = "executeOneArg: onlyArg: 10")) {
            var_dump(compact('url', 'result', 'proper'));
        }
        
        $context = new Ac_Cr_Context;
        $request = new Ac_Request;
        $context->setRequest($request);
        $context->setBaseUrl($base);
        $request->populate($url = 'http://localhost.local/sampleApp/twoArgsOneDefault/10', $self);
        $sam->handleRequest($context);
        if (!$this->assertEqual($result = $output->resOutput, $proper = "executeTwoArgsOneDefault: firstArg: 10, secondArg: defValue")) {
            var_dump(compact('url', 'result', 'proper'));
        }
        
        $controller = $sam->getComponent('firstController');
        $controller->setContext(Ac_Legacy_Controller_Context_Http::createFromContext($sam->getAdapter()->createDefaultContext()));
        if (!$this->assertEqual($result = $controller->getResponse('twoArgsOneDefault', 22)->content, $proper = "executeTwoArgsOneDefault: firstArg: 22, secondArg: defValue")) {
            var_dump(compact('result', 'proper'));
        }
        
    }
    
    function testStaticUrlMapper() {
        
        $urlMapper = new Ac_Application_FrontUrlMapper(array('application' => $this->getSampleApp()));
        $urlMapper->setUrlMappers(array(
            'firstController' => array(
                'class' => 'Ac_UrlMapper_StaticSignatures',
                'ignoreMethods' => array('otherMethod'),
                'patterns' => array(
                    array('const' => array('action' => 'otherMethod'), 'definition' => '/zzzTestzzz/{argument}/{?nc}')
                )
            )
        ));
        
        $this->assertNull($urlMapper->stringToParams('/otherMethod/3'));
        
        if (!$this->assertEqual(
            $result = 
                $urlMapper->stringToParams(
            $input = 
                '/zzzTestzzz/3'), 
            $expected = 
                array(
                    'controller' => null, 
                    'action' => 'otherMethod',
                    'argument' => '3'
                )
        ))
            var_dump(compact('input', 'result', 'expected'));
        
        if (!$this->assertEqual(
            $result = 
                $urlMapper->paramsToString(
            $input = 
                array(
                    'controller' => null, 
                    'action' => 'otherMethod',
                    'argument' => '3'
                )
            ),
            $expected = 
                '/zzzTestzzz/3'
        ))
            var_dump(compact('input', 'result', 'expected'));
        
    }
    
    function testRunWithoutUrlMapper() {
        
        $sample = $this->getSampleApp();
        if (!$sample->hasComponent('firstController')) {
            $sample->addComponent(array('class' => 'Sample_FirstCon'), 'firstController');
            $sample->addComponent(array('class' => 'Sample_SecondCon'), 'secondController');
        }
        
        $sam = $this->getSampleApp();

        $base = 'http://localhost.local/sampleApp/';
        $self = '/sampleApp/index.php';
        
        // BEGIN: prepare the test case
        $output = new Ac_Legacy_Output_Debug();
        
        $context = new Ac_Cr_Context;
        $request = new Ac_Request;
        $context->setRequest($request);
        $context->setBaseUrl($base);
        $request->populate($url = 'http://localhost.local/sampleApp/?action=otherMethod&argument=10', $self);
        // END: prepare the test case
        
        $fc = new Ac_Application_FrontController(array(
            'application' => $sample,
            'useUrlMapper' => false,
            'defaultController' => 'firstController',
            'legacyOutput' => $output
        ));
        
        $fc->handleRequest($context);
        $con = $sam->getComponent('firstController');
        $this->assertEqual(''.$con->getUrl(array(), false), 'http://localhost.local/sampleApp/');
        $this->assertEqual(''.$con->getUrl(array('a' => 'b'), false), 'http://localhost.local/sampleApp/?a=b');
        $this->assertEqual(''.$con->getUrl(array('foo' => 'bar'), true), 'http://localhost.local/sampleApp/?action=otherMethod&argument=10&foo=bar');
        
    }
    
}

<?php

class Ac_Test_Controller extends Ac_Test_Base {
    
    function testBasic() {
        
        $base = 'http://example.com/index.php';
        $query = '?action=details&id=1';
        $url = $base.$query;
        
        $rq = new Ac_Request();
        $rq->populate($url, '/index.php');
        
        $ctx = new Ac_Cr_Context();
        $ctx->setRequest($rq); 
        
        $c = new testController1();
        $c->setContext($ctx);
        
        $this->assertEqual(
            $c->getUrl().'', 
            $base,
            'Controller doesn\'t have any used params after initialization and its URL equals to base since it doesn\'t have path prefix'
        );
        
        $this->assertEqual($c->getAction(), 'details');
        
        $this->assertEqual(
            $c->getUrl().'', 
            $base.'?action=details',
            "Controller remembers 'action' param after getAction()"
        );
        
        $result = $c->getResult();
        
        $this->assertEqual(
            $c->getUrl().'', 
            $url,
            'Controller remembers used params after method invocation'
        );
        
        $this->assertIsA(
            $result,
            'Ac_Cr_Result',
            'Ac_Cr_Controller::getResult should return Ac_Cr_Result instance'
        );
        
        $this->assertEqual(
            get_class($result), 
            'Ac_Cr_Result', 
            'Method returns Ac_Cr_Result by default'
        );
        
        $this->assertSame(
            $c->getResult(), 
            $result, 
            'Subsequent calls to getResult() return the same instance as before'
        );
        
        $this->assertEqual(
            $result->getMethodOutput(), 
            'Id is: 1',
            'Result content should be the same as expected and contain passed parameter \'id\' value'
        );
            
        // Clear controller state
        $c->reset();
        
        $this->assertEqual($c->getUrl().'', $base, "controller' getUrl() reverts to base after reset()");
        
        $result2 = $c->getResult();
        
        $this->assertTrue ($result !== $result2, 
            'After Ac_Cr_Controller::reset() controller should return other Result instance'
        );
        
        $this->assertIsA($result->getResponse(), 'Ac_Response');
        
        $this->assertEqual(
            $c->getUrl().'',
            $url,
            "controller' getUrl() returns used params after second invocation after reset()"
        );
        
    }    
}

class testController1 extends Ac_Cr_Controller {

    function getId() {
        return $this->use->id->value();
    }
    
    function actionList() {
        echo "<h1>This is a list</h1>";
    }
    
    function actionDetails() {
        echo "Id is: ".$this->getId();
        return array(
            'id' => $this->getId(),
            'name' => 'Some name',
            'description' => 'Some description',
        );
    }
    
    function executeMyUrl() {
        echo "My url is: ".$this->getUrl();
    }
    
    function defaultHandler() {
        echo "Default handler; action is:".$this->getAction();
    }

}
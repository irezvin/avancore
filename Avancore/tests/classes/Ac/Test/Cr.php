<?php

class Ac_Test_Cr extends Ac_Test_Base {
    
    function testRequest() {
        $rq = new Ac_Request();
        
        $_GET['foo'] = 'bar';
        
        $_GET['var2'] = 'getVal2';
        $_POST['var2'] = 'val2';
        
        $_POST['arr'] = array('key1' => 'value1', 'key2' => 'value2');
        $_POST['var3'] = 'val3';
        $_POST['arrOver'] = array('k' => 'v', 'k2' => 'v2');
        
        $_SERVER['FOO_BAR'] = 'fooBar value';
        
        
        // Test default usage & cascades
        
        $this->assertEqual(
            $rq->getValue('foo'), 
            'bar',
            'Default cascade (post, get, cookie)'
        );
        
        $this->assertEqual(
            $rq->getValue('var2'), 
            'val2',
            'Default cascade (post overrides get)'
        );

        $this->assertEqual(
            $rq->getValueFrom(Ac_Request_Src::get(), 'foo'), 
            'bar',
            'specify exact source'
        );
        
        $this->assertEqual(
            $rq->getValueFrom(Ac_Request_Src::post(), array('arr', 'key1')), 
            'value1',
            'specify source & path'
        );
        
        $this->assertEqual(
            $rq->getValueFrom(Ac_Request_Src::factory(array('get', 'post')), array('var2')), 
            'getVal2',
            'specify arbitrary cascade'
        );
        
        $this->assertEqual(
            $rq->getValueFrom(Ac_Request_Src::server(), 'FOO_BAR'), 
            'fooBar value',
            'get value from $_SERVER'
        );
        
        // Test some 'magic'
        
        $this->assertEqual(
            $rq->get->foo, 
            'bar',
            'Magic get->foo'
        );
        
        $this->assertEqual(
            $rq->server->fooBar, 
            'fooBar value',
            'Magic server->fooBar will return $_SERVER[FOO_BAR]'
        );
        
        $rq->server->fooBar = 'new value';
        
        $this->assertEqual(
            $rq->server->fooBar,
            'new value',
            'Magic setter: server->fooBar="value" '
        );
        
        $this->assertFalse(
            isset($rq->get->nonExistent), 
            'Magic __isset'
        );
        
        unset($rq->server->fooBar);
        
        $this->assertEqual(
            $rq->server->fooBar,
            'fooBar value',
            'Magic unsetter: unset server->fooBar returns original value back'
        );
        
        $rq->server->fooBar = null;
        
        $this->assertFalse(
            isset($rq->server->fooBar),
            'simulate !isset override with NULL value'
        );
        
        // Partially override parameters
        $rq->setValues(Ac_Request::post, array('var2' => 'over', 'arrOver' => array('k' => 'vOver')), false);
        
        $this->assertEqual(
            $rq->getValue(array(Ac_Request_Src::post(), 'var2')), 
            'over',
            'specify override'
        );
        
        $this->assertEqual(
            $rq->getValue(array(Ac_Request_Src::post(), 'arrOver', 'k')), 
            'vOver',
            'partial nested override'
        );
        
        $this->assertEqual(
            $rq->getValue(array(Ac_Request_Src::post(), 'arrOver', 'k2')), 
            'v2',
            'partial nested override leaves part of the original data without changes'
                
        );

        // Full override (rewrite) parameters
        $rq->setValues(Ac_Request::post, array('var2' => 'over', 'arrOver' => array('k' => 'vOver')), true);
        
        $this->assertEqual(
            $rq->getValue(array(Ac_Request_Src::post(), 'var2')), 
            'over',
            'specify override'
        );
        
        $this->assertEqual(
            $rq->getValue(array(Ac_Request_Src::post(), 'arrOver', 'k')), 
            'vOver',
            'full override'
        );
        
        $this->assertEqual(
            $rq->getValue(array(Ac_Request_Src::post(), 'arr')), 
            false,
            'full override doesnt leave anything of the original data'
        );
        
        $this->assertEqual(
            $rq->getValue(array(Ac_Request_Src::post(), 'arrOver', 'k2')), 
            false,
            'full override doesnt leave anything of the original data'
        );
        
    }
    
    function testUrl() {
        $rq = new Ac_Request();
        
        // Simulate following URL
        $url = 'http://example.com/index.php/aaa/bbb?foo=bar&baz=quux';
        
        $rq->server->httpHost = 'example.com';
        $rq->server->requestUri = '/index.php/aaa/bbb?foo=bar&baz=quux';
        $rq->server->pathInfo = '/aaa/bbb';
        $rq->server->scriptName = '/index.php';
        $rq->server->queryString = 'foo=bar&baz=quux';
        
        $this->assertEqual(
            Ac_Cr_Url::guess(true, $rq).'',
            $url,
            'Guess URL with pathinfo'
        );
        
        // Try to work without pre-populated pathInfo (as in nginx)
        
        $rq->server->pathInfo = null;
        
        $this->assertEqual(
            Ac_Cr_Url::guess(true, $rq).'',
            $url,
            "Guess URL without \$_SERVER['PATHINFO']"
        );
    }
    
}
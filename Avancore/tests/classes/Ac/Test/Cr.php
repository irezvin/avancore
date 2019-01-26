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
        
        $original = ';\'foo\"bar';
        $_GET['withSlashes'] = addslashes($original);
        $rq3 = new Ac_Request(true);
        $this->assertEqual(
            $rq3->get->withSlashes, 
            $original,
            'Ac_Request with stripSlashesGPC enabled should return string without slashes'
        );
        $rq4 = new Ac_Request(false);
        $this->assertEqual(
            $rq4->get->withSlashes, 
            addslashes($original), 
            'Ac_Request with stripSlashesGPC disabled should return unchanged string'
        );
        
        $rq5 = new Ac_Request();
        $ap = new Ac_Accessor_Path($rq5->get, 'nonExistent');
        if (!$this->assertEqual($v = $ap->value(), null)) var_dump($v);
        
        $rq5 = new Ac_Request();
        $v = $rq5->get->nonExistent;
        if (!$this->assertEqual($v, null)) var_dump($v);
        
        $empty = array();
        $rq6 = new Ac_Request();
        $ap = new Ac_Accessor_Path($empty, 'nonExistent');
        if (!$this->assertEqual($v = $ap->value(), null)) var_dump($v);
        
    }
     
    function testUrl() {
        $rq = new Ac_Request();
        
        // Simulate following URL
        $url = 'http://example.com/index.php/aaa/bbb?foo=bar&baz=quux';
        
        $popRes = ($rq->populate($url, '/index.php', array('foo' => 'fooPost')));

        $this->assertEqual($rq->getValueFrom('get', 'foo'), 'bar');
        $this->assertEqual($rq->get->foo, 'bar');
        $this->assertEqual($rq->post->foo, 'fooPost');
        $this->assertEqual($rq->value->foo, 'fooPost');
        
        $this->assertArraysMatch(
            array_keys($popRes), 
            array('get', 'post', 'request', 'server', 'env'),
            'populate() should return array with alteration data'
        );
        
        $this->assertEqual($rq->server->httpHost, 'example.com');
        $this->assertEqual($rq->server->requestUri, '/index.php/aaa/bbb?foo=bar&baz=quux');
        $this->assertEqual($rq->server->pathInfo, '/aaa/bbb');
        $this->assertEqual($rq->server->scriptName, '/index.php');
        $this->assertEqual($rq->server->queryString, 'foo=bar&baz=quux');
        
        $server = $rq->getValueFrom('server', array());
        $this->assertEqual(
            Ac_Url::guess(true, $server).'',
            $url,
            'Guess URL with pathinfo'
        );
        
        // Try to work without pre-populated pathInfo (as in nginx)
        
        $rq->server->pathInfo = null;
        
        $this->assertEqual(
            Ac_Url::guess(true, $server = $rq->getValueFrom('server', array())).'',
            $url,
            "Guess URL without \$_SERVER['PATHINFO']"
        );
    }
   
    function testContext() {
        $rq = new Ac_Request();
        
        $rq->populate('http://example.com/some/long/path', '/index.php');
        
        $rq->get->foo = array('fooKey1' => 'fooVal1', 'fooKey2' => 'fooVal2');
        $rq->get->sub = array('subKey1' => 'subVal1', 'subKey2' => array('subVal2.1', 'subVal2.2'));
        $rq->get->bar = 'barVal';
        $rq->post->baz = 'bazVal';
        
        $ctx = new Ac_Cr_Context();
        $ctx->setRequest($rq);
        $this->assertEqual(
            $ctx->getParam('bar'), 
            'barVal',
            'simple getParam()'
        );
        $this->assertEqual(
             $ctx->getParam(array('foo', 'fooKey1')), 
             'fooVal1',
             'getParam(array path)'
        );
        
        $this->assertEqual(
             $ctx->getParam('foo[fooKey1]'), 
             'fooVal1',
             'getParam(string path)'
        );
        
        $this->assertEqual(
            $ctx->useParam('inexistent'),
            NULL,
            'useParam(inexistent) should return NULL'
        );
        
        $ctx->useParam(array('foo', 'fooKey2'));
        $this->assertTrue(
            $ctx->isParamUsed('foo[fooKey2]'),
            'isParamUsed(string $path)'
        );
        $this->assertTrue(
            $ctx->isParamUsed(array('foo', 'fooKey2')),
            'isParamUsed(array $path) works the same way'
        );
        $this->assertFalse(
            $ctx->isParamUsed(array('foo')),
            'isParamUsed($parentPathSegment) without $includeSubPaths should return FALSE'
        );
        $this->assertTrue(
            $ctx->isParamUsed(array('foo'), true),
            'isParamUsed($parentPathSegment) with $includeSubPaths should return TRUE'
        );
        
        $this->assertArraysMatch(
            $ctx->getUsedParams(), 
            array(
                'inexistent' => NULL,
                'foo' => array('fooKey2' => 'fooVal2')
            ),
            'getUsedParams() should return used params in their order, including NULLs for inexistent ones'
        );
        $this->assertEqual(
            urldecode($ctx->createUrl().''),
            'http://example.com/some/long/path?foo[fooKey2]=fooVal2',
            'createUrl() for context without path prefix but with used params'
        );
        $this->assertEqual(
            urldecode($ctx->createUrl(array(), true).''),
            'http://example.com/some/long/path',
            'createUrl() for context without path prefix and with fullOverride should return base url only'
        );
        
        $this->assertEqual(
            urldecode($ctx->createUrl(array('bar' => 'barOverride', 'foo' => array('fooKey3' => 'fooVal3'))).''),
            'http://example.com/some/long/path?foo[fooKey2]=fooVal2&foo[fooKey3]=fooVal3&bar=barOverride',
            'createUrl($params)'
        );
        
        $contextWithPath = new Ac_Cr_Context();
        $contextWithPath->setRequest($rq);
        $contextWithPath->setPathPrefix('sub');
        
        $this->assertEqual(
            $p = $contextWithPath->useParam('subKey1'),
            'subVal1',
            '$contextWithPathPrefix->useParam()'
        );
        $this->assertEqual(
            $u = urldecode($contextWithPath->createUrl().''),
            'http://example.com/some/long/path?sub[subKey1]=subVal1',
            '$contextWithPathPrefix->createUrl()'
        );
        
        $sub = $ctx->createSubContext('sub');
        $this->assertSame($sub->getTopContext(), $ctx);
        $this->assertEqual($sub->getPathPrefix(), 'sub');
        
        
        $this->assertEqual(
            $sub->getParam('subKey1'),
            'subVal1',
            '$subContext->getParam()'
        );
        $sub->useParam('subKey2[0]');
        
        $this->assertEqual(
            urldecode(''.$sub->createUrl()),
            'http://example.com/some/long/path?foo[fooKey2]=fooVal2&sub[subKey2][0]=subVal2.1',
            '$subContext->createUrl()'
        );
        
        $this->assertEqual(
            $sub->param->subKey2__0, 
            'subVal2.1',
            'magic $context->param->key__subKey (read)'
        );
        
        // Isset for 'use' property has changed
        /*$this->assertFalse(
            isset($sub->use->subKey2__1),
            'magic isset($context->use->key__subKey returns FALSE (before actual use))'
        );*/
        
        $this->assertEqual(
            $sub->use->subKey2__1, 
            'subVal2.2',
            'magic $context->use->key__subKey returns value too'
        );
                    
        // __isset for 'use' property has changed
        /*$this->assertTrue(
            isset($sub->use->subKey2__1),
            'magic isset($context->use->key__subKey) returns true after param was used'
        );*/
        
        $this->assertTrue(isset($sub->param->subKey2__1));
        
        $this->assertTrue(!isset($sub->param->subKey2__3));
        
    }
    
}
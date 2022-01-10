<?php

class Ac_Test_UrlMapper extends Ac_Test_Base {
    
    function testUrlMapper() {
        
        $mpr = new Ac_UrlMapper_UrlMapper(array(
            'patterns' => array(
                array('definition' => '/', 'const' => array('action' => '')),
                '/foo',
                '/foo/{xx}',
                '/bar/{bazId}'
            ),
        ));
        $this->assertArraysMatch($mpr->listPatterns(), [
            '/bar/{bazId}',
            '/foo/{xx}',
            '/',
            '/foo',
        ], '%s', true);
        $this->assertEqual($mpr->stringToParams('/'), array('action' => ''));
        $this->assertEqual($mpr->stringToParams('/foo'), array());
        $this->assertEqual($mpr->stringToParams('/foo/15'), array('xx' => 15));
        $this->assertEqual($mpr->stringToParams('/bar/10'), array('bazId' => 10));
        $u = new Ac_Url('http://example.com/Foo/?action=');
        $u->setUrlMapper($mpr);
        $this->assertEqual($u->toString(), 'http://example.com/Foo/');
        
    }

    function testPattern() {
        
        $patterns = [
            
            "/blog/{?'categoryId'[-\\w/]+}/{articleId}.html{?c}" => [
                'params' => ['categoryId', 'articleId'], // must be in the definition
                'samples' => [
                    // sample => properResult or FALSE
                    '/blog/computing/linux.html' => ['categoryId' => 'computing', 'articleId' => 'linux'],
                    '/blog/stuff/literature/reading.html' => ['categoryId' => 'stuff/literature', 'articleId' => 'reading'],
                    '/blog/stuff/moreStuff/etc' => ['/blog/stuff/moreStuff/etc.html', 'categoryId' => 'stuff/moreStuff', 'articleId' => 'etc'],
                    '/blog/non allowed char/linux.html' => false,
                    '/podkwpkdwpokdw' => false
                ]
            ],
            "/{?c}something{}/{?nc}" => [
                'extra' => ['const' => ['cr' => 'default']],
                'params' => ['cr'],
                'samples' => [
                    '/something' => ['cr' => 'default'],
                    '/something/' => ['/something', 'cr' => 'default'],
                    'something' => ['/something', 'cr' => 'default'],
                    'something/' => ['/something', 'cr' => 'default'],
                ],
            ],
            
            "/group/{?'etc'.*}" => [
                'params' => ['etc'],
                'samples' => [
                    '/group/a/b/c/d/efgh.html' => ['etc' => 'a/b/c/d/efgh.html'],
                    '/group/' => ['etc' => ''],
                ]
            ]
            
        ];
        
        foreach ($patterns as $pattern => $info) {
            if (isset($info['extra'])) {
                $proto = $info['extra'];
                $proto['definition'] = $pattern;
                $pat = Ac_Prototyped::factory($proto, 'Ac_UrlMapper_Pattern');
            } else {
                $pat = new Ac_UrlMapper_Pattern(['definition' => $pattern]);
            }
            $params = $pat->getParams();
            $correctParams = $info['params'];
            if (!$this->assertArraysMatch($params, $correctParams, '%s', true)) {
                var_dump(compact('params', 'correctParams', 'pattern'));
            }
            if (isset($info['samples'])) {
                foreach ($info['samples'] as $path => $correctResult) {
                    if (isset($correctResult[0])) { // canonical version
                        $correctPath = $correctResult[0];
                        unset($correctResult[0]);
                    } else {
                        $correctPath = $path;
                    }
                    if ($correctResult === false) { // we don't expect pattern to match
                        if (!$this->assertFalse($result = $pat->stringToParams($path), 'Path MUST NOT match pattern, but it does')) {
                            var_dump(compact('path', 'pattern', 'result'));
                        }
                    } else {
                        if (!$this->assertArraysMatch($actual = $pat->stringToParams($path), $correctResult, 'stringToParams: path MUST much pattern', true)) {
                            var_dump(compact('path', 'pattern', 'correctResult', 'actual'));
                        }
                        $paramValues = $correctResult;
                        if (!$this->assertEqual($builtString = $pat->paramsToString($paramValues), $correctPath, 'paramsToString: result params must produce same pattern', true)) {
                            var_dump(compact('path', 'builtString', 'paramValues', 'pattern', 'correctPath'));
                        }
                        $modifiedParamsArray = $correctResult;
                        if (!$this->assertEqual($builtString = $pat->moveParamsToString($modifiedParamsArray), $correctPath, 'moveParamsToString: result params must produce same pattern', true)) {
                            var_dump(compact('path', 'builtString', 'pattern', 'correctPath'));
                        }
                        if (!$this->assertTrue(!count($modifiedParamsArray), 'moveParamsToString: params array must be empty')) {
                            var_dump(compact('modifiedParamsArray'));
                        }
                    }
                }
            }
        }
    }
    
    function testUrlProcessing() {
        
        $m = new Ac_UrlMapper_UrlMapper(array(
            'patterns' => array(
                array('definition' => '/', 'const' => array('action' => '')),
                '/{action}/{?c}',
                "/{?'action'details}/{id}/{?c}"
            ),
            'baseUrl' => 'https://www.example.com/cms/',
        ));
        
        $this->assertEqual($res = $m->strPathToQuery($url = 'https://www.example.com/cms/list/'),
            $proper = 'https://www.example.com/cms/?action=list');
        $this->assertEqual($res = $m->strQueryToPath($proper), $url);
        
        $this->assertEqual($res = $m->strPathToQuery($url = 'https://www.example.com/cms/details/5/'),
            $proper = 'https://www.example.com/cms/?action=details&id=5');
        $this->assertEqual($res = $m->strQueryToPath($proper), $url);
        
        $this->assertEqual($res = $m->strPathToQuery($url = 'http://example.com/cms/details/5/'),
            $proper = $url);
        $this->assertEqual($res = $m->strQueryToPath($proper), $url);
        
    }
    
    function testPartialParse() {
        
        $m = new Ac_UrlMapper_UrlMapper([
            'patterns' => [
                "/{?'controller'user}/{action}",
                "/{?'controller'api}/{...}",
                "/{?'controller'pages}/{+++}.html",
            ],
            'baseUrl' => '/cms/',
        ]);
        
        $examples = [
            '/cms/api/foo/bar/baz' => '/cms/foo/bar/baz?controller=api',
            '/cms/pages/whatever/and/more.html' => '/cms/whatever/and/more?controller=pages',
            '/cms/user/login' => '/cms/?controller=user&action=login',
        ];
        
        foreach ($examples as $orig => $mapped) {
            $origUrl = new Ac_Url($orig);
            $partiallyMappedString = ''.$m->pathToQuery($origUrl);
            $this->assertEqual($partiallyMappedString, $mapped, 'Forward mapping: %s');
            
            $partiallyMappedUrl = new Ac_Url($mapped);
            $sefString = ''.$m->queryToPath($partiallyMappedUrl);
            $this->assertEqual($sefString, $orig, 'Back mapping: %s');
        }
        
    }
    
//    function testFoo() {
//        var_dump($u = Ac_Url::guess(true, array(
//            'SERVER_NAME' => 'localhost',
//            'REQUEST_URI' => '/index.php/foo',
//            'SCRIPT_NAME' => '/index.php',
//            'PATH_INFO' => '/foo',
//        )), ''.$u);
//        var_dump($u = Ac_Url::guess(true, array(
//            'SERVER_NAME' => 'localhost',
//            'REQUEST_URI' => '/index.php/foo',
//            'SCRIPT_NAME' => '/index.php',
//            'PATH_INFO' => '/foo',
//        )), ''.$u);
//    }
    
}

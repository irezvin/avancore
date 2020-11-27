<?php

class Ac_Test_Url extends Ac_Test_Base {

    function testGuessBase() {
        foreach (array(
            'https://example.com/foo/' => array(
                'HTTPS' => 'on',
                'SERVER_NAME' => 'example.com',
                'REQUEST_URI' => '/foo/',
                'SCRIPT_NAME' => '/foo/index.php'
            ),
            'https://example.com/foo/' => array(
                'HTTPS' => 'on',
                'SERVER_NAME' => 'example.com',
                'REQUEST_URI' => '/foo/index.php',
                'SCRIPT_NAME' => '/foo/index.php'
            ),
            'https://example.com/foo/' => array(
                'HTTPS' => 'on',
                'SERVER_NAME' => 'example.com',
                'REQUEST_URI' => '/foo/aaa/bbb',
                'SCRIPT_NAME' => '/foo/index.php'
            ),
            'https://example.com/foo/' => array(
                'HTTPS' => 'on',
                'SERVER_NAME' => 'example.com',
                'REQUEST_URI' => '/foo/index.php/aaa/bbb',
                'SCRIPT_NAME' => '/foo/index.php'
            )
            
        ) as $base => $server) {
            $this->assertEqual(''.Ac_Url::guessBase($server), $base);
        }
    }
    
    function testHasBase() {
        // base, url, pathInfo | false
        $tests = array(
            ['http://example.com/index.php', 'http://example.com/index.php/foo/bar', '/foo/bar'],
            ['http://example.com/foo/', 'http://example.com/foo/bar/baz', 'bar/baz'],
            ['http://example.com/foo/', '/foo/', ''],
            ['http://example.com/foo/bar/baz/', '../baz/zzz', 'zzz'],
            ['http://example.com/foo/', '//example.com/foo/baz', 'baz'],
            ['hTTp://exAmpLe.cOm/foo/', 'httP://ExAmPle.com/foo/baz', 'baz'],
            ['http://example.com/inDEx.php', 'http://example.com/index.php/foo/bar', null],
        );
        foreach ($tests as $test) {
            $proper = isset($test[2]);
            $u = new Ac_Url($test[1]);
            $res = $u->hasBase($test[0], $pathInfo);
            $this->assertEqual($res, $proper);
            if ($proper) $this->assertEqual ($pathInfo, ($properPathInfo = $test[2]));
        }
    }
    
    function testPathInfo() {
        $u = new Ac_Url('http://example.com/foo');
        $u->pathInfo = 'xxx';
        $this->assertEqual($u->toString(), 'http://example.com/foo/xxx', 'pathInfo always separated from path using "/"');
        
        $u = new Ac_Url('http://example.com/foo/');
        $u->pathInfo = '/xxx';
        $this->assertEqual($u->toString(), 'http://example.com/foo/xxx', 'extraneous "/" is always removed between pathInfo and path');
    }
    
    function testGuessPathInfo() {
        $u = Ac_Url::guess(true, array(
            'HTTP_HOST' => 'example.com',
            'REQUEST_URI' => '/foo/',
            'SCRIPT_NAME' => '/foo/index.php',
        ));
        $this->assertEqual($u->path, '/foo/');
        $this->assertEqual($u->pathInfo, '');
        
        $u = Ac_Url::guess(true, array(
            'HTTP_HOST' => 'example.com',
            'REQUEST_URI' => '/foo/bar/baz',
            'SCRIPT_NAME' => '/foo/index.php',
        ));
        $this->assertEqual($u->path, '/foo/');
        $this->assertEqual($u->pathInfo, 'bar/baz');
        
        $u = Ac_Url::guess(true, array(
            'HTTP_HOST' => 'example.com',
            'REQUEST_URI' => '/foo/index.php/bar/baz',
            'SCRIPT_NAME' => '/foo/index.php',
        ));
        $this->assertEqual($u->path, '/foo/index.php');
        $this->assertEqual($u->pathInfo, '/bar/baz');
        
        $u = Ac_Url::guess(true, array(
            'HTTP_HOST' => 'example.com',
            'REQUEST_URI' => '/foo/bar/baz',
            'SCRIPT_NAME' => '/index.php',
        ));
        $this->assertEqual($u->path, '/');
        $this->assertEqual($u->pathInfo, 'foo/bar/baz');
        
    }
    
    function resolve($url, $baseUrl) {
        $a = new Ac_Url($url);
        return ''.$a->resolve($baseUrl);
    }
    
    function testRelativeResolve() {
        if (!$this->assertEqual(
            $result = $this->resolve(
                $url = "foo/bar/baz.html", 
                $base = "https://www.example.com/"), 
                $proper = "https://www.example.com/foo/bar/baz.html")) 
        { 
            var_dump(compact('url', 'base', 'proper', 'result'));
        }
        if (!$this->assertEqual($result = $this->resolve(
                $url = "../../foo/bar/baz.html", 
                $base = "https://www.example.com/"), 
                $proper = "https://www.example.com/foo/bar/baz.html")) 
        {
            var_dump(compact('url', 'base', 'proper', 'result'));
        }
        if (!$this->assertEqual($result = $this->resolve(
                $url = "../../foo/bar/baz.html", 
                $base = "https://www.example.com/top/sub/level3"),
                $proper = "https://www.example.com/top/foo/bar/baz.html")) 
        {
            var_dump(compact('url', 'base', 'proper', 'result'));
        }
        if (!$this->assertEqual($result = $this->resolve(
                $url = ".././foo/../bar/baz.html", 
                $base = "https://www.example.com/top/sub/index.php"),
                $proper = "https://www.example.com/top/bar/baz.html")) 
        {
            var_dump(compact('url', 'base', 'proper', 'result'));
        }
        if (!$this->assertEqual($result = $this->resolve(
                $url = "/zz", 
                $base = "https://www.example.com/top/sub/index.php"),
                $proper = "https://www.example.com/zz")) 
        {
            var_dump(compact('url', 'base', 'proper', 'result'));
        }
        if (!$this->assertEqual($result = $this->resolve(
                $url = "//zz.com/xx/yy", 
                $base = "https://www.example.com/top/sub/index.php"),
                $proper = "https://zz.com/xx/yy")) 
        {
            var_dump(compact('url', 'base', 'proper', 'result'));
        }
        if (!$this->assertEqual($result = $this->resolve(
                $url = "http://zz.com/xx/yy", 
                $base = "https://www.example.com/top/sub/index.php"),
                $proper = "http://zz.com/xx/yy")) 
        {
            var_dump(compact('url', 'base', 'proper', 'result'));
        }
        
        if (!$this->assertEqual($result = $this->resolve(
                $url = "?foo=1", 
                $base = "http://foo/bar/baz.html"),
                $proper = "http://foo/bar/baz.html?foo=1")) 
        {
            var_dump(compact('url', 'base', 'proper', 'result'));
        }
        
        $u = new Ac_Url('http://zz.com');
        $this->assertTrue($u->isFullyQualified($u));
        
        $u = new Ac_Url('//zz.com');
        $this->assertFalse($u->isFullyQualified($u));
        
        $u = new Ac_Url('xx');
        $this->assertTrue($u->isRelative());
        
        $u = new Ac_Url('/xx');
        $this->assertFalse($u->isRelative());
    }
    
    function testTrickyUrls() {
        $tricky = [
            'simple url' => 'https://example.com/?foo=bar&baz=quux',
            'https://example.com/?foo',
            'https://example.com/?foo=bar&baz[quux]&quuxDoo&abc[def]=1'
        ];
        foreach ($tricky as $k => $v) {
            if (is_numeric($k)) $comment = '';
                else $comment = ': '.$k;
            $r = ''.($u = new Ac_Url($v));
            $r = strtr($r, ['%5B' => '[', '%5D' => ']']);
            if (!$this->assertEqual($v, $r, "parse tricky url '{$v}'{$comment}")) {
                var_dump($r, $u->query);
            }
        }
    }
    
}

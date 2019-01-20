<?php

class Ac_Test_Url extends Ac_Test_Base {
    
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
        
        $u = new Ac_Url('http://zz.com');
        $this->assertTrue($u->isFullyQualified($u));
        
        $u = new Ac_Url('//zz.com');
        $this->assertFalse($u->isFullyQualified($u));
        
        $u = new Ac_Url('xx');
        $this->assertTrue($u->isRelative());
        
        $u = new Ac_Url('/xx');
        $this->assertFalse($u->isRelative());
    }
    
}

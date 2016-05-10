#!/usr/bin/php
<?php

// replaces legacy constructors with __construct

if (isset($_SERVER['argv'][1])) {
    $fn = $_SERVER['argv'][1];
    $php = file_get_contents($fn);
} else {
    $php = get_example();
}
$orig = $php;
$cl = preg_match_all('/\bclass\s+(\w+)(?:\s+extends\s+(\w+))?\b/i', $php, $matches, PREG_SET_ORDER);
foreach ($matches as $item) {
    $class = $item[1];
    $php = preg_replace("/\b(function\s+){$class}\b/i", "\\1__construct", $php);
    if (isset($item[2])) {
        $parent = $item[2];
        $php = preg_replace("/::{$parent}(\s*\()/i", "::__construct\\1", $php);
    }
}
if (isset($fn)) {
    if ($php !== $orig) {
        rename($fn, $fn.'.old');
        file_put_contents($fn, $php);
    }
} else {
    echo $php;
}

function get_example() {
    ob_start();
?>    
    class Foo {
        function Foo() {
        }
    }
    
    class Bar extends Foo {
        function Bar   () {
            parent::Foo ();
            Foo::Foo();
        }
    }
<?php    
    return ob_get_clean();
}
<?php

class Ac_Test_Buffer extends Ac_Test_Base {

    var $a = array();
    
    function testBuffer() {
        ob_start();
        Ac_Buffer::out('Foo');
        $s = ob_get_clean();
        if (!$this->assertEqual(
            $s, 
            'Foo', 
            'When buffering is diabled, output is done with the echo()'
        )) var_dump($s);
        
        Ac_Buffer::begin(array($this, 'xxx'));
        Ac_Buffer::out($a1 = array('Aaa'), $a2 = array('Bbb'));
        echo($a3 = 'Ccc');
        Ac_Buffer::out($a4 = array('Ddd'));
        Ac_Buffer::end();
        
        if (!$this->assertEqual(
                $this->a, 
                array($a1, $a2, $a3, $a4),
                'When buffering is enabled, echo() content is captured by the callback'
        )) var_dump($this->a);
        
    }
    
    function testBufferPassOutput() {
        ob_start();
        Ac_Buffer::begin(array($this, 'yyy'), true);
        Ac_Buffer::out('first');
        echo ('second'); // should work through yyy() too
        Ac_Buffer::end();
        if (!$this->assertEqual(
            $v1 = ob_get_clean(), 
            $v2 = '***first*** ***second*** ',
            'echo() in handler function with passOutput'
        )) var_dump($v1);
    }
    
    function xxx($arg) {
        $this->a[] = $arg;
    }    
    
    function yyy($arg) {
        echo '***'.$arg.'*** ';
    }
    
}
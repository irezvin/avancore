<?php

class DataMixin extends Ac_Model_Data {
    
    var $bar = '';
    
    function listOwnProperties() {
        return array('bar');
    }
    
}

class DataMixable extends Ac_Model_Mixable_Data {
    
    var $foo = '';
    
    var $bar = '';
    
    protected function getOwnPropertiesInfo() {
        $res = array(
            'foo' => array(
                'caption' => 'The Foo property',
            )
        );
        return $res;
    }
    
    protected function listOwnProperties() {
        return array('foo', 'bar');
    }

    function onCheck(&$errors) {
        parent::onCheck($errors);
        if ($this->foo == 'bar') $errors['foo'] = 'Foo is not supposed to have the value "bar"';
    }
    
}
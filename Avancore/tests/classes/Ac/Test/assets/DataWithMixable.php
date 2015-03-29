<?php

class DataMixable extends Ac_Model_Mixable_Data {
    
    var $foo = '';
    
    protected function getOwnPropertiesInfo() {
        $res = array(
            'foo' => array(
                'caption' => 'The Foo property',
            )
        );
        return $res;
    }
    
    protected function listOwnProperties() {
        return array('foo');
    }

    function onCheck(&$errors) {
        parent::onCheck($errors);
        if ($this->foo == 'bar') $errors['foo'] = 'Foo is not supposed to have the value "bar"';
    }
    
}
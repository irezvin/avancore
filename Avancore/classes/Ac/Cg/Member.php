<?php

class Ac_Cg_Member extends Ac_Prototyped implements Ac_I_ArraySerializable {

    var $visibility = 'public';
    
    var $default = false;
    
    var $comment = '';
    
    var $name = 'foo';
    
    function hasPublicVars() {
        return true;
    }
    
    static function priv($def = false, $comment = '') {
        return new Ac_Cg_Member(array(
            'visibility' => 'private',
            'comment' => $comment,
            'default' => $def,
        ));
    }
    
    static function pub($def, $comment = '') {
        return new Ac_Cg_Member(array(
            'visibility' => 'public',
            'comment' => $comment,
            'default' => $def,
        ));
    }
    
    static function prot($def, $comment = '') {
        return new Ac_Cg_Member(array(
            'visibility' => 'protected',
            'comment' => $comment,
            'default' => $def,
        ));
    }
    
    static function va($def, $comment = '') {
        return new Ac_Cg_Member(array(
            'visibility' => 'var',
            'comment' => $comment,
            'default' => $def,
        ));
    }
    
    function export($name = false, $indent = 4) {
        echo "\n";
        if ($name === false) $name = $this->name;
        echo $this->comment();
        echo str_repeat(' ', $indent); 
        echo $this->visibility.' $'.$name.' = '; Ac_Util_Php::export($this->default, false, $indent); echo ";\n";
    }
    
    function comment($indent = 4) {
        if (strlen(trim($this->comment))) {
            $lines = preg_split("/(\n\r|\r\n|\n)/", Cg_Util::indent($this->comment, 1));
            $i = str_repeat(' ', $indent);
            $c = array($i.'/**');
            foreach ($lines as $l) $c[] = $i.' * '.$l;
            $c[] = $i.' */';
            $res = implode("\n", $c)."\n";
        } else {
            $res = '';
        }
        return $res;
    }
    
    function serializeToArray() {
        $res = get_object_vars($this);
        $res['__class'] = get_class($this);
        return $res;
    }
    
    function unserializeFromArray($array) {
        foreach ($array as $k => $v) $this->$k = $v;
    }
    
    
    
    
}
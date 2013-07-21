<?php

class Ac_Result_Placeholder_Template_Scripts extends Ac_Result_Placeholder_Template {
    
    protected $glue = "\n    ";
    protected $prefix = "\n    <script type='text/javascript'>";
    protected $suffix = "\n    </script>\n";
    
    protected function getStrings(Ac_Result_Placeholder $placeholder, Ac_Result_Writer $writer) {
        $res = array();
        foreach ($placeholder->getItems() as $item) {
            if (is_object($item) && $item instanceof Ac_Js_Script) $item = $item->toJs();
            $res[] = $item;
        }
        return $res;
    }
    
    
}
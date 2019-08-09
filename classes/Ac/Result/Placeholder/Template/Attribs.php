<?php

class Ac_Result_Placeholder_Template_Attribs implements Ac_I_Result_PlaceholderTemplate {
    
    public function writePlaceholder(Ac_Result_Placeholder $placeholder, Ac_Result_Writer $writer) {
        echo Ac_Util::mkAttribs($placeholder->getItems());
    }
    
}
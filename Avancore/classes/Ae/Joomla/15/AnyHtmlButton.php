<?php

        
if (!class_exists('JButton')) jimport('joomla.html.toolbar');

class JButtonAnyHtml extends JButton {

    function fetchId() {
    }
    
    function fetchButton() {
        $args = func_get_args();
        return $args[1];
    }

}            


class Ae_Joomla_15_AnyHtmlButton extends JButtonAnyHtml {
}

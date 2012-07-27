<?php
    
class Ac_Legacy_Output_Joomla15 extends Ac_Legacy_Output_Joomla {
    
    var $removePathwayDuplicates = true;

    function __construct() {
        $this->mainframe =  &JFactory::getApplication();
    }
    
    function setPageTitle($pageTitle) {
        $doc = JFactory::getDocument();
        $doc->setTitle($pageTitle);
    }
    
    function outputResponse(Ac_Legacy_Controller_Response_Http $response, $asModule = false) {
        if ($response->redirectUrl) {
            $permanent = ($response->redirectType == Ac_Legacy_Controller_Response_Http::redirPermanent);
            $this->mainframe->redirect(''.$response->redirectUrl, '', 'message', $permanent);
        }
        parent::outputResponse($response, $asModule);
    }
    
    function showPathway(Ac_Legacy_Controller_Response_Html $response) {
        if ($response->pathway) {
            $pw = & $this->mainframe->getPathway();
            $pathway = $response->pathway;
            foreach ($pw->getPathway() as $item) {
                foreach ($pathway as $k => $v) {
                    list ($url, $text) = $v;
                    if (strlen($url)) {
                        if (JRoute::_($url) == JRoute::_($item->link)) unset($pathway[$k]);
                    } else {
                        if (JRoute::_($text) == JRoute::_($item->name)) unset($pathway[$k]);
                    }
                }
            }
            foreach ($pathway as $urlAndText) {
                list($url, $text) = $urlAndText;
                $pw->addItem($text, JRoute::_($url));
            }
        }
    }
    
    
    function appendMetaTag($name, $value) {
        $doc = JFactory::getDocument();
        if ($name == 'description' || $name == 'keywords') {
            $md = $doc->getMetaData($name, false);
            if (strlen($md)) $value = $md.', '.$value;
        }
        $doc->setMetaData($name, $value);
    }
    
    static function addHtmlToJoomlaToolbar($html) {
        $bar = JToolBar::getInstance('toolbar');
        $bar->appendButton('AnyHtml', $html);
        
    }
    
    
}

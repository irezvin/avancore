<?php
    
class Ac_Legacy_Output_Joomla15 extends Ac_Legacy_Output_Joomla {
    
    var $removePathwayDuplicates = true;
    
    var $replaceMetaKeywords = false;
    
    var $replaceMetaDescription = false;

    function __construct() {
        $this->mainframe = JFactory::getApplication();
    }
    
    function setPageTitle($pageTitle) {
        $doc = JFactory::getDocument();
        $doc->setTitle($pageTitle);
    }
    
    function outputResponse(Ac_Legacy_Controller_Response_Html $response, $asModule = false) {
        if ($response->redirectUrl) {
            if (strlen(''.$response->redirectUrl) > 2000) {
                while(ob_get_level()) ob_end_clean ();
                $au = new Ac_Url($response->redirectUrl);
                echo $au->getJsPostRedirect();
                die();
            }
            $permanent = ($response->redirectType == Ac_Legacy_Controller_Response_Http::redirPermanent);
            $this->mainframe->redirect(''.$response->redirectUrl, '', 'message', $permanent);
        }
        parent::outputResponse($response, $asModule);
    }
    
    function showPathway(Ac_Legacy_Controller_Response_Html $response) {
        if ($response->pathway) {
            $pw = $this->mainframe->getPathway();
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
        if ($name == 'description' && !$this->replaceMetaDescription || $name == 'keywords' && !$this->replaceMetaKeywords) {
            $md = $doc->getMetaData($name, false);
            if (strlen($md)) $value = $md.', '.$value;
        }
        $doc->setMetaData($name, $value);
    }
    
    static function addHtmlToJoomlaToolbar($html) {
        Ac_Util::loadClass('Ac_Joomla_15_AnyHtmlButton');
        $bar = JToolBar::getInstance('toolbar');
        $bar->appendButton('AnyHtml', $html);
    }
    
    
}

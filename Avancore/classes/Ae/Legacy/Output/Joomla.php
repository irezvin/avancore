<?php

class Ae_Legacy_Output_Joomla extends Ae_Legacy_Output {

    var $asModule = false;
    
    /**
     * @var mosMainFrame
     */
    var $mainframe;
    
    var $shownCss = array();
    
    var $shownJs = array();
    
    function __construct() {
        $this->mainframe = & $GLOBALS['mainframe'];
    }
    
    function setPageTitle($pageTitle) {
        $this->mainframe->setPageTitle(implode(" - ", $pt));
    }
    
    function addCustomHeadTag($tag, $unique = false) {
        $document=& JFactory::getDocument();
		if($document->getType() == 'html') {
		    $ok = true;
		    if ($unique) {
		        $head = $document->getHeadData();
		        if (in_array($tag, $head['custom'])) $ok = false; 
		    }
		    if ($ok) $document->addCustomTag($tag); 
		}
    }
    
    /**
     * @param Ae_Legacy_Controller_Response_Html $response
     */
    function outputResponse(Ae_Legacy_Controller_Response_Html $response) {
        
        if (func_num_args() > 1) {
            $asModule = func_get_arg(1);
        } else {
            $asModule = $this->asModule;
        }

        if (class_exists('Ae_Legacy_Controller_Response_Global', false)) {
            $glob = Ae_Legacy_Controller_Response_Global::getInstance();
            if ($glob->hasResponse() && ($glob->getResponse() !== $response)) $response->mergeWithResponse($glob->getResponse());
        }

        //var_dump($response);
        
        //var_dump('As module: '. $asModule);
            
        if ($response->headTags) {
            foreach ($response->headTags as $t) {
                if ($asModule)  echo $t;
                    else $this->addCustomHeadTag($t);
            }
        }
        
        if (!$asModule) {
        
            if ($response->jsLibs) {
                foreach ($response->jsLibs as $jsLibData) {
                    if (in_array($jsLibData, $this->shownJs)) continue;
                    $this->shownJs[] = $jsLibData;
                    list($jsLib, $isLocal) = $jsLibData;
                    $this->addCustomHeadTag($response->getJsScriptTag($jsLib, $isLocal), true);
                }
            }
            if ($response->cssLibs) {
                foreach ($response->cssLibs as $cssLibData) {
                    if (in_array($cssLibData, $this->shownCss)) continue;
                    $this->shownCss[] = $cssLibData;
                    list($cssLib, $isLocal) = $cssLibData;
                    $this->addCustomHeadTag($response->getCssLibTag($cssLib, $isLocal), true);
                }
            }
            
            if ($response->pageTitle) {
                $pt = array();
                foreach ($response->pageTitle as $t) {
                    //$pt[] = $this->_unhtmlentities($t);
                    $pt[] = html_entity_decode($t, ENT_QUOTES, 'cp1251');
                }
                $this->setPageTitle(implode(" - ", $pt));
            }
            $this->showPathway($response);
            if ($response->metas) {
                foreach ($response->metas as $name => $value) {
                    $this->appendMetaTag($name, $value);
                }
            }
            
        } else {
            
            if ($response->jsLibs) {
                foreach ($response->jsLibs as $jsLibData) {
                    if (in_array($jsLibData, $this->shownJs)) continue;
                    $this->shownJs[] = $jsLibData;
                    list ($jsLib, $isLocal) = $jsLibData;
                    echo $response->getJsScriptTag($jsLib, $isLocal);
                }
            }
            if ($response->cssLibs) {
                foreach ($response->cssLibs as $cssLibData) {
                    if (in_array($cssLibData, $this->shownCss)) continue;
                    $this->shownCss[] = $cssLibData;
                    list ($cssLib, $isLocal) = $cssLibData;
                    echo $response->getCssLibTag($cssLib, $isLocal);
                }
            }
            
        }

        if ($response->noHtml) {
            while(ob_get_level()) ob_end_clean();
            if (strlen($response->contentType)) header('content-type: '.$response->contentType);
        }
        
        echo $response->replacePlaceholders(false, true);
        
        if ($response->noHtml) {
            die();
        }
        
    }
    
    function appendMetaTag($name, $value) {
        $this->mainframe->appendMetaTag($name, htmlspecialchars($value));        
    }
    
    /**
     * @param Ae_Legacy_Controller_Response_Html $response
     */
    function showPathway($response) {
            if ($response->pathway) {
                $pw = $response->pathway;
                foreach ($pw as $urlAndText) {
                    list($url, $text) = $urlAndText;
                    if (strlen($url)) {
                        $pathwayStr = '<a href="'.htmlspecialchars($url).'">'.$text."</a>";
                    }
                    else $pathwayStr = $text; 
                    $this->mainframe->appendPathWay($pathwayStr);
                }
            }
    }
    
}

?>
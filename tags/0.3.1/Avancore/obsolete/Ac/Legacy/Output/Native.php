<?php

class Ac_Legacy_Output_Native extends Ac_Legacy_Output {
    
    var $showOuterHtml = false;
    
    var $htmlTemplateSettings = array();
    
    var $defaultContentType = 'text/html; charset=utf-8';
    
    var $allowCleanBufferAndDie = true;

    var $templateClass = 'Ac_Legacy_Template_HtmlPage';
    
    function Ac_Output_Native($options = array()) {
        Ac_Util::bindAutoparams($this, $options);
    }
    
    /**
     * @param Ac_Legacy_Controller_Response_Html $r
     */
    function outputResponse(Ac_Legacy_Controller_Response_Html $r, $asModule = false) {
        
        if (class_exists('Ac_Legacy_Controller_Response_Global', false)) {
            $glob = Ac_Legacy_Controller_Response_Global::getInstance();
            if ($glob->hasResponse() && ($glob->getResponse() !== $r)) $r->mergeWithResponse($glob->getResponse());
        }
        
        $t = $this->templateClass;
        $tpl = new $t($this->htmlTemplateSettings);
        $tpl->htmlResponse = $r;
        if (!$r->contentType) $r->contentType = $this->defaultContentType;
        $redir = false;
        if ($r->redirectUrl) {
            if (strlen(''.$r->redirectUrl) > 2000) {
                while(ob_get_level()) ob_end_clean ();
                $au = new Ac_Url($r->redirectUrl);
                echo $au->getJsPostRedirect();
                die();
            }
            $redir = $r->redirectUrl;
            if (is_a($redir, 'Ac_Url')) $redir = $redir->toString();
        }
        if (!headers_sent()) {
            if ($r->contentType) header('Content-type: '.$r->contentType);
            if ($redir) {
                header( 'Pragma: no-cache' );
                header('Location: '. $redir, TRUE, 302);
            }
            foreach ($r->extraHeaders as $eh) {
                if (is_array($eh)) {
                    header($eh[0], true, $eh[1]);
                }
                else {
                    header($eh);
                }
            }
        }
        
        if ($r->noHtml || $r->noWrap) {
            if ($this->allowCleanBufferAndDie) while(ob_get_level()) ob_end_clean();
        }
        $showHtml = !$r->noHtml && $this->showOuterHtml;
        
        if (headers_sent() && $redir) {
            ob_start();
?> 
            <script type='text/javascript'>
                document.location = '<?php echo addslashes($redir); ?>';
            </script>
<?php
            $r->content .= ob_get_clean();           
        }
        
        if ($showHtml) {
            $tpl->showDefault();
        } else {
            $tpl->showCssLibs();
            $tpl->showJsLibs();
            $c = $r->replacePlaceholders(false, true);
            echo $c;
        }
        if ($r->noHtml || $r->noWrap) {
            if ($this->allowCleanBufferAndDie) die();
        }
    }
    
}

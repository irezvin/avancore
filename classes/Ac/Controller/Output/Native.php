<?php

class Ac_Controller_Output_Native extends Ac_Controller_Output {
    
    var $showOuterHtml = false;
    
    var $htmlTemplateSettings = array();
    
    var $defaultContentType = 'text/html; charset=utf-8';
    
    var $allowCleanBufferAndDie = true;

    var $templateClass = 'Ac_Template_HtmlPage';
    
    function headersSent() {
        return headers_sent();
    }
    
    function header($header, $replace = true, $httpResponseCode = 0) {
        return header($header, $replace, $httpResponseCode);
    }
    
    function cancelBuffering() {
        while(ob_get_level()) ob_end_clean();
    }
    
    /**
     * @param Ac_Controller_Response_Html $r
     */
    function outputResponse(Ac_Controller_Response_Html $r, $asModule = false) {
        
        $r->replaceResultsInContent();
        
        if (class_exists('Ac_Controller_Response_Global', false)) {
            $glob = Ac_Controller_Response_Global::getInstance();
            if ($glob->hasResponse() && ($glob->getResponse() !== $r)) $r->mergeWithResponse($glob->getResponse());
        }
        
        $t = $this->templateClass;
        $tpl = new $t($this->htmlTemplateSettings);
        $tpl->htmlResponse = $r;
        if (!$r->contentType) $r->contentType = $this->defaultContentType;
        $redir = false;
        if ($r->redirectUrl) {
            if (strlen(''.$r->redirectUrl) > 2000) {
                if ($this->allowCleanBufferAndDie) $this->cancelBuffering();
                $au = new Ac_Url($r->redirectUrl);
                echo $au->getJsPostRedirect();
                if ($this->allowCleanBufferAndDie) $this->exitPhp();
            }
            $redir = $r->redirectUrl;
            if (is_a($redir, 'Ac_Url')) $redir = $redir->toString();
        }
        if (!$this->headersSent()) {
            if ($r->contentType) $this->header('Content-type: '.$r->contentType);
            if ($redir) {
                $this->header('Pragma: no-cache');
                $this->header('Location: '. $redir, TRUE, 302);
            }
            foreach ($r->extraHeaders as $eh) {
                if (is_array($eh)) {
                    $this->header($eh[0], true, $eh[1]);
                }
                else {
                    $this->header($eh);
                }
            }
        }
        
        if ($r->noHtml || $r->noWrap) {
            if ($this->allowCleanBufferAndDie) $this->cancelBuffering ();
        }
        $showHtml = !$r->noHtml && $this->showOuterHtml;
        
        if (headers_sent() && $redir) {
            ob_start();
?> 
            <meta http-equiv="refresh" content="1;<?php echo htmlspecialchars($redir); ?>" />
            <script type='text/javascript'>
                document.location = '<?php echo addslashes($redir); ?>';
            </script>
<?php
            $r->headTags[] = ob_get_clean();
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
            if ($this->allowCleanBufferAndDie) $this->exitPhp();
        }
    }
    
}


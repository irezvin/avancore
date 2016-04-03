<?php

class Ac_Legacy_ResultWriter extends Ac_Result_Writer_AbstractHtmlRenderer {

    /**         
     * @var Ac_Legacy_Controller_Response_Html
     */
    protected $response = false;

    function setResponse(Ac_Legacy_Controller_Response_Html $response = null) {
        if ($response === null) $response = false;
        $this->response = $response;
    }
    
    /**
     * @return Ac_Legacy_Controller_Response_Html
     */
    function getResponse() {
        if ($this->response === false) return Ac_Legacy_Controller_Response_Global::r();
        return $this->response;
    }

    protected function implWriteBase(Ac_Result_Http_Abstract $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        $resp = $this->getResponse();
        if (strlen($ct = $r->getContentType())) {
            if (strlen($cs = $r->getCharset())) $ct .= '; charset='.$cs;
            $resp->contentType = $ct;
        }
        foreach ($r->getHeaders() as $head) $resp->addExtraHeader($head);
        //ob_start();
        $r->echoContent();
        /*$cnt = ob_get_clean();
        if ($r->getOverrideMode() === Ac_Result::OVERRIDE_NONE) {
            $resp->content .= $cnt;
        } else {
            $resp->content = $cnt;
        }
        echo($resp->content);*/
    }
    
    protected function implWriteHttp(Ac_Result_Http $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        if ($r instanceof Ac_Result_Redirect && (($u = $r->getUrl()))) {
            $resp = $this->getResponse();
            $resp->redirectType = $r->getStatusCode();
            $resp->redirectUrl = $u;
        } else {
            $this->implWriteBase($r, $t, $s);
            $resp = $this->getResponse();
            $resp->noWrap = true;
            $resp->noHtml = true;
        }
    }
    
    protected function implWriteHtml(Ac_Result_Html $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        $this->implWriteBase($r, $t, $s);
        $resp = $this->getResponse();
        if ($r->getOverrideMode() === Ac_Result::OVERRIDE_ALL) {
            $resp->noHtml = true;
        }
        $u = array_flip($r->listPlaceholders(true));
        if (isset($u['title'])) {
            $t = $r->title;
            if ($t->getOverwriteOnMerge()) $resp->pageTitle = array();
            foreach ($t->getItemsForWrite($this) as $t) {
                $resp->addPageTitle($t);
            }
        }
        if (isset($u['meta'])) {
            $meta = $r->meta;
            $mp = array_flip($meta->listUsedPlaceholders());
            if (isset($mp['description'])) {
                $d = $meta['description'];
                $resp->metas['description'] = implode(", ", $d->getItemsForWrite($this));
                unset($mp['description']);
            }
            if (isset($mp['keywords'])) {
                $d = $meta['keywords'];
                $resp->metas['keywords'] = implode(", ", $d->getItemsForWrite($this));
                unset($mp['keywords']);
            }
            if (isset($mp['http'])) {
                foreach($mp['http']->getItemsForWrite($this) as $k => $v) {
                    $resp->metas[$k] = array($v, true);
                }
                unset($mp['http']);
            }
            foreach ($mp as $k => $v) {
                $resp->metas[$k] = $v;
            }
        }
        if (isset($u['headTags'])) {
            $resp->headTags = array_merge($resp->headTags, $r->headTags->getItemsForWrite($this));
        }
        
        if (isset($u['assets'])) {
            $resp->addAssetLibs($r->assets->getItemsForWrite($this));
        }
        
        if (isset($u['headScripts'])) {
            $resp->headTags[] = $r->headScripts->render($this);
        }
        
        if (isset($u['bodyAttribs'])) $resp->setData($u->bodyAttribs->getItemsForWrite($this), 'bodyAttribs');
        
        if (isset($u['doctype'])) $resp->setData(implode('', $r->doctype->getItemsForWrite($this)), 'doctype');
        
        if (isset($u['pathway'])) {
            if ($r->getOverrideMode()) $resp->pathway = array();
            foreach ($r->pathway as $p) $resp->pathway[] = $p;
        }
        
    }
    
    protected function implWriteNoCharset(Ac_Result $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        if ($r instanceof Ac_Result_Http) {
            $this->implWriteHttp($r, $t, $s);
        } elseif ($r instanceof Ac_Result_Html) {
            $this->implWriteHtml($r, $t, $s);
        } else {
            throw Ac_E_InvalidCall::wrongClass('r', $r, array('Ac_Result_Html', 'Ac_Result_Http'));
        }
        // todo: initScripts & comments placeholders
    }
    
}
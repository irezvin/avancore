<?php

abstract class Ac_Result_Writer_PassHtml extends Ac_Result_Writer_AbstractHtmlRenderer {
    
    protected $assetPlaceholders = false;

    function setAssetPlaceholders(array $assetPlaceholders) {
        $this->assetPlaceholders = $assetPlaceholders;
    }

    /**
     * @return array
     */
    function getAssetPlaceholders() {
        if ($this->assetPlaceholders === false) {
            $app = ($s = $this->getStage())? $s->getApplication() : Ac_Application::getDefaultInstance();
            if (!$app) return array();
            return $app->getAssetPlaceholders(true);
        }
        return $this->assetPlaceholders;
    }
    
    protected function wp($placeholderId, $_ = null) {
        $args = func_get_args();
        foreach ($args as $placeholderId) 
            $this->source->getPlaceholder($placeholderId)->write($this);
    }

    abstract protected function implPassToCms(Ac_Result_Html $result, Ac_Result_Stage $s = null);
    
    protected function implWriteNoCharset(Ac_Result $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        if (!$r instanceof Ac_Result_Html) throw Ac_E_InvalidCall::wrongClass('r', $r, 'Ac_Result_Html');
        if ($r->getContentType() === false) $r->setContentType('text/html');
        if ($t) throw new Ac_E_InvalidCall(__CLASS__." can work only without target response");
        $this->implPassToCms($result, $s);
    }
    
}
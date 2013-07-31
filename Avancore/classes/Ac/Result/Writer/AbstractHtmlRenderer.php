<?php

abstract class Ac_Result_Writer_AbstractHtmlRenderer extends Ac_Result_Writer_WithCharset {

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
    
    protected function writePlaceholder($placeholderId, $_ = null) {
        $args = func_get_args();
        foreach ($args as $placeholderId) 
            $this->source->getPlaceholder($placeholderId)->write($this);
    }

    
}
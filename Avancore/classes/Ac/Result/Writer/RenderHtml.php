<?php

class Ac_Result_Writer_RenderHtml extends Ac_Result_Writer_Plain {
    
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
    
    protected function implWrite(Ac_Result $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        if (!$r instanceof Ac_Result_Html) throw Ac_E_InvalidCall::wrongClass('r', $r, 'Ac_Result_Html');
?>
<?php   $this->wp('doctype'); ?>
<html<?php $this->wp('htmlAttribs'); ?>>
    <head><?php ?>
<?php $this->wp('title', 'headTags', 'assets', 'headScripts'); ?>
    </head>
    <body<?php $this->wp('bodyAttribs'); ?>>
<?php   echo parent::implWrite($r, $t, $s); ?>
<?php   $this->wp('initScripts'); ?>
    </body>
</html>
<?php   $this->wp('comments'); ?>
<?php
    }
    
}
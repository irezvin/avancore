<?php

class Ac_Result_Placeholder_Template_Assets extends Ac_Result_Placeholder_Template_InHtml {
    
    /**
     * @var array
     */
    protected $assetPlaceholders = false;

    function setAssetPlaceholders(array $assetPlaceholders) {
        $this->assetPlaceholders = $assetPlaceholders;
    }

    /**
     * @return array
     */
    function getAssetPlaceholders() {
        return $this->assetPlaceholders;
    }
    
    protected function getStrings(Ac_Result_Placeholder $placeholder, Ac_Result_Writer $writer) {
        if ($this->assetPlaceholders === false) {
            $assetPlaceholders = array();
            if ($writer instanceof Ac_Result_Writer_RenderHtml)
                $assetPlaceholders = $writer->getAssetPlaceholders();
            else {
                $app = Ac_Application::getDefaultInstance();
                if ($app) $assetPlaceholders = $writer->getAssetPlaceholders();
            }
        } else {
            $assetPlaceholders = $this->assetPlaceholders;
        }
        $ren = new Ac_Util_AssetRenderer($assetPlaceholders);
        return $ren->renderAssets($placeholder->getItems(), true);
    }
    
}
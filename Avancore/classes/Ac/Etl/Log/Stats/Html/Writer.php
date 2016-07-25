<?php

class Ac_Etl_Log_Stats_Html_Writer {
    
    var $assetPlaceholders = array();
    
    function writeAssetRefs(array $assets) {
        foreach ($assets as $asset) {
            $asset = strtr($asset, $this->assetPlaceholders);
            $asset = preg_replace('/#.*$/', '', $asset);
            if (strlen($asset)) {
                if (preg_match('/\.css$/', $asset)) $str = Ac_Util::mkElement('link', false, array('rel' => 'stylesheet', 'href' => $asset, 'type' => 'text/css'));
                else $str = Ac_Util::mkElement('script', '', array('type' => 'text/javascript', 'src' => $asset));
            }
?>
            <?php echo $str; ?> 
<?php
        }
    }
    
    function writeScripts(array $scripts) {
        if ($scripts) {
            $s = new Ac_Js_Script($scripts);
            echo $s;
        }
    }
    
    function writeWidget(Ac_Etl_Log_Stats_Html_Widget $widget) {
        $this->writeAssetRefs($widget->getAssetLibs());
        $this->writeScripts($widget->getPreJs());
?>
        <?php echo $widget->getHtml(); ?>
<?php   
        $this->writeScripts($widget->getPostJs());
    }
    
}
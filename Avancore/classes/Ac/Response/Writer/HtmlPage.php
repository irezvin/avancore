<?php

class Ac_Response_Writer_HtmlPage extends Ac_Response_Writer {

    /**
     * @var Ac_Response
     */
    protected $response = null;
    
    /**
     * @var Ac_I_Response_Environment
     */
    protected $environment = false;

    function setEnvironment(Ac_I_Response_Environment $environment) {
        $this->environment = $environment;
    }

    /**
     * @return Ac_I_Response_Environment
     */
    function getEnvironment() {
        return $this->environment? $this->environment : new Ac_Response_Environment_Native();
    }    
    
    protected function analyze() {
        
        $consolidated = $this->getConsolidated();
        
        $consolidated->mergeRegistry($this->response);
        
        $arr = $fullBlown->getConsolidated();
        
        return $arr;
        
    }
    
    protected function processConsolidatedArray(array $arrData) {
        
        $env = $this->getEnvironment();
        $env->begin();
        
        if (isset($arrData[$k = 'headers']) && is_array($arrData[$k])) $env->acceptHeaders($arrData[$k]);
        if (isset($arrData[$k = 'cookie']) && is_array($arrData[$k])) $env->acceptCookies($arrData[$k]);
        if (isset($arrData[$k = 'session']) && is_null($arrData[$k])) $env->destroySession;
        if (isset($arrData[$k = 'session']) && is_array($arrData[$k])) $env->acceptSessionData($arrData[$k]);
        
        $text = $this->composeResponseText($arrData);
        if (strlen($text)) $env->acceptResponseText ($text);
        
    }
    
    protected function composeResponseText($arrData) {
        extract($arrData);
        ob_start();
?>
<?php if (strlen($docType)) { ?>
<!DOCTYPE <?php echo $docType; ?>>
<?php } ?>
<html<?php echo Ac_Util::mkAttribs($rootTagAttribs); ?>>
    <head>
<?php if (strlen($title)) { ?>
        <title><?php echo $title; ?></title>
<?php } ?>
<?php if (strlen($metaKeywords)) { ?>
        <?php echo Ac_Util::mkElement('meta', false, array('name' => 'keywords', 'content' => $metaKeywords)); ?> 
<?php } ?>
<?php if (strlen($metaDescription)) { ?>
        <?php echo Ac_Util::mkElement('meta', false, array('name' => 'description', 'content' => $metaDescription)); ?> 
<?php } ?>
<?php if ($assetLibs) { ?>
        <?php $this->showAssetLibs($assetLibs, "\n        "); ?> 
<?php } ?>
<?php if ($headContent) echo implode("\n", $headContent)."\n"; ?>
    </head>
    <body<?php echo Ac_Util::mkAttribs($bodyTagAttribs); ?>><?php echo implode("\n", $content); ?></body>
</html>
<?php if ($debug && $this->showDebugInfo) { // TODO: pass debug info to Env? ?>
<!-- Debug:
                                                                               
<?php echo (implode("\n", $debug)); ?> 
                                                                               
--><?php } ?>
<?php        
        return ob_get_clean();
    }
    
}

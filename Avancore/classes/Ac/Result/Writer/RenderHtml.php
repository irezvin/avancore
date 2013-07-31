<?php

class Ac_Result_Writer_RenderHtml extends Ac_Result_Writer_AbstractHtmlRenderer {
    
    /**
     * @var Ac_I_Response_Environment
     */
    protected $environment = null;

    function setEnvironment(Ac_I_Response_Environment $environment = null) {
        $this->environment = $environment;
    }

    /**
     * @return Ac_I_Response_Environment
     */
    function getEnvironment() {
        return $this->environment;
    }        
    
    protected function implWriteNoCharset(Ac_Result $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        if (!$r instanceof Ac_Result_Html) throw Ac_E_InvalidCall::wrongClass('r', $r, 'Ac_Result_Html');
        if ($r->getContentType() === false) $r->setContentType('text/html');
        if ($t && ($env = $this->getEnvironment())) 
            throw new Ac_E_InvalidUsage("Both Environment and Target properties were set - cannot decide precedence");
        $useEnv = (!$t && ($env = $this->environment));
        if ($useEnv) {
            $env->begin();
            $env->acceptHeaders($r->getHeaders()->getItems());
        } elseif ($t && $t instanceof Ac_Result_Http_Abstract) {
            $t->getHeaders()->mergeWith($r->getHeaders());
        }
?>
<?php   $this->writePlaceholder('doctype'); ?>
<html<?php $this->writePlaceholder('htmlAttribs'); ?>>
<head><?php ?>
<?php $this->writePlaceholder('title', 'meta', 'headTags', 'assets', 'headScripts'); ?>
</head>
<body<?php $this->writePlaceholder('bodyAttribs'); ?>>
<?php   $r->echoContent(); ?>
<?php   $this->writePlaceholder('initScripts'); ?> 
</body>
</html>
<?php   $this->writePlaceholder('comments'); ?>
<?php
        if ($useEnv) $env->finishOutput();
    }
    
}
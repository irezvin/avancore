<?php

class Ac_Cg_Strategy {
    /**
     * @var Ac_Cg_Domain
     */ 
    var $_dom = false;
    
    /**
     * @var Ac_Cg_Generator
     */
    var $_gen = false;
    
    /**
     * @var string
     */
    var $outputDir = false;
    
    /**
     * Generate user-editable files?
     * 
     * @var bool
     */
    var $genEditable = true;
    
    /**
     * Overwrite user-editable files?
     * 
     * @var bool
     */
    var $ovrEditable = true;
    
    /**
     * Generate files that are not intended to be editable by user?
     * @var bool
     */
    var $genNonEditable = true;
    
    /**
     * @var array Names of templates that are common for whole domain
     */
    var $domainTemplates = array('Ac_Cg_Template_Domain');
    
    var $language = 'labels';
    
    var $languageData = false;
    
    /**
     * @param Ac_Cg_Generator $generator
     * @param string $domainName
     */
    function Ac_Cg_Strategy($generator, $domainName, $outputDir, $genEditable, $overwriteEditable, $extraOptions = array()) {
        Ac_Util::simpleBind($extraOptions, $this);
        $this->_gen = $generator;
        $this->_dom = $generator->getDomain($domainName);
        $this->outputDir = $outputDir;
        $this->genEditable = $genEditable;
        $this->ovrEditable = $overwriteEditable;
    }
    
    function generateCommonCode() {
        foreach ($this->listCommonTemplates() as $tplName) $this->processTemplate($tplName);
    }
    
    function generateCodeForModels($modelsList) {
        foreach ($modelsList as $name) {
            foreach ($this->listTemplatesForModel($name) as $tplName) {
                $this->processTemplate($tplName, $name);
            }
        }
    }
    
    function listCommonTemplates() {
        return $this->domainTemplates;
    }
    
    function listTemplatesForModel($name) {
        $mod = $this->_dom->getModel($name);
        $res = Ac_Util::toArray($mod->getTemplates());
        $res = array_unique($res);
        return $res;
    }
    
    /**
     * @param Ac_Cg_Model $model
     * @return string Name and path of pagemap file 
     */
    function getPagemapFileName($model) {
        $res = 'pagemap/'.$model->getModelBaseName().'.config.php';
        return $res;
    }
    
    /**
     * Instantiates a template (if $modelName is given, this must be model-wise template, and domain-wise in other case)
     * @return Ac_Cg_Template
     */
    function _createTemplate($templateName, $modelName = false) {
        $tpl = new $templateName;
        $tpl->generator = $this->_gen;
        $tpl->domain = $this->_dom;
        if (strlen($modelName)) {
            $mod = $this->_dom->getModel($modelName);
            $tpl->model = $mod;
        } else {
            $tpl->model = false;
        }
        $tpl->strategy = $this;
        $tpl->domain = $this->_dom;
        return $tpl;
    }
    
    /**
     * Processes a template (instantiates it if needed)
     */
    function processTemplate($templateName, $modelName = false) {
        $tpl = $this->_createTemplate($templateName, $modelName);
        foreach ($tpl->listFiles() as $n) {
            $skip = false;
            $p = $tpl->getFilePath($n, $this->outputDir);
            if ($tpl->fileIsUserEditable($n)) {
                
                if (!$this->genEditable) $skip = true;
                    else if (!$this->ovrEditable && is_file($p)) {
                        $this->_gen->log($p.": user-editable file is already in place; skipping");
                        $skip = true;
                    }
            } else {
            	if (!$this->genNonEditable) $skip = true;
            }
            if (!$skip) {
                $this->_gen->log($p.": writing file ");
                $tpl->outputFile($n, $this->outputDir);
                if ($this->_gen->lintify) $this->_gen->runLint($p);
            }
        }
    }
    
    function getLanguageString($strName, $default = '(Language string missing: ~)') {
        if (!is_array($this->languageData)) {
            $this->languageData = array();
            $ldf = 'languages/'.$this->language.'.php';
            if (is_file($ldf)) require($ldf);
            if (isset($language)) $this->languageData = $language;
            if (isset($addQuotes) && $addQuotes) {
                foreach ($this->languageData as $k => $v) {
                    $this->languageData[$k] = '\''.addcslashes($v, '\'').'\'';
                }
            }
        }
        if (isset($this->languageData[$strName])) {
            $res = $this->languageData[$strName];
        } else {
            $res = str_replace('~', $strName, $default);
        }
        return $res;
    }
    
}


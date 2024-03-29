<?php

/**
 * Simple frontend implementation for CodeGen.
 * 
 * To use it, insert following code into your codegen/index.php:
 * 
 * <code>
 *    require_once('../deploy.settings.php');
 *    ini_set('include_path', _DEPLOY_AVANCORE_PATH.PATH_SEPARATOR.'.');
 *    require_once('Cg/Frontend.php');
 *    $f = new Ac_Cg_Frontend();
 *    $f->processWebRequest();
 * </code>
 */
class Ac_Cg_Frontend {

    var $configPath = 'codegen.config.php';

    var $genDeployPath = false;
    
    /**
     * @var TRUE|FALSE|number TRUE = unlimited time; FALSE = don't set time limit; number = explicit time limit
     */
    var $maxExecutionTime = 60;
    
    var $showErrors = true;
    
    var $showOuterHtml = true;
    
    /**
     * @var Ac_Application
     */
    var $app = false;
    
    /**
     * @var Ac_Cg_Generator
     */
    var $generator = false;
    
    /**
     * @var bool
     */
    protected $init = false;
    
    function check() {
    }
    
    function init() {
        if (!$this->init) {
            $this->init = true;
                
            if (defined('_DEPLOY_AVANCORE_PATH') && (strpos(($ip = ini_get('include_path')), _DEPLOY_AVANCORE_PATH) === false)) {
                ini_set('include_path', $ip.PATH_SEPARATOR._DEPLOY_AVANCORE_PATH.PATH_SEPARATOR.'.');
            }
            
            if ($this->showErrors) {
                ini_set('error_reporting', E_ALL ^ E_STRICT);
                ini_set('display_errors', 1);
                ini_set('html_errors', 1);
            }
            
            /**
             * Model initialization code requires some stack
             */
            ini_set('xdebug.max_nesting_level', 512);
            
            if ($this->maxExecutionTime !== false) {
                ini_set('max_execution_time', $this->maxExecutionTime === true? 0 : $this->maxExecutionTime);
            }
        }
    }
    
    function getTitle() {
        $prefix = array();
        foreach ($this->generator->listDomains() as $dom) {
            $domain = $this->generator->getDomain($dom);
            $prefix[] = strlen($domain->caption)? $domain->caption : $domain->appName;
        }
        if ($prefix) $prefix = implode(", ", $prefix)." - ";
            else $prefix = '';
        $res = $prefix . "Avancore CodeGen v.".Ac_Avancore::version." &copy; 2008 &mdash; 2019 Ilya Rezvin";
        
        return $res;
    }
    
// ---------------------------------------------------------------------------------------------------------->8
    
    function showHtml($content) {
?>        
    <html>
    <head>
        <title><?php echo $this->getTitle(); ?></title>
        <meta http-equiv="content-type" content="text/html; charset=utf8" />
        <style type='text/css'>
            div {margin-left: 1em; padding-left: 1em; border-left: 1px dotted silver}
            form div {border: none}
            form .spanned {text-align: center}
            form input[type=submit] {font-weight: bold; padding: 0.5em 1em}
        </style>
    </head>
    <body>
<?php     echo $content; ?>
	</body>
	</html>
<?php 	    	
    }
    
    function processWebRequest() {
        
        if ($this->showOuterHtml) ob_start();
        
        $this->check();
        $this->init();
            
        $gen = $this->generator = new Ac_Cg_Generator(['configPath' => $this->configPath]);
        
        $gen->prepare();
    
        $form = new Ac_Form(array(
            'htmlAttribs' => array(
                'style' => 'float: left; padding: 1em; border: 1px solid silver; margin: 0.5em; background-color: lightyellow',
            ),
            'submissionControl' => 'submitForm',
            'controls' => array(
                'cleanOutputDir' => array(
                    'class' => 'Ac_Form_Control_Toggle',
                    'caption' => 'Clean output dir',
                ),
                'createEditableFiles' => array(
                    'class' => 'Ac_Form_Control_Toggle',
                    'caption' => 'Create editable files',
                ),
                'createNonEditableFiles' => array(
                    'class' => 'Ac_Form_Control_Toggle',
                    'caption' => 'Create non-editable files',
                ),
                'copyEditable' => array(
                    'class' => 'Ac_Form_Control_Toggle',
                    'caption' => 'Deploy editable files after generation',
                ),
                'copyGen' => array(
                    'class' => 'Ac_Form_Control_Toggle',
                    'caption' => 'Deploy non-editable files after generation',
                ),
                'submitForm' => array(
                    'class' => 'Ac_Form_Control_Button', 'buttonType' => 'submit',
                    'caption' => 'Generate',
                    'wrapperTemplateClass' => 'Ac_Form_Control_Template_Basic',
                    'wrapperTemplatePart' => 'trWrapperColspan',
                ),
                'reload' => array(
                    'class' => 'Ac_Form_Control_Button', 'buttonType' => 'button',
                    'caption' => 'Reload page',
                    'htmlAttribs' => array(
                        'onclick' => 'this.form.method="get";'
                    ),
                    'wrapperTemplateClass' => 'Ac_Form_Control_Template_Basic',
                    'wrapperTemplatePart' => 'trWrapperColspan',
                ),
            ),
        ));
        
        $value = $form->getValue();
        
        $cleanOutputDir = (bool) $value['cleanOutputDir'];
        $createEditableFiles = (bool) $value['createEditableFiles'];
        $createNonEditableFiles = (bool) $value['createNonEditableFiles'];
        $copyGen = (bool) $value['copyGen'];
        $copyEditable = (bool) $value['copyEditable'];
        
        $gen->clearOutputDir =  $cleanOutputDir;
        $gen->genEditable = $createEditableFiles;
        $gen->genNonEditable = $createNonEditableFiles;
        $gen->deployEditable = $copyEditable;
        $gen->deployNonEditable = $copyGen;
        $gen->deployPath = $this->getGenDeployPath($gen);

?>
    <p><i><?php echo $this->getTitle(); ?></i></p>
    
    <?php echo $form->fetchPresentation(); ?>
    
    <br style='clear: both' />

<?php

    //$this->show($gen);

    foreach ($gen->listDomains() as $domName) {
    
        $dom = $gen->getDomain($domName);
        $this->showExpandable("<h2>".$domName."</h2>", false);
    
        foreach ($dom->listModels() as $modelName) {
            $model = $dom->getModel($modelName);
            $this->showExpandable("<h3>".$modelName."</h3>", false);
            $this->show($model);
?>
<?php       if ($ps = $model->listProperties()) { ?>
        		<ul>
<?php           foreach ($ps as $p) { $prop = $model->getProperty($p); ?>
<?php               $cmn = '$'.$prop->getClassMemberName(); if ($prop->pluralForList) $cmn .= '[]'; ?>
              		<li>
<?php                     $this->showExpandable($cmn, false); ?>
<?php                     var_dump(get_class($prop)); ?>
<?php                     $this->show($prop); ?>
<?php                     echo '<p>Property info</p>'; ?>
<?php                     var_dump($prop->getAeModelPropertyInfo()); ?>
<?php                     if ($prop instanceof Ac_Cg_Property_Object && ($relId = $prop->modelRelation) && ($rel = $model->getAeModelRelationPrototype($relId))) { ?>
<?php                         echo '<p>Relation prototype</p>' ?>
<?php                         var_dump($rel); ?>
<?php                     } ?>                        
<?php                     if ($prop instanceof Ac_Cg_Property_Object && ($assoc = $prop->getAssociationPrototype())) { ?>
<?php                         echo '<p>Association prototype</p>' ?>
<?php                         var_dump($assoc); ?>
<?php                     } ?>                        
                      	</div>
              		</li>
<?php           } ?> 
        		</ul>
<?php       } else { ?>
            	<p>No properties</p>
<?php       } ?>
	    	</div>
<?php     } // foreach listModels... ?>

		</div>      

<?php   } // foreach listDomains... ?>

<?php   if ($form->isSubmitted()) { ?>
	  	<pre>
<?php         $gen->run(); ?>
	  	</pre>
      	<h3>Generator run complete: <?php echo $gen->getOutputBytes(); ?> bytes in <?php echo $gen->getOutputFiles(); ?> files</h3>

<?php 

    } 
?>
<?php if (function_exists('xdebug_time_index')) var_dump(xdebug_time_index()); ?>
<?php

        if ($this->showOuterHtml) $this->showHtml(ob_get_clean());
    
    }
// ---------------------------------------------------------------------------------------------------------->8    

    function show($object, $buf = false, $showDefaults = false) {
        $r = array();
        if (!$showDefaults) $cv = get_class_vars(get_class($object));
        $vars = get_object_vars($object);
        if (method_exists($object, 'onShow')) {
            $extra = $object->onShow();
        } else {
            $extra = array();
        }
        Ac_Util::ms($vars, $extra);
        foreach ($vars as $k => $v) {
            if ($k == 'password') $v = str_repeat('*', strlen($v));
            if (array_key_exists($k, $extra) || ($k[0] != '_' && !is_object($v) && !is_array($v))) {
                if (!$showDefaults && isset($cv[$k]) && $cv[$k] === $v) continue; 
                if ($v === false) $v = '<i>false</i>';
                elseif ($v === true) $v = '<i>true</i>';
                elseif (is_array($v)) $v = $v? '<pre>'.htmlspecialchars(print_r($v, 1)).'</pre>' : "Array()";
                elseif (is_object($v)) $v = Ac_Util::typeClass ($v);
                else $v = "'".htmlspecialchars($v)."'";
                $r[] = "<li> <strong>$k</strong>: $v</li>";
            }
        }
        if ($r) $res = "<ul>".implode(" ", $r)."</ul>"; else $res = "";
        if ($buf) return $res; else echo $res; 
    }
    
    function showExpandable($head, $text, $class='xp') {
        static $id = 0;
        $id++;
        echo "<div class='{$class}Head'><a style='text-decoration: none' href='#' onclick=\"var foo=document.getElementById('xp$id'); if (foo) foo.style.display = foo.style.display == 'none'? '' : 'none'; return false;\">$head</a></div>";
        if ($text !== false) $end = $text.'</div>'; else $end = '';
        echo "<div id='xp$id' class='{$class}Text' style='display: none'>$end";     
    }
    
    function getExpandable($head, $text, $class='xp') {
        ob_start();
        $this->showExpandable($head, $text, $class);
        return ob_get_clean();
    }

    function getGenDeployPath(Ac_Cg_Generator $gen) {
        // TODO: remove this insane contraption and use Ac_Cg_Generator settings instead
        $ds = DIRECTORY_SEPARATOR;
        if ($this->genDeployPath !== false) {
            $res = $this->genDeployPath;
        } else {
            if (defined('_DEPLOY_GEN_PATH')) $res = dirname(_DEPLOY_GEN_PATH);
            if (is_dir($d = "..{$ds}".$gen->genDir)) {
                $res = $d;
            } else {
                $res = "..{$ds}..{$ds}".$gen->genDir;
            }
        }
        return $res;
    }
    
}

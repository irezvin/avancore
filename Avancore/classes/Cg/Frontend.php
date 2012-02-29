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
 *    $f = new Cg_Frontend();
 *    $f->processWebRequest();
 * </code>
 */
class Cg_Frontend {

    var $configPath = 'codegen.config.php';

    var $genDeployPath = false;
    
    /**
     * @var TRUE|FALSE|number TRUE = unlimited time; FALSE = don't set time limit; number = explicit time limit
     */
    var $maxExecutionTime = 60;
    
    var $showErrors = true;
    
    var $showOuterHtml = true;
    
    /**
     * @var Ae_Application
     */
    var $application = false;
    
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
                ini_set('error_reporting', E_ALL);
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
        $res = "Avancore CodeGen v.0.0.3 &copy; 2008 &mdash; 2011 Ilya Rezvin";
        return $res;
    }
    
// ---------------------------------------------------------------------------------------------------------->8
    
    function showHtml($content) {
?>        
    <html>
    <head>
        <title><?php echo $this->getTitle(); ?></title>
        <style type='text/css'>
            div {margin-left: 1em; padding-left: 1em; border-left: 1px dotted silver}
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
            
        Ae_Dispatcher::loadClass('Cg_Generator');
    
        $gen = new Cg_Generator($this->configPath);
    
        Ae_Dispatcher::loadClass('Ae_Form_Helper');
    
        $hlp = new Ae_Form_Helper($dummy = null, false);
        $hlp->alwaysWrap = false;
    
        $cleanOutputDir = isset($_REQUEST['cod']);
        $createEditableFiles = isset($_REQUEST['cef']);
        $createNonEditableFiles = isset($_REQUEST['cnf']);
        $copyGen = isset($_REQUEST['cpg']);
        
        $gen->clearOutputDir =  $cleanOutputDir;
        $gen->genEditable = $createEditableFiles;
        $gen->genNonEditable = $createNonEditableFiles;

?>
    <p><i><?php echo $this->getTitle(); ?></i></p>
    
    <form method='post' style='float: left; padding: 1em; border: 1px solid silver; margin: 0.5em; background-color: lightyellow'>
        <?php $hlp->showCheckbox('cod', $cleanOutputDir, false, array(), true); ?> Clear output dir <br />
        <?php $hlp->showCheckbox('cef', $createEditableFiles, false, array(), true); ?> Create editable files <br /> 
        <?php $hlp->showCheckbox('cnf', $createNonEditableFiles, false, array(), true); ?> Create non-editable files <br /> 
        <?php $hlp->showCheckbox('cpg', $copyGen, false, array(), true); ?> Deploy non-editable files after generation <br />
        <p style='text-align: center'> 
        	<input type='submit' name='submit' style='height: 30px; width: 100px; font-weight: bold' value='Run!' />
       	</p>
    </form>
    <br style='clear: both' />

<?php

    $this->show($gen);

    foreach ($gen->listDomains() as $domName) {
    
        $dom = & $gen->getDomain($domName);
        $this->showExpandable("<h2>".$domName."</h2>", false);
    
        foreach ($dom->listModels() as $modelName) {
            $model = & $dom->getModel($modelName);
            $this->showExpandable("<h3>".$modelName."</h3>", false);
            $this->show($model);
            //var_dump($dom->analyzeTableName($model->table));
?>
<?php       if ($ps = $model->listProperties()) { ?>
        		<ul>
<?php           foreach ($ps as $p) { $prop = & $model->getProperty($p); ?>
<?php               // if (is_a($prop, 'Cg_Property_Object')) var_dump($prop->getOtherEntityName(), $prop->getOtherEntityName(false)); ?>
<?php               $cmn = '$'.$prop->getClassMemberName(); if ($prop->pluralForList) $cmn .= '[]'; ?>
              		<li>
<?php                     $this->showExpandable($cmn, false); ?>
<?php                     var_dump(get_class($prop)); ?>
<?php                     $this->show($prop); ?>
<?php                     var_dump($prop->getAeModelPropertyInfo()); ?>
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

<?php   if (isset($_POST['submit'])) { ?>
	  	<pre>
<?php         $gen->run(); ?>
	  	</pre>
      	<h3>Generator run complete: <?php echo $gen->getOutputBytes(); ?> bytes in <?php echo $gen->getOutputFiles(); ?> files</h3>

<?php   if ($copyGen && $createNonEditableFiles) { ?>
<?php       $ds = DIRECTORY_SEPARATOR; ?>
<?php 	    list($cmd, $output, $res) = Cg_Util::copyDirRecursive("output{$ds}gen", $this->getGenDeployPath(), true, true); ?>
<?php 	    echo '<pre>'.htmlspecialchars($cmd)."</pre><p>{$res}</p><pre>".$output.'</pre>'; ?>
<?php   } ?>

<?php 

    } 
?>
<?php if (function_exists('xdebug_time_index')) var_dump(xdebug_time_index()); ?>
<?php

        if ($this->showOuterHtml) $this->showHtml(ob_get_clean());
    
    }
// ---------------------------------------------------------------------------------------------------------->8    

    function show(& $object, $buf = false, $showDefaults = false) {
        $r = array();
        if (!$showDefaults) $cv = get_class_vars(get_class($object));
        foreach (get_object_vars($object) as $k => $v) {
            if ($k == 'password') $v = str_repeat('*', strlen($v));
            if ($k{0} != '_' && !is_object($v) && !is_array($v)) {
                if (!$showDefaults && isset($cv[$k]) && $cv[$k] === $v) continue; 
                if ($v === false) $v = '<i>false</i>';
                elseif ($v === true) $v = '<i>true</i>';
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

    function getGenDeployPath() {
        if ($this->genDeployPath !== false) {
            $res = $this->genDeployPath;
        } else {
            if (defined('_DEPLOY_GEN_PATH')) $res = dirname(_DEPLOY_GEN_PATH);
            else {
                $ds = DIRECTORY_SEPARATOR;
                $res = "..{$ds}..{$ds}gen";
            }
        }
        return $res;
    }
    
}
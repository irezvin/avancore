<html>
<head>
    <title>Php Codegen v.0.0.2 / based on domain metamodel &copy; 2008 Ilya Rezvin</title>
    <style type='text/css'>
        div {margin-left: 1em; padding-left: 1em; border-left: 1px dotted silver}
    </style>
</head>
<body>
<?php
/**
 * @package
 * @copyright    (c) 2008 Ilya Rezvin
 * @author         Ilya Rezvin <ilya@rezvin.com>
 * @version        $Id$
 */

ini_set('xdebug.max_nesting_level', 1000);
ini_set('include_path', dirname(__FILE__).'/../../classes'.PATH_SEPARATOR.'.');
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('html_errors', 1);
require('Ae/Dispatcher.php');

Ae_Dispatcher::loadClass('Cg_Generator');

$gen = new Cg_Generator('codegen.config.php');

Ae_Dispatcher::loadClass('Ae_Form_Helper');

$hlp = new Ae_Form_Helper($dummy = null, false);
$hlp->alwaysWrap = false;

$cleanOutputDir = isset($_REQUEST['cod']);
$createEditableFiles = isset($_REQUEST['cef']);

$gen->clearOutputDir =  $cleanOutputDir;
$gen->genEditable = $createEditableFiles;

?>
    <p>Php Codegen v.0.0.2 / based on domain metamodel &copy; 2008 Ilya Rezvin</p>
    
    <form method='post'>
        <?php $hlp->showCheckbox('cod', $cleanOutputDir, false, array(), true); ?> Clear output dir <br />
        <?php $hlp->showCheckbox('cef', $createEditableFiles, false, array(), true); ?> Create editable files <br /> 
        <input type='submit' name='submit' value='Run!' /><br />
    </form>

<?php

show($gen);

foreach ($gen->listDomains() as $domName) {
    
    $dom = & $gen->getDomain($domName);
    showExpandable("<h2>".$domName."</h2>", false);

    foreach ($dom->listModels() as $modelName) {
        $model = & $dom->getModel($modelName);
        showExpandable("<h3>".$modelName."</h3>", false);
        show($model);
        //var_dump($dom->analyzeTableName($model->table));
    ?>
        <?php if ($ps = $model->listProperties()) { ?>
        <ul>
            <?php foreach ($ps as $p) { $prop = & $model->getProperty($p); ?>
                <?php // if (is_a($prop, 'Cg_Property_Object')) var_dump($prop->getOtherEntityName(), $prop->getOtherEntityName(false)); ?>
                <?php $cmn = '$'.$prop->getClassMemberName(); if ($prop->pluralForList) $cmn .= '[]'; ?>
                <li>
                    <?php showExpandable($cmn, false); ?>
                        <?php var_dump(get_class($prop)); ?>
                        <?php show($prop); ?>
                        <?php var_dump($prop->getAeModelPropertyInfo()); ?>
                    <?php echo "</div>"; ?>
                </li>
            <?php } ?> 
        </ul>
        <?php } else { ?>
            <p>No properties</p>
        <?php } ?>
        </div>
    <?php } // foreach listModels...?>

</div>      
<?php } // foreach listDomains... ?>


<?php if (isset($_POST['submit'])) { ?>

<pre>
<?php $gen->run(); ?>
</pre>
<h3>Generator run complete: <?php echo $gen->getOutputBytes(); ?> bytes in <?php echo $gen->getOutputFiles(); ?> files</h3>

<?php } ?>

<?php if (function_exists('xdebug_time_index')) var_dump(xdebug_time_index()); ?>

<?php ##################################################################################################### ?>

<?php



function show(& $object, $buf = false, $showDefaults = false) {
    $r = array();
    if (!$showDefaults) $cv = get_class_vars(get_class($object));
    foreach (get_object_vars($object) as $k => $v) {
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
    showExpandable($head, $text, $class);
    return ob_get_clean();
}

?>
</body>
</html>

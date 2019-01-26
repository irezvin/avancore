<?php

require('../tests/testsStartup.php');
require_once('simpletest/reporter.php');
require_once(dirname(__FILE__).'/classes/Ac/Test/Base.php');
require_once(dirname(__FILE__).'/app.config.php');
define('AC_TESTS_TMP_PATH', dirname(__FILE__).'/var');
Ac_Test_Base::$config = $config;

$classes = array();
$allTests = true;

$ac = true;
$etl = false;
if (isset($_GET['etl'])) $etl = (bool)($_GET['etl']);
if (isset($_GET['ac'])) $ac = (bool) ($_GET['ac']);

$v = Ac_Avancore::version;

$titlePrefix = "";
if (isset($_GET['class']) && strlen($class = $_GET['class'])) {
    $classes = explode(',', $_GET['class']);
    $allTests = false;
    $titlePrefix = str_replace("Ac_Test_", "", implode(", ", $classes))." - ";
} else {
    $files = array();
    $base = __DIR__.'/classes/';
    if ($ac) $files = array_merge($files, glob($base.'Ac/Test/*.php'));
    if ($etl) $files = array_merge($files, glob($base.'Etl/Test/*.php'));
    foreach ($files as $file) {
        $class = substr($file, strlen($base));
        $class = str_replace('/', '_', $class);
        $class = str_replace('.php', '', $class);
        if ($class !== 'Ac_Test_Base' && $class !== 'Ac_Test_Reporter') $classes[] = $class;
    }
}
	
$time = microtime(true);

?>
<!DOCTYPE html>
<html>
    <title><?php echo $titlePrefix; ?>Avancore <?php echo Ac_Avancore::version; ?> tests</title>
    <head>
        <style type="text/css">
            .fail { background-color: inherit; color: red; }
            .pass { background-color: inherit; color: green; }
            pre { background-color: lightgray; color: inherit; }
            h1 a, .fa {
                font-size: 0.5em;
                font-weight: normal;
                display: block;
                float: right;
                text-shadow: none;
            }
            .xdebug-error td, .xdebug-error th {
                color: black;
            }
            h1, h2, h3 {
                font-weight: normal;
                text-shadow: 2px 2px black;
            }
            html {
                font-family: sans;
                height: 100%;
                background-color: #333;
                background: linear-gradient(to bottom, #424242 0%,#9b9b9b 100%); /* W3C */
                color: #eee;
                background-attachment: fixed;
            }
            pre {
                color: black;
            }
            span.fail {
                background-color: red; color: white; padding: 1px 4px; border-radius: 5px; font-weight: bold;
            }
            body {
                line-height: 1.8em;
            }
            div, pre {
                border-radius: 5px;
                padding: 5px;
                line-height: 1em;
            }
        </style>
    </head>
    <body>
<?php

if (!$allTests) {
    echo "<p><a href='?'>Back to all tests</a></p>";
}

foreach ($classes as $class) {

    if (strpos($class, '_Test_') === false) {
        $class = 'Ac_Test_'.ucfirst($class); 
    }
    $head = "{$class} <a href='?class={$class}'>rerun</a>";
    $t = new TestSuite($head);
    $t->add($class);

    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ob_start();
    $status = $t->run(new Ac_Test_Reporter('UTF-8'));
    $content = ob_get_clean();
    $inContent = trim(preg_replace("#<!-- s -->.*<!-- /s -->#sUu", '', $content));
    if (!$status || strlen($inContent) || count($classes) == 1) {
        echo $content;
        var_dump(round(microtime(true) - $time, 3));
        $time = microtime(true);
    }

}
var_dump(round(microtime(true) - $time, 3));
$time = microtime(true);

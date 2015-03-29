<?php

if (php_sapi_name() !== 'cli') throw new Exception("CLI usage only");
/*

    Usage:
    
    stdin:
            onBeforeFoo
            afterBar ($arg1, $arg2)
           
    stdout:
    
        |**
        |* @function onBeforeFoo()
        |*
        const EVENT_ON_BEFORE_FOO = 'onBeforeFoo';
        
        
        |**
        |* @function afterBar($arg1, $arg2)
        |*
        const EVENT_AFTER_BAR = 'afterBar';

*/

$in = file('php://stdin');
foreach ($in as $item) {
    $item = trim($item);
    if (!strlen($item) || $item{0} === '#') continue;
    $itemArgs = preg_split("/\s+/", $item, 2);
    $eventName = $itemArgs[0];
    if (count($itemArgs) > 1) $args = $itemArgs[1];
        else $args = false;
    $const = strtoupper("EVENT_".preg_replace("/([^A-Z])([A-Z])/", "\\1_\\2", $eventName));
    if (!strlen($args)) $args = '()';
?>

    /**
     * function <?php echo $eventName; ?><?php echo $args; ?>

     */
    const <?php echo $const; ?> = '<?php echo $eventName; ?>';
<?php
}


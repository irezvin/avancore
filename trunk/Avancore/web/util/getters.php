<?php
    if (isset($_REQUEST['submit'])) $foc = 'res'; else $foc = 'getters';
?>
<html>
    <head>
        <title>Getter Setter Maker</title>
    </head>
    <body onload="document.gform.<?php echo $foc; ?>.focus();">
        <form method='post' name='gform'>
            <label for='getters'>Pro<u>p</u>s list:</label>
            <table cols='2'>
                <tr><td valign='top'>
                        <textarea name='getters' cols='30' rows='10' accesskey="p" onfocus="this.select();"><?php if (isset($_REQUEST['getters'])) echo $_REQUEST['getters']; ?></textarea>
                    </td>
                    <td valign='top'>
                        <p>Format: propName [: className] [p][s|g][c[f]][l[x]]</p>
                        <p>"p" = protected setter | "s" = create setter only | "g" = create getter only | "c" = track change in setter | "l" = lazy getter</p>
                        <p>"f" = add $force param to track-change setter | "x" = load value from $this->_context in lazy getter</p>
                        <p><button name='submit' accesskey="a">Gener<u>a</u>te!</button><p>
                    </td>
                </tr>
            </table>
<?php   if (isset($_REQUEST['submit'])) { $vars = array(); $sg = array();
    
    foreach (explode("\n", $_REQUEST['getters']) as $line) {    
        if (preg_match ('#(\w+)(\s*:\s*\w+)?(\s*\w+)?#', trim($line), $matches)) {
            $varName = $matches[1];
            $setterName = "set".ucfirst($varName);
            $getterName = "get".ucfirst($varName);
            $isProtectedSetter = isset($matches[3]) && strpos($matches[3], 'p') !== false;
            $isSetterOnly = isset($matches[3]) && strpos($matches[3], 's') !== false;
            $isGetterOnly = isset($matches[3]) && strpos($matches[3], 'g') !== false;
            $trackChange = isset($matches[3]) && strpos($matches[3], 'c') !== false;
            $lazyGetter = isset($matches[3]) && strpos($matches[3], 'l') !== false;
            $useThisContext = isset($matches[3]) && strpos($matches[3], 'x') !== false;
            $force = isset($matches[3]) && strpos($matches[3], 'f') !== false;
            
            $type = false;
            if (isset($matches[2])) {
                $type = trim($matches[2], ': ');
            }
            
            $defaultValue = 'false';
            
            if ($type) {
                $tComm = "    /**\n     * @var {$type}\n     */\n";
                if (in_array($type, array('bool', 'boolean')) && $lazyGetter) {
                    $defaultValue = 'null';
                }
                if (!isSimpleType($type)) {
                    $sType = "{$type} ";
                    $stComm = "";
                } else {
                    $sType = "";
                    $stComm = "/**\n     * @param {$type} \${$varName}\n     */\n    ";
                }
                $gComm = "    /**\n     * @return {$type}\n     */\n";
            } else {
                $tComm = "";
                $sType = "";
                $gComm = "";
                $stComm = "";
            }
            
            $vars[] = "{$tComm}    protected \${$varName} = $defaultValue;";
            
            if ($force) {
                $forceParam = ', $force = false';
                $forceInset = ' || $force';
            } else {
                $forceParam = '';
                $forceInset = '';
            }
            
            if (!$isGetterOnly) {
                if (!$trackChange) {
                    $sg[] = "    {$stComm}".($isProtectedSetter? "protected " : "")."function {$setterName}({$sType}\${$varName}) {
        \$this->{$varName} = \${$varName};
    }";
                } else {
                    $uVarName = ucFirst($varName);
                    $sg[] = "    {$stComm}".($isProtectedSetter? "protected " : "")."function {$setterName}({$sType}\${$varName}{$forceParam}) {
        if (\${$varName} !== (\$old{$uVarName} = \$this->{$varName}){$forceInset}) {
            \$this->{$varName} = \${$varName};
        }
    }";
                }
            }
            if (!$isSetterOnly) {
                if ($lazyGetter) {
                    if ($useThisContext) $inset = "
            \$this->{$varName} = \$this->_context->getData('{$varName}');";
                    else $inset = "";
                
                    $sg[] = "{$gComm}    function {$getterName}() {
        if (\$this->{$varName} === $defaultValue) {"."$inset
        }
        return \$this->{$varName};
    }";
                } else {
                    $sg[] = "{$gComm}    function {$getterName}() {
        return \$this->{$varName};
    }";         
                }
            }
        }
    }
?>
<label for="res"><u>r</u>esult:</label><br />
<textarea cols="80" rows="30" name="res" accesskey="r" onfocus="this.select();">
<?php
    echo implode("\n\n", $vars)."\n\n".implode("\n\n", $sg);
?>
</textarea>
<?php   } ?>
</form>
</html>
            
<?php


    function isSimpleType($foo) {
        return in_array(trim($foo), array('bool', 'boolean', 'string',  'double', 'float', 'int', 'integer'));
    }

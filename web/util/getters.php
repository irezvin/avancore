<?php
    if (isset($_REQUEST['submit'])) $foc = 'res'; else $foc = 'getters';
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
?>
<html>
    <head>
        <title>Getter Setter Maker</title>
    </head>
    <body onload="document.gform.<?php echo $foc; ?>.focus();">
        <form method='post' name='gform'>
            <label for='getters'>Prop<u>s</u> list:</label>
            <table cols='2'>
                <tr><td valign='top'>
                        <textarea name='getters' cols='80' rows='10' accesskey="s" onfocus="this.select();"><?php if (isset($_REQUEST['getters'])) echo $_REQUEST['getters']; ?></textarea>
                    </td>
                    <td valign='top' colspan="2">
                        <p>Format: propName [: className] [p][s|g][c[f]][l[x]] [//comment[\nNew line]]</p>
                        <ul>
                            <li>"p" = protected setter</li>
                            <li>"s" = create setter only</li>
                            <li>"g" = create getter only</li>
                            <li>"c" = track change in setter</li>
                            <li>"l" = lazy getter</li>
                            <li>"f" = add $force param to track-change setter</li>
                            <li>"x" = load value from $this->_context in lazy getter</li>
                        </ul>
                        <p><button name='submit' accesskey="a">Gener<u>a</u>te!</button><p>
                    </td>
                </tr>
<?php   if (isset($_REQUEST['submit'])) { $vars = array(); $sg = array();
    
    foreach (explode("\n", $_REQUEST['getters']) as $line) {    
        if (preg_match ('#(\w+)(\s*:\s*[\\\\\w]+)?(\s*\w+)?(\s*//.*+)?#', trim($line), $matches)) {
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
            $comment = isset($matches[4])? ltrim(trim($matches[4]), '/') : '';
            
            $type = false;
            if (isset($matches[2])) {
                $type = trim($matches[2], ': ');
            }
            
            $defaultValue = 'false';
            
            $tComm = [];
            $stComm = [];
            $gComm = [];
            
            if (strlen($comment)) {
                $comment = explode('\n', $comment);
                
                $stComm = array_merge($comment, $stComm);
                $stComm[0] = "Sets ".$stComm[0];
                
                $gComm = array_merge($comment, $gComm);
                $gComm[0] = "Returns ".$gComm[0];
                
                $tComm = array_merge($comment, $tComm);
            }
            
            if ($type) {
                $tComm[] = "@var {$type}";
                if (in_array($type, array('bool', 'boolean')) && $lazyGetter) {
                    $defaultValue = 'null';
                }
                if (!isSimpleType($type)) {
                    $sType = "{$type} ";
                } else {
                    $sType = "";
                    $stComm[] = "@param {$type} \${$varName}";
                }
                $gComm[] = "@return {$type}";
            } else {
                $sType = "";
            }
            
            $vars[] = getComment($tComm)."    protected \${$varName} = $defaultValue;";
            
            if ($force) {
                $forceParam = ', $force = false';
                $forceInset = ' || $force';
            } else {
                $forceParam = '';
                $forceInset = '';
            }
            
            if (!$isGetterOnly) {
                if (!$trackChange) {
                    $sg[] = getComment($stComm)."    ".($isProtectedSetter? "protected " : "")."function {$setterName}({$sType}\${$varName}) {
        \$this->{$varName} = \${$varName};
    }";
                } else {
                    $uVarName = ucFirst($varName);
                    $sg[] = getComment($stComm)."    ".($isProtectedSetter? "protected " : "")."function {$setterName}({$sType}\${$varName}{$forceParam}) {
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
                
                    $sg[] = getComment($gComm)."    function {$getterName}() {
        if (\$this->{$varName} === $defaultValue) {"."$inset
        }
        return \$this->{$varName};
    }";
                } else {
                    $sg[] = getComment($gComm)."    function {$getterName}() {
        return \$this->{$varName};
    }";         
                }
            }
        }
    }
?>
    <tr>
        <td>
            <label for="res"><u>r</u>esult:</label><br />
            <textarea cols="80" rows="30" name="res" accesskey="r" onfocus="this.select();"><?php
                echo implode("\n\n", $vars)."\n\n".implode("\n\n", $sg);
            ?></textarea>
        </td>
    </tr>
<?php   } ?>
    </table>
</form>
</html>
            
<?php


    function isSimpleType($foo) {
        return in_array(trim($foo), array('bool', 'boolean', 'string',  'double', 'float', 'int', 'integer'));
    }
    
    function getComment(array $comm) {
        if (count($comm)) {
            foreach ($comm as & $l)  $l = "\n     * {$l}";
            $res = "    /**".implode("", $comm)."\n     */\n";
        } else {
            $res = "";
        }
        return $res;
    }

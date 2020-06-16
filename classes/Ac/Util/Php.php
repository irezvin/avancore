<?php

abstract class Ac_Util_Php {
    
    /**
     * Shows php open tag
     */
    static function phpOpen() {
        echo "<"."?php";
    }
    
    /**
     * Shows php closing tag
     */
    static function phpClose() {
        echo "?".">";
    }
    
    protected static $dontFixIndent = 0;
    
    static function export($foo, $return = false, $indent = 0, $beginArrayWithIndent = false) {
        if (is_array($foo)) {
            $res = "";
            if ($beginArrayWithIndent) $res .= str_repeat(" ", $indent);
            $res .= self::exportArray($foo, $indent, true, null, true);
        }
        elseif ($foo === 0) $res = '0';
        else $res = self::myVarExport($foo, true, $indent);
        
        if ($return) return $res; 
            else echo $res;
    }
    
    protected static function myVarExport($array, $return = false, $indent = 0, $inner = false)
    {
        // Common output variables
        $strIndent      = str_repeat(" ", $indent);
        $strInnerIndent = str_repeat(" ", $indent + 4);
        $doublearrow = ' => ';
        $lineend     = ",\n";
        $stringdelim = '\'';

        // Check the export isn't a simple string / int
        if (is_object($array) && $array instanceof Ac_I_PhpExpression) {
            $out = $array->getExpression();
        } elseif (is_string($array)) {
            if (strpos($array, "\n")) {
                $out = '"'.addcslashes($array, "\\\"\n").'"';
            } else {
                $out = $stringdelim . addcslashes($array, "\\'") . $stringdelim;
            }
        } elseif (is_int($array) || is_float($array)) {
            $out = (string)$array;
        } elseif (is_bool($array)) {
            $out = $array ? 'true' : 'false';
        } elseif (is_null($array)) {
            $out = 'NULL';
        } elseif (is_resource($array)) {
            $out = 'resource';
        } elseif (!count($array)) {
            $out = '[]';
        } else {
            // Begin the array export
            // Start the string
            
            $out = "";
            
            //if (!$inner) $out .= $strIndent;
            $out .= "[\n";

            // Loop through each value in array
            foreach ($array as $key => $value) {

                if (is_object($value) && $value instanceof Ac_I_PhpExpression_Extended) {
         
                    $out .= "\n".$value->export($key, $indent + 4).$lineend;
                        
                } else {
                    
                    $val = self::myVarExport($value, true, $indent + 4, true);

                    // Piece together the line
                    $out .= $strInnerIndent;

                    // If the key is a string, delimit it
                    if (!is_numeric($key)) {
                        $key = addcslashes($key, "\\'");
                        $key = $stringdelim . $key . $stringdelim;
                    }

                    $out .= $key . $doublearrow . $val . $lineend;
                }
            }

            // End our string
            $out .= $strIndent;
            $out .= "]";
        }

        // Decide method of output
        if ($return) {
            return $out;
        } else {
            echo $out;
            return;
        }
    }
    
    
    /**
     * Returns code for initializing given PHP array
     */
    static function exportArray($foo, $indent = 0, $withNumericKeys = false, $oneLine = null, $return = false) {
        if (is_null($oneLine)) {
            $oneLine = true;
            foreach ($foo as $key => $item) {
                if (!is_numeric($key) || !is_scalar($item) && !is_null($item)) {
                    $oneLine = false;
                    break;
                }
            }
        }
        $vx = self::myVarExport($foo, 1, $indent);
        $vx = preg_replace("/=> \n([ ]+)\[ \\(/", "=> [ (\\1", $vx);
        //$vx = preg_replace_callback("/\n([ ]*)/", array('Ac_Util_Php', 'replaceIndent'), $vx);
        if (!$withNumericKeys) $vx = preg_replace ("/(\n[ ]+)\\d+=>/", "\\1", $vx);
        if ($oneLine) {
            $vx = preg_replace("/\n[ ]*/", " ", $vx);
        }
        if (!$return) echo $vx; 
            else return $vx;
            
    }
    
    /**
     * Returns escaped and quoted PHP string
     */
    static function str ($string, $return = false) {
        $res = "'".addcslashes($string, "'")."'";
        if ($return) return $res; 
            else echo $res;
    }
    
    /**
     * Returns twice-escaped-and-quoted string (useful for strings in generated PHP code that runs SQL operators) 
     */
    static function str2 ($string, $return = false) {
        $res = addcslashes("'".addcslashes($string, "'")."'", "'");
        if ($return) return $res; 
            else echo $res;
    }
    
}
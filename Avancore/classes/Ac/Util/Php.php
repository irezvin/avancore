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
    
    protected static function replaceIndent($match) {
        return "\n".str_repeat(" ", strlen($match[0])/2*4);
    }
    
    static function export($foo, $return = false, $indent = 0) {
        if (is_array($foo)) $res = self::exportArray($foo, $indent, true, true, true);
        elseif ($foo === 0) $res = '0';
        else $res = self::myVarExport($foo, true);
        
        if ($return) return $res; 
            else echo $res;
    }
    
    protected static function myVarExport($array, $return = false, $lvl=0)
    {
        // Common output variables
        $indent      = '  ';
        $doublearrow = ' => ';
        $lineend     = ",\n";
        $stringdelim = '\'';

        // Check the export isn't a simple string / int
        if (is_object($array) && is_a($array, 'Cg_Php_Expression')) {
            $out = $array->getExpression();
        } elseif (is_string($array)) {
            if (strpos($array, "\n")) {
                $out = '"'.addcslashes($array, "\\\"\n").'"';
            } else {
                $out = $stringdelim . str_replace('\'', '\\\'', str_replace('\\', '\\\\', $array)) . $stringdelim;
            }
        } elseif (is_int($array) || is_float($array)) {
            $out = (string)$array;
        } elseif (is_bool($array)) {
            $out = $array ? 'true' : 'false';
        } elseif (is_null($array)) {
            $out = 'NULL';
        } elseif (is_resource($array)) {
            $out = 'resource';
        } else {
            // Begin the array export
            // Start the string
            $out = "array (\n";

            // Loop through each value in array
            foreach ($array as $key => $value) {
                // If the key is a string, delimit it
                if (is_string($key)) {
                    $key = str_replace('\'', '\\\'', str_replace('\\', '\\\\', $key));
                    $key = $stringdelim . $key . $stringdelim;
                }

                $val = self::myVarExport($value, true, $lvl+1);

                // Piece together the line
                for ($i = 0; $i <= $lvl; $i++)
                    $out .= $indent;
                $out .= $key . $doublearrow . $val . $lineend;
            }

            // End our string
            for ($i = 0; $i < $lvl; $i++)
                $out .= $indent;
            $out .= ")";
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
    static function exportArray($foo, $indent = 0, $withNumericKeys = false, $oneLine = false, $return = false) {
        $vx = self::myVarExport($foo, 1);
        $vx = preg_replace("/=> \n([ ]+)array \\(/", "=> array (\\1", $vx);
        $vx = preg_replace_callback("/\n[ ]+/", array('Ac_Util_Php', 'replaceIndent'), $vx);
        if ($indent) {
            $ind = str_repeat(" ", $indent);
            $vx = preg_replace("/\n/", "\n".$ind, $vx);
        }
        if (!$withNumericKeys) $vx = preg_replace ("/(\n[ ]+) \\d+ =>/", "\\1", $vx);
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
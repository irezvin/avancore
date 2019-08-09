<?php
// (c) YURiQUE (Yuriy Malchenko), 2005
// jmalchenko@gmail.com
//
// A very simple class with more natural cyrillic to latin transliteration.
//
// function Transliterate() takes 3 arguments - the string to transliterate itself,
// an encoding of the input string and an encoding of the result to get.
//
  class Translit {
    var $cyr=array(
    "Щ",  "Ш", "Ч", "Ц","Ю", "Я", "Ж", "А","Б","В","Г","Д","Е","Ё","З","И","Й","К","Л","М","Н","О","П","Р","С","Т","У","Ф","Х", "Ь","Ы","Ъ","Э","Є","Ї",
    "щ",  "ш", "ч", "ц","ю", "я", "ж", "а","б","в","г","д","е","ё","з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х", "ь","ы","ъ","э","є","ї");
    var $lat=array(
    "Shh","Sh","Ch","C","Ju","Ja","Zh","A","B","V","G","D","Je","Jo","Z","I","J","K","L","M","N","O","P","R","S","T","U","F","Kh","'","Y","`","E","Je","Ji",
    "shh","sh","ch","c","ju","ja","zh","a","b","v","g","d","je","jo","z","i","j","k","l","m","n","o","p","r","s","t","u","f","kh","'","y","`","e","je","ji"
    );
    var $tbl = false;
    
    function Transliterate($str, $encIn = "utf-8", $encOut = "utf-8"){
      if ($encIn != "utf-8") $str = iconv($encIn, "utf-8", $str);
      if ($this->tbl === false) {      
        $this->tbl = array();
        for($i=0; $i<count($this->cyr); $i++){
          $c_cyr = $this->cyr[$i];
          $c_lat = $this->lat[$i];
          $this->tbl[$c_cyr] = $c_lat;
        }
      }
      $str = strtr($str, $this->tbl);
      $str = preg_replace("/([qwrtpsdfghklzxcvbnmQWRTPSDFGHKLZXCVBNM]+)[jJ]e/u", "\${1}e", $str);
      $str = preg_replace("/([eyuioaEYUIOA]+)[Kk]h/u", "\${1}h", $str);
      $str = preg_replace("/^kh/", "h", $str);
      $str = preg_replace("/^Kh/", "H", $str);
      
      if ($encOut != 'utf-8') 
          return iconv("utf-8", $encOut, $str);
      else
          return $str;
    }
  }
?>

<?php

class Ac_Etl_Logger_Echo implements Ac_Etl_I_Logger {
    
    protected $isHtml = null;

    function setIsHtml($isHtml) {
        $this->isHtml = $isHtml;
    }

    function getIsHtml() {
        return $this->isHtml == null? ini_get('html_errors') : $this->isHtml;
    }
    
    function acceptItem(Ac_Etl_Log_Item $item) {
        $item->acceptedBy($this);
    }
    
    /*function logMessage($message, $type = Ac_Etl_I_Logger::logTypeError, $lineNo = false, $colName = false, $key = false, Ac_Etl_Import $import = null) {
        $m = array($type);
        if ($import !== null && strlen($id = $import->getImportId())) $m[] = "i:".$id;
        if ($lineNo !== false) $m[] = "l:".$lineNo;
        if ($colName !== false) $m[] = "c:".$colName;
        if ($key !== false) $m[] = $key;

        if ($this->getIsHtml()) $text = $this->makeHtml($type, $m, $message);
        else $text = "[".implode(" ", $m)."]\t".$message."\n";
        echo $text;
    }*/
    
    protected function makeHtml($type, $m, $message) {
        $message = htmlspecialchars($message);
        $res = "<div class='log {$type}'><span class='loc'>".implode(" ", $m)."</span> <span class='msg'>{$message}</span></div>";
        return $res;
    }
    
    
}
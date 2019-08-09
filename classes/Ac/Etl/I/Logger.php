<?php

    interface Ac_Etl_I_Logger {
        
        /*const logTypeError = 'logTypeError';
        const logTypeNotice = 'logTypeNotice';
        const logTypeDebug = 'logTypeDebug';
        */
        //function logMessage($message, $type = Ac_Etl_I_Logger::logTypeError, $lineNo = false, $colName = false, $key = false, Ac_Etl_Import $import = null);
         
        function acceptItem(Ac_Etl_Log_Item $item);
        
    }

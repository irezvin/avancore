<?php

interface Ac_I_Mail_Sender_WithDump extends Ac_I_Mail_Sender {
    
    const DUMP_NONE = 0;
    const DUMP_ERRORS = 1;
    const DUMP_ALL = 2;
    const DUMP_ALL_IF_NO_SEND = 4;
    
    function setDumpMode($dumpMode);
    function getDumpMode();
    
    function setDumpDir($dumpDir);
    function getDumpDir();
    
}
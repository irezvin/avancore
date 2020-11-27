<?php

interface Ac_I_Compat {
    
    const ACTION_NONE = 0;
    
    const ACTION_DEPRECATED = 1;
    
    const ACTION_THROW = 2;
    
    const MODE_RENAME_PROPERTY = 1;
    
    const MODE_USE_ACCESSOR = 2;
    
    const MODE_RENAME_METHOD = 4;
    
    const ACCESS_GET = 1;
    
    const ACCESS_SET = 2;
    
    const ACCESS_ISSET = 3;
    
}
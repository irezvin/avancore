<?php

interface Ac_I_Param_Source_Composite extends Ac_I_Param_Source {

    function listSources($mode = false, $path = false);

    function getSource($index);

    function addSource(Ac_I_Param_Source $source, $path, $mode = Ac_I_Param_Source::modeOverride);

    

}
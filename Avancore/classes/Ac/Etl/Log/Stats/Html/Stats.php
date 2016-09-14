<?php

class Ac_Etl_Log_Stats_Html_Stats extends Ac_Etl_Log_Stats_Html_Widget {
    
    /**
     * @var Ac_Etl_Log_Stats
     */
    protected $stats = false;

    function __construct(Ac_Etl_Log_Stats $stats) {
        parent::__construct();
        $this->stats = $stats;
    }
    
    /**
     * @return Ac_Etl_Log_Stats
     */
    function getStats() {
        return $this->stats;
    }

    function getAssetLibs() {
        return array(
            '{AC}/etl/stats.css',
            'https://raw.github.com/LeaVerou/prefixfree/gh-pages/prefixfree.min.js',
            '{AC}/vendor/jquery.min.js',
            '{AC}/util.js',
            '{AC}/etl/Stats.js',
        );
    }
    
    protected function doCreateWidgets() {
        $res = array();
        foreach ($this->stats->getTags() as $tag) {
            /*if (count($tag->getPath()) == 1)*/ $res[] = new Ac_Etl_Log_Stats_Html_Tag($tag, $this);
        }
        return $res;
    }
    
    function getHtml() {
        ob_start();
?> 
        <table class='logStats' id='<?php echo $this->id; ?>'>
            <thead>
                Search: <input type='text' id='<?php echo $this->id; ?>_search' />
            </thead>
            <tr class='header'>
                <th class='tagName'>Tag</th>
                <th class='count'>N</th>
                <th class='time'>T</th>
                <th class='mem'>M</th>
                <th class='avgTime'>T<sub>avg</sub></th>
                <th class='avgMemory'>M<sub>avg</sub></th>
                <th class='childCount'>N<sub>ch</sub></th>
                <th class='childTime'>T<sub>ch</sub></th>
                <th class='childMemory'>M<sub>ch</sub></th>
                <th class='childAvgTime'>T<sub>ch<sub>avg</sub></sub></th>
                <th class='childAvgMemory'>M<sub>ch<sub>avg</sub></sub></th>
            </tr>
<?php       echo implode("\n", $this->getWidgetsHtml()); ?>            
        </table>
<?php
        return ob_get_clean();
    }
    
    function getNumColumns() {
        return 11;
    }    
    
    function getJsVarName() {
        $res = 'stats_'.$this->getId();
        return $res;
    }
    
    function getPostJs() {
        $o = new Ac_Js_Object(
            $this->getJsVarName(),
            'Stats',
            array(
                'id' => $this->getId(),
                'tags' => $this->getWidgets(),
            )
        );
        $res = array_merge(parent::getPostJs(), array($o->init()));
        return $res;
    }
    
}
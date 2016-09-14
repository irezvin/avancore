<?php

class Ac_Etl_Log_Stats_Html_Tag extends Ac_Etl_Log_Stats_Html_Widget {
    
    /**
     * @var Ac_Etl_Log_Stats_Tag
     */
    protected $tag = false;
    
    /**
     * @var Ac_Etl_Log_Stats_Html_Stats 
     */
    protected $statsWidget = false;
    
    /**
     * @var Ac_Etl_Log_Stats_Html_Tag
     */
    protected $parentWidget = null;
    
    function __construct(Ac_Etl_Log_Stats_Tag $tag, Ac_Etl_Log_Stats_Html_Stats $statsWidget, Ac_Etl_Log_Stats_Html_Tag $parentWidget = null) {
        parent::__construct();
        $this->tag = $tag;
        $this->statsWidget = $statsWidget;
        $this->parentWidget = $parentWidget;
    }
    
    protected function doCreateWidgets() {
        $res = array();
        foreach ($this->tag->getItems() as $item) {
            $res[] = new Ac_Etl_Log_Stats_Html_Item($item, $this);
        }
        return $res;
    }
    
    /**
     * @return Ac_Etl_Log_Stats_Html_Stats
     */
    function getStatsWidget() {
        return $this->statsWidget;
    }
    
    /**
     * @return Ac_Etl_Log_Stats_Tag
     */
    function getTag() {
        return $this->tag;
    }
    
    function getInheritanceClassNames($inclThis = false, $prefix='child_') {
        $r = array();
        $p = $this->tag->getPath();
        $c = $inclThis? count($p) : count($p) - 1;
        for ($i = 1; $i <= $c; $i++) {
            $r[] = $prefix.implode("-", array_slice($p, 0, $i));
        }
        $res = implode(" ", $r);
        return $res;
    }
    
    function getHtml() {
        ob_start();
        $es = $this->tag->getExtendedStats(false);
        $classes = array(
            'tag', 
            'level'.count($this->tag->getPath()), 
            'oftag_'.$this->id,
            'direct_'.str_replace('/', '-', $this->tag->getPath(true)),
            'parent_'.implode('-', array_slice($this->tag->getPath(false), 0, -1)),
            $this->getInheritanceClassNames(),
        );
?>
            <tr class="<?php echo implode(' ', $classes); ?>" stats_tag="<?php echo $this->tag->getPath(true); ?>" id="<?php echo $this->id; ?>">
                <th class='tagName'>
<?php               for ($i = 1; $i < count($this->tag->getPath()); $i++) { ?>
                    <span class='indent indent<?php echo $i; ?>'></span>
<?php               } ?>
                    <?php echo $this->tag->getPath(true); ?> 
                </th>
                <td class='count'><?php $this->showValue($this->tag->getCount()); ?></td>
                <td class='time'><?php $this->showValue($this->tag->getTime()); ?></td>
                <td class='mem'><?php $this->showValue($this->tag->getMemory()); ?></td>
                <td class='avgTime'><?php $this->showValue($es['ownAvgTime']); ?></td>
                <td class='avgMemory'><?php $this->showValue($es['ownAvgMemory']); ?></td>
                <td class='childCount'><?php $this->showValue($es['childCount']); ?></td>
                <td class='childTime'><?php $this->showValue($es['childTime']); ?></td>
                <td class='childMemory'><?php $this->showValue($es['childMemory']); ?></td>
                <td class='childAvgTime'><?php $this->showValue($es['childAvgTime']); ?></td>
                <td class='childAvgMemory'><?php $this->showValue($es['childAvgMemory']); ?></td>
            </tr>
<?php       echo implode("\n", $this->getWidgetsHtml()); ?>
<?php
        return ob_get_clean();
    }
    
    function getJsVarName() {
        $res = 'tag_'.$this->getId();
        return $res;
    }
    
    function toJson() {
        return array(
            'id' => $this->id,
            'name' => $this->tag->getPath(true),
            'items' => $this->getWidgets(),
        );
    }
    
/*    
    function getPostJs() {
        $tagCr = new Ac_Js_Object(
            $this->getJsVarName(),
            'Importer_Tag',
            array(
                'id' => $this->getId()
            )
        );
        $res = array_merge(parent::getPostJs(), array($tagCr));
        return $res;
    }
*/    
}
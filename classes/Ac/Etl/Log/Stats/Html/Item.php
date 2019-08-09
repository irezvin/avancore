<?php

class Ac_Etl_Log_Stats_Html_Item extends Ac_Etl_Log_Stats_Html_Widget {
    
    /**
     * @var Ac_Etl_Log_Stats_Html_Tag
     */
    protected $tag = false;
    
    /**
     * @var Ac_Etl_Log_Item
     */
    protected $item = false;
    
    function __construct(Ac_Etl_Log_Item $item, Ac_Etl_Log_Stats_Html_Tag $tag) {
        parent::__construct();
        $this->tag = $tag;
        $this->item = $item;
    }
    
    function toJson() {
        return array(
            'id' => $this->id,
            'message' => $this->item->message,
            'messageId' => $this->item->id,
            'tags' => $this->item->tags,
        );
    }
    
    function getHtml() {
        ob_start();
        $classes = array(
            'item', 
            'level'.count($this->tag->getTag()->getPath()), 
            'type_'.$this->item->type,
            'oftag_'.$this->tag->getId(),
            'ofitem_'.$this->getId(),
            $this->tag->getInheritanceClassNames(true),
            'parent_'.str_replace('/', '-', $this->tag->getTag()->getPath(true)),
        );
?>
        <tr class="<?php echo implode(" ", $classes ); ?>">
            <th class="tagName">&nbsp;</th>
            <td class='count'>&nbsp;</td>
            <td class='time'><?php $this->showValue($this->item->spentTime); ?></td>
            <td class='mem'><?php $this->showValue($this->item->spentMemory); ?></td>
            <td class='details' colspan='<?php echo $this->tag->getStatsWidget()->getNumColumns() - 4; ?>'>
                <div class='content'><?php echo trim($this->item->message); ?></div>
            </td>

        </tr>
<?php
        return ob_get_clean();
    }
    
}
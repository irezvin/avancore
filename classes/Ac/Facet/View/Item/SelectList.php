<?php

class Ac_Facet_View_Item_SelectList extends Ac_Facet_ItemView {
    
    protected $dummyCaption = false;

    protected $size = null;
    
    protected $maxSize = false;
    
    protected $submitOnSelectChange = true;

    function setAutoHeight($autoHeight) {
        $this->autoHeight = $autoHeight;
    }

    function getAutoHeight() {
        return $this->autoHeight;
    }    
    
    function setDummyCaption($dummyCaption) {
        $this->dummyCaption = $dummyCaption;
    }

    function getDummyCaption() {
        return $this->dummyCaption !== false? $this->dummyCaption : '- '.$this->item->getCaption().' -';
    }
    
    function renderItem(Ac_Controller_Response_Html $response) {
        $v = $this->getExpandedPossibleValues();
        $size = $this->getSize();
        $a = array(
            'size' => $size? $size : 1,
            'name' => $this->getHtmlName(),
        );
        if ($this->submitOnSelectChange) {
            $a['onchange'] = 'if (this.form) this.form.submit();';
        }
        if ($multiple = $this->getItem()->getMultiple()) {
            $a['multiple'] = true;
            $a['name'] .= '[]';
            if (is_null($size)) {
                $a['size'] = max(count($v), 1);
                if ($this->maxSize !== false && $this->maxSize >= 0 && $a['size'] >= $this->maxSize) $a['size'] = $this->maxSize;
            }
        }
        if (count($v) == 1) $a['disabled'] = true;
        $av = $this->item->getValue();
        if (!is_array($av)) {
            if ($av !== false) $av = array($av);
                else $av = array();
        }
?>
        <select <?php echo Ac_Util::mkAttribs($a); ?>>
<?php       if (count($v) > 1 && !$multiple) { ?> 
            <option value=""><?php echo $this->getDummyCaption(); ?></option>
<?php } ?>
<?php       foreach ($v as $val => $item) { $t = $this->getCaption($item); ?>
            <option value="<?php echo htmlspecialchars($val); ?>" <?php if (strlen($val) && (in_array($val, $av))) { ?> selected="selected"<?php } ?>><?php echo htmlspecialchars(strip_tags($t)); ?></option>
<?php       } ?> 
        </select>
<?php
    }
    
    function getCaption($item) {
        $res = $item['title'];
        if (isset($item['count'])) $res .= ' ('.$item['count'].')';
        return $res;
    }

    function setSize($size) {
        $this->size = $size;
    }

    function getSize() {
        return $this->size;
    }

    function setMaxSize($maxSize) {
        $this->maxSize = $maxSize;
    }

    function getMaxSize() {
        return $this->maxSize;
    }    
    
}
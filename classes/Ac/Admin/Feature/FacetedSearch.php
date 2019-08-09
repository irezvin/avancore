<?php

class Ac_Admin_Feature_FacetedSearch extends Ac_Admin_Feature {
    
    /**
     * @var Ac_Facet_Set
     */
    var $facetSet = false;
    
    var $facetsJoinPartName = 'facetsJoin';
    
    function shouldUseFacets() {
        $res = $this->facetSet && $this->facetSet->getView()->getValue($_REQUEST);
        return $res;
    }
    
    function onCreateSqlSelect(Ac_Sql_Select $select) {
        
        
        parent::onCreateSqlSelect($select);
        
        if ($this->shouldUseFacets()) {
            $mapper = $this->manager->getMapper();
            $db = $mapper->getDb();
            $proto = $mapper->getSqlSelectPrototype();
            $fs = $this->facetSet;
            $sel = $fs->getImpl()->createSelectForItem(null);
            $pk = $db->n($mapper->pk);
            $sel->columns = "t.{$pk} AS {$pk}";
            $select->addTable(array(
                'class' => 'Ac_Sql_Select_Table_Subquery',
                'subquery' => $sel,
                'joinsAlias' => 't',
                'joinsOn' => array($mapper->pk),
            ), $this->facetsJoinPartName);
            $select->useAlias($this->facetsJoinPartName);
        }
    }
    
    function applyToFilterFormSettings(& $filterFormSettings) {
        if ($this->facetSet) {
            Ac_Util::ms($filterFormSettings, array(
                'controls' => array(
                    'facetedSearch' => array(
                        'class' => 'Ac_Admin_Feature_FacetedSearch_Control',
                        'facetSet' => $this->facetSet,
                    ),
                ),
            ));
        }
    }
    
}
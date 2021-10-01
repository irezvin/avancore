<?php

abstract class Ac_Cg_Base extends Ac_Prototyped implements Ac_I_ArraySerializable_Extended {

    static $strictParams = Ac_Prototyped::STRICT_PARAMS_WARNING;
    
    function hasPublicVars() {
        return true;
    }    
    
    /**
     * @return array(parentClass, parentMemberName)
     */
    function getSerializationParentInfo() {
        return array(null, null);
    }
    
    /**
     * @return array (myProperty => array(arrayKey, defaultClass, crArgs))
     * crArgs => array(keyA, keyB, keyC) <- constructor args map
     * crArgs = false -- just copy $this->$myProperty to/from $array[$arrayKey]
     */
    function getSerializationMap() {
        $res = array();
        return $res;
    }
    
    protected function beforeSerialize(& $vars) {
        
    }
    
    protected function beforeUnserialize(& $vars) {
        
    }
    
    public function serializeToArray() {
        $pubKeys = array_keys(array_intersect_key(Ac_Util::getPublicVars($this), get_class_vars(get_class($this))));
        $allowed = array_unique(array_merge($pubKeys, array_keys($this->getSerializationMap())));
        $array = array_intersect_key(get_object_vars($this), array_flip($allowed));
        $this->beforeSerialize($array);
        $array = Ac_Impl_ArraySerializer::serializeToArray($this, $array);
        return $array;
    }
    
    public function unserializeFromArray($array) {
        $tmp = Ac_Impl_ArraySerializer::$alwaysUnserializedClasses;
        Ac_Impl_ArraySerializer::$alwaysUnserializedClasses = array_unique(array_merge(
            Ac_Impl_ArraySerializer::$alwaysUnserializedClasses, array(
                'Ac_Cg_Php_Expression',
            )
        ));
        $this->beforeUnserialize($array);
        $objects = array();
        foreach ($this->getSerializationMap() as $myProp => $info) {
            $arrayKey = $info[0];
            if (isset($array[$arrayKey])) {
                $objects[$arrayKey] = $array[$arrayKey];
                unset($array[$arrayKey]);
            }
        }
        $vars = Ac_Impl_ArraySerializer::getUnserializationVars($this, $array);
        $allowed = array_keys(Ac_Util::getPublicVars($this));
        foreach (array_intersect_key($vars, array_flip($allowed)) as $k => $v) $this->$k = $v;
        if ($objects) {
            $objects = Ac_Impl_ArraySerializer::getUnserializationVars($this, $objects);
            foreach ($objects as $k => $v) $this->$k = $v;
        }
        Ac_Impl_ArraySerializer::$alwaysUnserializedClasses = $tmp;
    }
    
    protected function refRelation(Ac_Sql_Dbi_Relation $relation) {
        return array($relation->ownTable->name, $relation->name);
    }
    
    protected function refModel(Ac_Cg_Model $model) {
        return $model->name;
        
    }
    
    protected function unrefRelation($ref) {
        list ($table, $name) = $ref;
        return $this->_model->_domain->getDatabase()->getTable($table)->getRelation($name);
    }
    
    protected function unrefModel($ref) {
        return $this->_model->_domain->getModel($ref);
    }
    
    protected function refColumn(Ac_Sql_Dbi_Column $column) {
        return array($column->_table->name, $column->name);
    }
    
    protected function unrefColumn($ref) {
        list ($table, $name) = $ref;
        return $this->_model->_domain->getDatabase()->getTable($table)->getColumn($name);
    }
    

}
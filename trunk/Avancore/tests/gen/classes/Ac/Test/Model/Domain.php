<?php
class Ac_Test_Model_Domain {
 
    /**
     * @return Ac_Test_Model_Orientation_Mapper 
     */
    static function & getAeTestModelOrientationMapper () {
        $res = & Ac_Dispatcher::getMapper('Ac_Test_Model_Orientation_Mapper');
        return $res;
    }
 
    /**
     * @return Ac_Test_Model_People_Mapper 
     */
    static function & getAeTestModelPeopleMapper () {
        $res = & Ac_Dispatcher::getMapper('Ac_Test_Model_People_Mapper');
        return $res;
    }
 
    /**
     * @return Ac_Test_Model_Relation_Type_Mapper 
     */
    static function & getAeTestModelRelationTypeMapper () {
        $res = & Ac_Dispatcher::getMapper('Ac_Test_Model_Relation_Type_Mapper');
        return $res;
    }
 
    /**
     * @return Ac_Test_Model_Relation_Mapper 
     */
    static function & getAeTestModelRelationMapper () {
        $res = & Ac_Dispatcher::getMapper('Ac_Test_Model_Relation_Mapper');
        return $res;
    }
 
    /**
     * @return Ac_Test_Model_Tag_Mapper 
     */
    static function & getAeTestModelTagMapper () {
        $res = & Ac_Dispatcher::getMapper('Ac_Test_Model_Tag_Mapper');
        return $res;
    }

 
    /**
     * @return Ac_Test_Model_Orientation 
     */
    static function & Ac_Test_Model_Orientation (& $object) {
        return $object;
    }
 
    /**
     * @return Ac_Test_Model_People 
     */
    static function & Ac_Test_Model_People (& $object) {
        return $object;
    }
 
    /**
     * @return Ac_Test_Model_Relation_Type 
     */
    static function & Ac_Test_Model_Relation_Type (& $object) {
        return $object;
    }
 
    /**
     * @return Ac_Test_Model_Relation 
     */
    static function & Ac_Test_Model_Relation (& $object) {
        return $object;
    }
 
    /**
     * @return Ac_Test_Model_Tag 
     */
    static function & Ac_Test_Model_Tag (& $object) {
        return $object;
    }

}
?>
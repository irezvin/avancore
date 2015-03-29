<?php

abstract class Ac_Impl_ArraySerializer {

    static $parents = array();
    
    static $alwaysUnserializedClasses = array();
    
    static function pushParent($parent) {
        self::$parents[] = $parent;
    }
    
    static function getParent($class = false) {
        $p = self::$parents;
        while($p && !$res) {
            $res = array_pop($p);
            if (strlen($class) && !is_object($res) && $res instanceof $class) $res = null;
        }
        return $res;
    }
    
    static function popParent() {
        if (!count(self::$parents)) throw new Ac_E_InvalidCall("Call to Ac_Impl_ArraySerializer::popParent() without prior ::pushParent()");
        $res = array_pop(self::$parents);
        return $res;
    }
    
    static function toolArraySerialize(array $propsAndVals, $className = false, $all = false, $withDefs = true) {
        $defs = array();
        $res = array();
        if (strlen($className)) {
            $defs = get_class_vars($className);
            $res['__class'] = $className;
        }
        foreach ($propsAndVals as $k => $v) {
            if ($all || $k{0} !== '_') {
                if ($withDefs || !(array_key_exists($k, $defs) && $defs[$k] === $v)) {
                    if (is_object($v)) {
                        if ($v instanceof Ac_I_ArraySerializable) {
                            $v = $v->serializeToArray();
                        } else {
                            throw new Ac_E_ArraySerialization("Only Ac_I_ArraySerializable instances must occur in \$propsAndVals, but ".get_class($v)." (key '{$k}') does not implement it");
                        }
                        $res[$k] = $v;
                    } elseif (is_array($v)) {
                        $res[$k] = self::toolArraySerialize($v, false, true, $withDefs);
                    } else {
                        $res[$k] = $v;
                    }
                }
            }
        }
        return $res;
    }
    
    public static function getUnserializationVars(Ac_I_ArraySerializable_Extended $object, array $array) {
        list ($parentClass, $parentMember) = $object->getSerializationParentInfo();
        if ($parentMember && $parent = Ac_Impl_ArraySerializer::getParent($parentClass)) $object->$parentMember = $parent;
        Ac_Impl_ArraySerializer::pushParent($object);
        $res = array();
        foreach ($object->getSerializationMap() as $myProp => $si) {
            list ($arrayKey, $defClass, $crArgs) = $si;
            if (array_key_exists($arrayKey, $array)) {
                if ($crArgs === false) {
                    $res[$myProp] = $array[$arrayKey];
                } elseif (is_array($array[$arrayKey]) && $array[$arrayKey]) {
                    $notMany = false;
                    if (isset($array[$arrayKey]['__class'])) {
                        $notMany = true;
                        $array[$arrayKey] = array($array[$arrayKey]);
                    }
                    $res[$myProp] = array();
                    foreach ($array[$arrayKey] as $subKey => $item) {
                        $item['__parent'] = $object;
                        if (!is_array($item) || !isset($item['__class'])) throw new Exception("WTF");
                        $crArgValues = array();
                        if (is_array($crArgs)) {
                            foreach ($crArgs as $prop) {
                                if (!strncmp($prop, '::', 2)) 
                                    $crArgValues[] = Ac_Impl_ArraySerializer::getParent(substr($prop, 2));
                                $crArgValues[] = $item[$prop];
                                unset($item[$prop]);
                            }
                        }
                        unset($item['__parent']);
                        $rClass = new ReflectionClass($item['__class']);
                        unset($item['__class']);
                        if (strlen($defClass)) {
                            if (!($rClass->name === $defClass || $rClass->isSubclassOf($defClass) || interface_exists($defClass) && $rClass->implementsInterface($defClass))) {
                                throw new Ac_E_ArraySerialization ($rClass->getName()." doesn't inherit or implement {$defClass}");
                            }
                        }
                        $instance = $rClass->newInstanceArgs($crArgValues);
                        $res[$myProp][$subKey] = $instance;
                        $instance->unserializeFromArray($item);
                    }
                    
                    if ($notMany) $res[$myProp] = $res[$myProp][0];
                }
            }
            unset($array[$arrayKey]);
        }
        foreach ($array as $k => $v) {
            if (is_array($v) && self::$alwaysUnserializedClasses) {
                $v = self::autoUnserialize($v);
            }
            $res[$k] = $v;
        }
        Ac_Impl_ArraySerializer::popParent();
        return $res;
    }
    
    static function autoUnserialize(array $array) {
        foreach ($array as $k => $v) {
            if (is_array($v)) $array[$k] = self::autoUnserialize($v);
        }
        if (isset($array['__class']) && in_array($array['__class'], self::$alwaysUnserializedClasses)) {
            $c = $array['__class'];
            $res = new $c;
            unset($array['__class']);
            if ($res instanceof Ac_I_ArraySerializable) $res->unserializeFromArray($array);
            else {
                Ac_Accessor::setObjectProperty ($res, $array);
            }
        } else {
            $res = $array;
        }
        return $res;
    }
    
    static function serializeToArray(Ac_I_ArraySerializable $object, array $objectVars = array()) {
        if (func_num_args() == 1) $objectVars = Ac_Util::getPublicVars($object);
        $res = Ac_Impl_ArraySerializer::toolArraySerialize($objectVars, get_class($object));
        if ($object instanceof Ac_I_ArraySerializable_Extended) {
            foreach ($object->getSerializationMap() as $myProp => $si) {
                list ($arrayKey, $defClass, $crArgs) = $si;
                if (is_array($objectVars[$myProp])) {
                    foreach($objectVars[$myProp] as $k => $v) {
                        $res[$arrayKey][$k] = is_object($v)? $v->serializeToArray() : $v;
                    }
                } elseif (is_object($objectVars[$myProp])) {
                    $res[$arrayKey] = $objectVars[$myProp]->serializeToArray();
                } else {
                    $res[$arrayKey] = $objectVars[$myProp];
                }
            }
        }
        return $res;
    }
    
    
}
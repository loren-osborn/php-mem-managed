<?php
namespace LinuxDr\MemManaged\Internals;

use ReflectionClass;
use ReflectionProperty;

class ProxyClassFactory
{
    public function getProxyClassName($obj)
    {
        $classInfo = new ReflectionClass($obj);
        $interfaceList = $this->getClassInterfaceList($classInfo);
        $proxyClass = "LinuxDr\\MemManaged\\Internals\\ObjectProxy";
        $publicProperties = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED;
        $inheritanceDisqualifiers =
            $classInfo->isFinal() ||
            (count($classInfo->getProperties($publicProperties)) > 0);
        if (!$inheritanceDisqualifiers) {
            $proxySpec = array('parent' => $classInfo->getName());
            $classNameSuffix = sha1(json_encode($proxySpec));
            $proxyClass .= "\\DynamicallyGenerated\\ObjProxy_" . $classNameSuffix;
        } elseif (count($interfaceList) > 0) {
            $proxySpec = array('interfaces' =>$interfaceList);
            $classNameSuffix = sha1(json_encode($proxySpec));
            $proxyClass .= "\\DynamicallyGenerated\\ObjProxy_" . $classNameSuffix;
        }
        return $proxyClass;
    }

    private function getClassInterfaceList($classInfo)
    {
        $interfaceReflectionList = $classInfo->getInterfaces();
        $interfaceList = $classInfo->getInterfaceNames();
        $interfaceList = array();
        foreach ($interfaceReflectionList as $item) {
            $parent = false;
            foreach ($interfaceReflectionList as $possibleChild) {
                if ($possibleChild->isSubclassOf($item->getName())) {
                    $parent = true;
                }
            }
            if (!$parent) {
                $interfaceList[] = $item->getName();
            }
        }
        sort($interfaceList);
        return $interfaceList;
    }
}
/*
$reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED)

*/
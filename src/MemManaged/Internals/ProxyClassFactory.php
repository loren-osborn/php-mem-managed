<?php
namespace LinuxDr\MemManaged\Internals;

use ReflectionClass;

class ProxyClassFactory
{
    public function getProxyClassName($obj)
    {
        $classInfo = new ReflectionClass($obj);
        $interfaceList = $classInfo->getInterfaceNames();
        $proxyClass = "LinuxDr\\MemManaged\\Internals\\ObjectProxy";
        if (count($interfaceList) > 0) {
            $proxySpec = array('interfaces' =>$interfaceList);
            $classNameSuffix = sha1(json_encode($proxySpec));
            $proxyClass .= "\\DynamicallyGenerated\\ObjProxy_" . $classNameSuffix;
        }
        return $proxyClass;
    }
}

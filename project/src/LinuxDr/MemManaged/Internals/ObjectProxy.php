<?php
namespace LinuxDr\MemManaged\Internals;

use ReflectionClass;

abstract class ObjectProxy
{
	abstract public function getProxiedObject();

    abstract protected static function getClassName();

    protected function init() {}

    public static function create()
    {
        $staticClassName = get_called_class();
        $targetClass = new ReflectionClass(call_user_func_array( $staticClassName . '::getClassName', func_get_args()));
        $targetInterfaces = $targetClass->getInterfaces();
        $proxyToInstantiate = $staticClassName;
        if (false && count($targetInterfaces) > 0) {
            $parentProxyClass = $proxyToInstantiate;
            $interfaceList = array_keys($targetInterfaces);
            sort($interfaceList, SORT_STRING);
            $md5Input = $parentProxyClass . ':' . implode(',', $interfaceList);
            $proxyClassSuffix = md5($md5Input, false);
            // $proxyClassSuffix = md5($parentProxyClass . ':' . implode(',', $interfaceList), false);
            $proxyToInstantiate = __NAMESPACE__ . '\\ObjectProxy\\DynamicallyGenerated\\ObjProxy_' . $proxyClassSuffix;
            if (!class_exists($proxyToInstantiate, false)) {
                throw new \Exception('TBD instantiating variant: ' . $md5Input);
            }
        }
        $result = new $proxyToInstantiate();
        call_user_func_array( array($result, 'init'), func_get_args());
        return $result;
    }

    public function getClass()
    {
        return get_class($this->getProxiedObject());
    }

    public function isA($className)
    {
        return is_a($this->getProxiedObject(), $className, false);
    }

    public function __isset($propName)
    {
        return isset($this->getProxiedObject()->$propName);
    }

    public function __get($propName)
    {
        return $this->getProxiedObject()->$propName;
    }

    public function __set($propName, $newVal)
    {
        $this->getProxiedObject()->$propName = $newVal;
    }

    public function __unset($propName)
    {
        unset($this->getProxiedObject()->$propName);
    }

    public function __call($method, $args)
    {
        return call_user_func_array(array($this->getProxiedObject(), $method), $args);
    }

    public function __invoke()
    {
        return call_user_func_array($this->getProxiedObject(), func_get_args());
    }
}

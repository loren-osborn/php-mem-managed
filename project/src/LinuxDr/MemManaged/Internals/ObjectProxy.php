<?php
namespace LinuxDr\MemManaged\Internals;

abstract class ObjectProxy
{
	abstract public function getProxiedObject();

    protected function init() {}

    static public function create()
    {
        $result = new static();
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
}

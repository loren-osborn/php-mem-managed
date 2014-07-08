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
}

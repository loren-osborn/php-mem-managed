<?php
namespace LinuxDr\MemManaged\Tests\Internals;

use PHPUnit_Framework_TestCase;
use LinuxDr\MemManaged\Internals\ObjectProxy;
use ReflectionClass;
use DateTime;

class SampleObjectProxy extends ObjectProxy
{
    private $proxiedObject = null;

    public function getProxiedObject()
    {
        return $this->proxiedObject;
    }

    protected function init($obj)
    {
        $this->proxiedObject = $obj;
    }
}

class ObjectProxyTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $classInfo = new ReflectionClass('LinuxDr\\MemManaged\\Internals\\ObjectProxy');
        $this->assertTrue($classInfo->isAbstract());
        $this->assertTrue($classInfo->getMethod('getProxiedObject')->isAbstract());
        $this->assertTrue($classInfo->getMethod('getProxiedObject')->isPublic());
        $object = SampleObjectProxy::create($this);
    }

    public function testGetClass()
    {
        $proxy = SampleObjectProxy::create($this);
        $this->assertEquals(get_class($this), $proxy->getClass());
        $date = new DateTime();
        $dateProxy = SampleObjectProxy::create($date);
        $this->assertEquals(get_class($date), $dateProxy->getClass());
    }
}

<?php
namespace LinuxDr\MemManaged\Tests\Internals;

use PHPUnit_Framework_TestCase;
use LinuxDr\MemManaged\Internals\ObjectProxy;
use ReflectionClass;
use DateTime;

class ObjectProxyTest__SampleObjectProxy extends ObjectProxy
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
        $object = ObjectProxyTest__SampleObjectProxy::create($this);
    }

    public function getTestObjs()
    {
        $objs = array();
        $objs['proxy'] = ObjectProxyTest__SampleObjectProxy::create($this);
        $objs['proxyProxy'] = ObjectProxyTest__SampleObjectProxy::create($objs['proxy']);
        $objs['date'] = new DateTime();
        $objs['dateProxy'] = ObjectProxyTest__SampleObjectProxy::create($objs['date']);
        return $objs;
    }

    public function testGetClass()
    {
        $objs = $this->getTestObjs();
        $this->assertEquals(get_class($this), $objs['proxy']->getClass());
        $this->assertEquals('LinuxDr\\MemManaged\\Tests\\Internals\\ObjectProxyTest', $objs['proxy']->getClass());
        $this->assertEquals(get_class($objs['proxy']), $objs['proxyProxy']->getClass());
        $this->assertEquals('LinuxDr\\MemManaged\\Tests\\Internals\\ObjectProxyTest__SampleObjectProxy', $objs['proxyProxy']->getClass());
        $this->assertEquals(get_class($this), $objs['proxy']->getClass());
        $this->assertEquals('LinuxDr\\MemManaged\\Tests\\Internals\\ObjectProxyTest', $objs['proxy']->getClass());
        $this->assertEquals(get_class($objs['date']), $objs['dateProxy']->getClass());
        $this->assertEquals('DateTime', $objs['dateProxy']->getClass());
    }

    public function testIsA()
    {
        $objs = $this->getTestObjs();
        $this->assertTrue($objs['proxy']->isA('LinuxDr\\MemManaged\\Tests\\Internals\\ObjectProxyTest'));
        $this->assertFalse($objs['proxy']->isA('LinuxDr\\MemManaged\\Tests\\Internals\\ObjectProxyTest__SampleObjectProxy'));
        $this->assertFalse($objs['proxy']->isA('LinuxDr\\MemManaged\\Internals\\ObjectProxy'));
        $this->assertFalse($objs['proxy']->isA('DateTime'));
        $this->assertFalse($objs['dateProxy']->isA('LinuxDr\\MemManaged\\Tests\\Internals\\ObjectProxyTest'));
        $this->assertFalse($objs['dateProxy']->isA('LinuxDr\\MemManaged\\Tests\\Internals\\ObjectProxyTest__SampleObjectProxy'));
        $this->assertFalse($objs['dateProxy']->isA('LinuxDr\\MemManaged\\Internals\\ObjectProxy'));
        $this->assertTrue($objs['dateProxy']->isA('DateTime'));
        $this->assertFalse($objs['proxyProxy']->isA('LinuxDr\\MemManaged\\Tests\\Internals\\ObjectProxyTest'));
        $this->assertTrue($objs['proxyProxy']->isA('LinuxDr\\MemManaged\\Tests\\Internals\\ObjectProxyTest__SampleObjectProxy'));
        $this->assertTrue($objs['proxyProxy']->isA('LinuxDr\\MemManaged\\Internals\\ObjectProxy'));
        $this->assertFalse($objs['proxyProxy']->isA('DateTime'));
    }

    public function getSymbolNames()
    {
        return array(array('foo'), array('bar'), array('baz'));
    }

    /**
      * @dataProvider getSymbolNames
      */
    public function testPropertyAccess($propertyName)
    {
        $mock = $this->getMock('SomeClass');
        $proxy = ObjectProxyTest__SampleObjectProxy::create($mock);
        $this->assertFalse(isset($proxy->$propertyName));
        $mock->$propertyName = 3.14159;
        $this->assertTrue(isset($proxy->$propertyName));
        $this->assertEquals(3.14159, $proxy->$propertyName);
        $proxy->$propertyName = 1.41421;
        $this->assertEquals(1.41421, $proxy->$propertyName);
        $mock->$propertyName = 2.71828;
        $this->assertEquals(2.71828, $proxy->$propertyName);
        unset($proxy->$propertyName);
        $this->assertFalse(isset($proxy->$propertyName));
    }
}

<?php
namespace LinuxDr\MemManaged\Tests\Internals;

use PHPUnit_Framework_TestCase;
use LinuxDr\MemManaged\Internals\ObjectProxy;
use ReflectionClass;
use DateTime;
use Iterator;

class ObjectProxyTest__SampleObjectProxy extends ObjectProxy
{
    private $proxiedObject = null;

    public function getProxiedObject()
    {
        return $this->proxiedObject;
    }

    protected static function getClassName()
    {
        $argList = func_get_args();
        return get_class($argList[0]);
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

    public function getArgsAndRetVals()
    {
        $argLists = array(array(), array('asdf'), array(1, 3, 5, 7));
        $valuesReturned = array(null, 4, 'some string');
        $retVal = array();
        foreach ($argLists as $args) {
            foreach ($valuesReturned as $val) {
                $retVal[] = array($args, $val);
            }
        }
        return $retVal;
    }

    public function getMethodNamesArgsAndRetVals()
    {
        $METHOD_NAME_INDEX = 0;
        $ARGS_INDEX = 0;
        $RET_VAL_INDEX = 1;
        $symbolNames = $this->getSymbolNames();
        $argAndRetValPairs = $this->getArgsAndRetVals();
        $retVal = array();
        foreach ($symbolNames as $methodArray) {
            foreach ($argAndRetValPairs as $pair) {
                $retVal[] = array($methodArray[$METHOD_NAME_INDEX], $pair[$ARGS_INDEX], $pair[$RET_VAL_INDEX]);
            }
        }
        return $retVal;
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

    /**
      * @dataProvider getMethodNamesArgsAndRetVals
      */
    public function testMethodAccess($methodName, $argList, $valToReturn)
    {
        $closureThis = $this;
        $expectedArgs = array_map(function ($val) use ($closureThis) { return $closureThis->equalTo($val); }, $argList);
        $mock = $this->getMock('SomeClass', array($methodName));
        $method = $mock->expects($this->once())
            ->method($methodName);
        call_user_func_array(array($method, 'with'), $expectedArgs)
            ->will($this->returnValue($valToReturn));
        $proxy = ObjectProxyTest__SampleObjectProxy::create($mock);
        $result = call_user_func_array(array($proxy, $methodName), $argList);
        $this->assertEquals($valToReturn, $result);
    }

    /**
      * @dataProvider getArgsAndRetVals
      */
    public function testInvoke($argList, $valToReturn)
    {
        $closureThis = $this;
        $expectedArgs = array_map(function ($val) use ($closureThis) { return $closureThis->equalTo($val); }, $argList);
        $mock = $this->getMock('SomeClass', array('__invoke'));
        $method = $mock->expects($this->once())
            ->method('__invoke');
        call_user_func_array(array($method, 'with'), $expectedArgs)
            ->will($this->returnValue($valToReturn));
        $proxy = ObjectProxyTest__SampleObjectProxy::create($mock);
        $result = call_user_func_array($proxy, $argList);
        $this->assertEquals($valToReturn, $result);
    }

    public function testInterfaceMirroring()
    {
        /*
         * "yeild" is new in PHP 5.5, but gives us a quick way to create an
         * object implementing "Iterator"
         */
        $simpleGenFunc = (function () {
            for ($i = 1; $i <= 3; $i++) {
                yield $i;
            }
        });
        $simpleGenerator = $simpleGenFunc();
        $generatorConsumer = (function (Iterator $iter) {
            $retVal = array();
            foreach ($iter as $val) {
                $retVal[] = $val;
            }
            return $retVal;
        });
        $proxy = ObjectProxyTest__SampleObjectProxy::create($simpleGenerator);
        $this->markTestSkipped('Not yet implemented');
        $result = $generatorConsumer($proxy);
        $this->assertEquals(array(1, 2, 3), $result);
    }
}

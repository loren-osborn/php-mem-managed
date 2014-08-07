<?php
namespace LinuxDr\MemManaged\Tests\Internals;

use DateTime;
use ReflectionClass;
use PHPUnit_Framework_TestCase;
use LinuxDr\MemManaged\Internals\ProxyClassFactory;

final class ProxyClassFactoryTest_TestFinalClass
{
}

interface ProxyClassFactoryTest_TestInterface
{
    public function foo();
}

interface ProxyClassFactoryTest_TestParentInterface
{
    public function bar();
}

interface ProxyClassFactoryTest_TestChildInterface extends ProxyClassFactoryTest_TestParentInterface
{
    public function baz();
}

final class ProxyClassFactoryTest_TestFinalClassHavingInterface
    implements ProxyClassFactoryTest_TestInterface
{
    public function foo()
    {
    }
}

final class ProxyClassFactoryTest_TestFinalClassHavingAllInterface
    implements ProxyClassFactoryTest_TestInterface, ProxyClassFactoryTest_TestParentInterface, ProxyClassFactoryTest_TestChildInterface
{
    public function foo()
    {
    }
    public function bar()
    {
    }
    public function baz()
    {
    }
}

final class ProxyClassFactoryTest_TestFinalClassHavingAllInterfacesDifferentOrder
    implements ProxyClassFactoryTest_TestChildInterface, ProxyClassFactoryTest_TestInterface
{
    public function foo()
    {
    }
    public function bar()
    {
    }
    public function baz()
    {
    }
}

class ProxyClassFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        // /*
        //  * "yeild" is new in PHP 5.5, but gives us a quick way to create an
        //  * object implementing "Iterator"
        //  */
        // $simpleGenFunc = (function () {
        //     for ($i = 1; $i <= 3; $i++) {
        //         yield $i;
        //     }
        // });
        // $simpleGenerator = $simpleGenFunc();
        $factory = new ProxyClassFactory();
        $finalClassObj = new ProxyClassFactoryTest_TestFinalClass();
        $finalClassProxyClass = $factory->getProxyClassName($finalClassObj);
        $this->assertEquals(
            "LinuxDr\\MemManaged\\Internals\\ObjectProxy",
            $finalClassProxyClass,
            "Expected value for final class or class with final public methods");
        $finalClassWithInterface = new ProxyClassFactoryTest_TestFinalClassHavingInterface();
        $interfaceClassProxyClass = $factory->getProxyClassName($finalClassWithInterface);
        $proxySpec = array('interfaces' =>
            array("LinuxDr\\MemManaged\\Tests\\Internals\\ProxyClassFactoryTest_TestInterface"));
        $classNameSuffix = sha1(json_encode($proxySpec));
        $this->assertEquals(
            "LinuxDr\\MemManaged\\Internals\\ObjectProxy\\DynamicallyGenerated\\ObjProxy_" . $classNameSuffix,
            $interfaceClassProxyClass,
            "Final classes (or classes wth final methods) that implement interfaces should " .
                "illicit proxy implementing those interfaces");
        $finalClassWithAllInterfaces = new ProxyClassFactoryTest_TestFinalClassHavingAllInterface();
        $finalClassWithAllInterfaces2 = new ProxyClassFactoryTest_TestFinalClassHavingAllInterfacesDifferentOrder();
        $allInterfacesClassProxyClass = $factory->getProxyClassName($finalClassWithAllInterfaces);
        $allInterfacesClassProxyClass2 = $factory->getProxyClassName($finalClassWithAllInterfaces2);
        $proxySpec = array('interfaces' =>
            array(
                "LinuxDr\\MemManaged\\Tests\\Internals\\ProxyClassFactoryTest_TestChildInterface",
                "LinuxDr\\MemManaged\\Tests\\Internals\\ProxyClassFactoryTest_TestInterface"));
        $classNameSuffix = sha1(json_encode($proxySpec));
        $this->assertEquals(
            "LinuxDr\\MemManaged\\Internals\\ObjectProxy\\DynamicallyGenerated\\ObjProxy_" . $classNameSuffix,
            $allInterfacesClassProxyClass,
            "Final classes (or classes wth final methods) that implement interfaces should " .
                "illicit proxy implementing those interfaces in an unambiguous order");
        $this->assertEquals(
            "LinuxDr\\MemManaged\\Internals\\ObjectProxy\\DynamicallyGenerated\\ObjProxy_" . $classNameSuffix,
            $allInterfacesClassProxyClass2,
            "Final classes (or classes wth final methods) that implement interfaces should " .
                "illicit proxy implementing those interfaces in an unambiguous order");
        $this->assertEquals(
            $allInterfacesClassProxyClass,
            $allInterfacesClassProxyClass2,
            "Final classes (or classes wth final methods) that implement interfaces should " .
                "illicit proxy implementing those interfaces in an unambiguous order");
        $normalClassObj = new DateTime();
        $normalClassProxyClass = $factory->getProxyClassName($normalClassObj);
        $proxySpec = array('parent' => 'DateTime');
        $classNameSuffix = sha1(json_encode($proxySpec));
        $this->assertEquals(
            "LinuxDr\\MemManaged\\Internals\\ObjectProxy\\DynamicallyGenerated\\ObjProxy_" . $classNameSuffix,
            $normalClassProxyClass,
            "Normal classes should just be based on the class name.");
        $normalClassWithPublicPropertiesObj = new ReflectionClass($normalClassObj);
        $normalClassWithPublicPropertiesProxyClass = $factory->getProxyClassName($normalClassWithPublicPropertiesObj);
        $proxySpec = array('interfaces' => array('Reflector'));
        $classNameSuffix = sha1(json_encode($proxySpec));
        $this->assertEquals(
            "LinuxDr\\MemManaged\\Internals\\ObjectProxy\\DynamicallyGenerated\\ObjProxy_" . $classNameSuffix,
            $normalClassWithPublicPropertiesProxyClass,
            "Normal classes with public properties can't be inherited from properly.");
    }
}

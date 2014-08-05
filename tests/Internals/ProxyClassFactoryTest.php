<?php
namespace LinuxDr\MemManaged\Tests\Internals;

use PHPUnit_Framework_TestCase;
use LinuxDr\MemManaged\Internals\ProxyClassFactory;

final class ProxyClassFactoryTest_TestFinalClass
{
}

interface ProxyClassFactoryTest_TestInterface
{
    public function foo();
}

final class ProxyClassFactoryTest_TestFinalClassHavingInterface
    implements ProxyClassFactoryTest_TestInterface
{
    public function foo()
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
    }
}

<?php
namespace LinuxDr\MemManaged\Tests;

use PHPUnit_Framework_TestCase;
use LinuxDr\MemManaged\MemManaged;


class MemManagedTest_SampleClientClass {
    use MemManaged;

    private $references = array();

    public function addReferenceTo($obj)
    {
        $this->references[] = $obj->newReference();
    }

    public function createAndAddReferenceTo($class)
    {
        $obj = $class::create();
        $this->references[] = $obj;
        return $obj;
    }
}

class MemManagedTest_SampleDerivedClass extends MemManagedTest_SampleClientClass {
    private $initArgs;

    protected function init($first = 5)
    {
        $this->initArgs = func_get_args();
        $this->initArgs[0] = $first;
    }

    public function getInitArgs() 
    {
        return $this->initArgs;
    }


}

class MemManagedTest extends PHPUnit_Framework_TestCase
{
    public function testClientCreation()
    {
        $obj = MemManagedTest_SampleClientClass::create();
        $this->assertEquals('LinuxDr\\MemManaged\\Tests\\MemManagedTest_SampleClientClass', $obj->getClass());
        $obj = MemManagedTest_SampleDerivedClass::create();
        $this->assertEquals('LinuxDr\\MemManaged\\Tests\\MemManagedTest_SampleDerivedClass', $obj->getClass());
        $this->assertEquals(array(5), $obj->getInitArgs());
        $obj = MemManagedTest_SampleDerivedClass::create(1, 2, 3);
        $this->assertEquals('LinuxDr\\MemManaged\\Tests\\MemManagedTest_SampleDerivedClass', $obj->getClass());
        $this->assertEquals(array(1, 2, 3), $obj->getInitArgs());
    }

    public function testGetReference()
    {
        $obj = MemManagedTest_SampleClientClass::create();
        $otherRef = $obj->newReference();
        $this->assertSame($obj->getReferencedObject(), $otherRef->getReferencedObject());
    }

    public function testGetReferencingObject()
    {
        $obj = MemManagedTest_SampleClientClass::create();
        $this->assertSame($obj->getReferencingObject(), $this);
        $otherObj = $obj->createAndAddReferenceTo('LinuxDr\\MemManaged\\Tests\\MemManagedTest_SampleClientClass');
        $this->assertSame($otherObj->getReferencingObject(), $obj);
    }
}

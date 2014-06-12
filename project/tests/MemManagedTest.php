<?php
namespace LinuxDr\MemManaged\Tests;

use PHPUnit_Framework_TestCase;
use LinuxDr\MemManaged\MemManaged;
use Weakref;


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
    private $referencedObjects = array();

    public function setUp()
    {
        $this->referencedObjects = array();
    }

    private function addTrackedObject($obj)
    {
        $this->referencedObjects[] = new Weakref($obj);
    }

    private function verifyAllDeallocated()
    {
        foreach ($this->referencedObjects as $ref) {
            $this->assertFalse($ref->valid(), $ref->valid() ? get_class($ref->get()) . " should have been deallocated." : '');
        }
    }

    public function testClientCreation()
    {
        $obj = MemManagedTest_SampleClientClass::create();
        $this->assertEquals('LinuxDr\\MemManaged\\Tests\\MemManagedTest_SampleClientClass', $obj->getClass());
        $this->addTrackedObject($obj);
        $obj = MemManagedTest_SampleDerivedClass::create();
        $this->assertEquals('LinuxDr\\MemManaged\\Tests\\MemManagedTest_SampleDerivedClass', $obj->getClass());
        $this->assertEquals(array(5), $obj->getInitArgs());
        $this->addTrackedObject($obj);
        $obj = MemManagedTest_SampleDerivedClass::create(1, 2, 3);
        $this->assertEquals('LinuxDr\\MemManaged\\Tests\\MemManagedTest_SampleDerivedClass', $obj->getClass());
        $this->assertEquals(array(1, 2, 3), $obj->getInitArgs());
        $this->addTrackedObject($obj);
        unset($obj);
        $this->verifyAllDeallocated();
    }

    public function testGetReference()
    {
        $obj = MemManagedTest_SampleClientClass::create();
        $this->addTrackedObject($obj);
        $otherRef = $obj->newReference();
        $this->addTrackedObject($otherRef);
        $this->assertSame($obj->getReferencedObject(), $otherRef->getReferencedObject());
        unset($obj);
        unset($otherRef);
        $this->verifyAllDeallocated();
    }

    public function testGetReferencingObject()
    {
        $obj = MemManagedTest_SampleClientClass::create();
        $this->assertSame($obj->getReferencingObject(), $this);
        $this->addTrackedObject($obj);
        $otherObj = $obj->createAndAddReferenceTo('LinuxDr\\MemManaged\\Tests\\MemManagedTest_SampleClientClass');
        $this->assertSame($otherObj->getReferencingObject(), $obj);
        $this->addTrackedObject($otherObj);
        unset($otherObj);
        unset($obj);
        $this->verifyAllDeallocated();
    }
}

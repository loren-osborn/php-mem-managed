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

    public function tearDown()
    {
        $this->verifyAllDeallocate();
    }

    private function verifyAllDeallocate()
    {
        $weakRefs = array();
        foreach ($this->referencedObjects as $key => $obj) {
            $weakRefs[$key] = new Weakref($obj);
        }
        unset($obj);
        $this->referencedObjects = null;
        foreach ($weakRefs as $key => $ref) {
            $this->assertFalse($ref->valid(), $ref->valid() ? get_class($ref->get()) . " ($key) should have been deallocated." : '');
        }
    }

    public function testClientCreation()
    {
        $this->referencedObjects['obj1'] = MemManagedTest_SampleClientClass::create();
        $this->assertEquals('LinuxDr\\MemManaged\\Tests\\MemManagedTest_SampleClientClass', $this->referencedObjects['obj1']->getClass());
        $this->referencedObjects['obj2'] = MemManagedTest_SampleDerivedClass::create();
        $this->assertEquals('LinuxDr\\MemManaged\\Tests\\MemManagedTest_SampleDerivedClass', $this->referencedObjects['obj2']->getClass());
        $this->assertEquals(array(5), $this->referencedObjects['obj2']->getInitArgs());
        $this->referencedObjects['obj3'] = MemManagedTest_SampleDerivedClass::create(1, 2, 3);
        $this->assertEquals('LinuxDr\\MemManaged\\Tests\\MemManagedTest_SampleDerivedClass', $this->referencedObjects['obj3']->getClass());
        $this->assertEquals(array(1, 2, 3), $this->referencedObjects['obj3']->getInitArgs());
    }

    public function testNewReference()
    {
        $this->referencedObjects['obj'] = MemManagedTest_SampleClientClass::create();
        $this->referencedObjects['otherRef'] = $this->referencedObjects['obj']->newReference();
        $this->assertSame($this->referencedObjects['obj']->getReferencedObject(), $this->referencedObjects['otherRef']->getReferencedObject());
    }

    public function testGetReferencingObject()
    {
        $this->referencedObjects['obj'] = MemManagedTest_SampleClientClass::create();
        $this->assertSame($this->referencedObjects['obj']->getReferencingObject(), $this);
        $this->referencedObjects['otherObj'] = $this->referencedObjects['obj']->createAndAddReferenceTo('LinuxDr\\MemManaged\\Tests\\MemManagedTest_SampleClientClass');
        $this->assertSame($this->referencedObjects['otherObj']->getReferencingObject(), $this->referencedObjects['obj']);
    }
}

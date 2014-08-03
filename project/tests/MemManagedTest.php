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

    /*
    protected function setUp()
    {
        require_once(dirname(dirname(__FILE__)) . '/src/LinuxDr/CgmQuiz/AddBusinessDays.php');
    }

    public function getExpectedValues()
    {
        return array(
            array( // given
                'date' => '2013-08-29',
                'holiday' => '2013-09-02',
                'add' => 3,
                'expected' => '2013-09-04'),
            array( // weekday with no delta
                'date' => '2013-08-29',
                'holiday' => '2013-09-02',
                'add' => 0,
                'expected' => '2013-08-29'),
            array( // weekday skip over a weekend
                'date' => '2013-08-29',
                'holiday' => '2013-09-02',
                'add' => 1,
                'expected' => '2013-08-30'),
            array( // weekend with no delta
                'date' => '2013-08-31',
                'holiday' => '2013-10-02',
                'add' => 0,
                'expected' => '2013-09-02'),
            array( // weekend becomes monday adding 1 day
                'date' => '2013-08-31',
                'holiday' => '2013-10-02',
                'add' => 1,
                'expected' => '2013-09-03'),
            array( // few weeks ahead
                'date' => '2013-08-31',
                'holiday' => '2013-10-02',
                'add' => 11,
                'expected' => '2013-09-17'),
            array( // few weeks backwards
                'date' => '2013-08-31',
                'holiday' => '2013-10-02',
                'add' => -11,
                'expected' => '2013-08-16'),
            array( // holiday on weekend has no effect
                'date' => '2013-08-29',
                'holiday' => '2013-09-01',
                'add' => 2,
                'expected' => '2013-09-02'),
            array( // holiday in past has no effect
                'date' => '2013-08-29',
                'holiday' => '2013-08-28',
                'add' => 2,
                'expected' => '2013-09-02'),
            array( // holiday on Friday pushes us into the next week
                'date' => '2013-08-29',
                'holiday' => '2013-08-30',
                'add' => 1,
                'expected' => '2013-09-02'),
            array( // holiday on Monday pushes us back to previous week
                'date' => '2013-09-03',
                'holiday' => '2013-09-02',
                'add' => -1,
                'expected' => '2013-08-30'),
            array(
                'date' => '2013-08-29',
                'holiday' => '2013-09-02',
                'add' => 2,
                'expected' => '2013-09-03'),
            array(
                'date' => '2013-08-29',
                'holiday' => '2013-08-28',
                'add' => -1,
                'expected' => '2013-08-27'),
            array(
                'date' => '2013-08-29',
                'holiday' => '2013-08-28',
                'add' => -3,
                'expected' => '2013-08-23')
        );
    }

    / **
    * @dataProvider getExpectedValues
    * /
    public function testValues($date, $holiday, $add, $expected)
    {
        $result = \LinuxDr\CgmQuiz\addBusinessDays($date, $holiday, $add);
        $this->assertEquals($expected, $result);
    } 
    */
}

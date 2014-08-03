<?php
namespace LinuxDr\MemManaged\Tests\Internals;

use PHPUnit_Framework_TestCase;
use LinuxDr\MemManaged\Internals\DynamicallyGeneratedClass;

class DynamicallyGeneratedClassTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $newClass = new DynamicallyGeneratedClass('Foo');
        $this->assertEquals("class Foo\n{\n}\n", $newClass->getSource());
        $newClass = new DynamicallyGeneratedClass('Bar');
        $this->assertEquals("class Bar\n{\n}\n", $newClass->getSource());
        $newClass = new DynamicallyGeneratedClass('Some\\Namespace\\Name\\Baz');
        $this->assertEquals("namespace Some\\Namespace\\Name;\n\nclass Baz\n{\n}\n", $newClass->getSource());
    }

    public function extractNamespaces($symbolList)
    {
        $result = array();
        foreach ($symbolList as $sym) {
            $matches = array();
            if (preg_match("/^((?:[A-Za-z0-9_]+\\\\)*[A-Za-z0-9_]+)\\\\/", $sym, $matches) == 0) {
                $matches = array('', '');
            }
            $this->assertCount(2, $matches);
            $result[] = $matches[1];
        }
        return array_unique($result);
    }

    public function getClassPermutations($className, $seqNum = 0, $variationProbability = 0.1)
    {
        $includePermutation = (function ($variation) use ($className, $seqNum, $variationProbability) {
            $comparisonValue = hexdec(substr(md5("{className}:{$seqNum}:{$variation}"), 0, 6)) / (1 << 24);
            return ($comparisonValue <= $variationProbability);
        });
        $nameParts = explode('\\', $className);
        $namespaceSeperators = array('', '\\', "\\\\", "\\\\\\");
        $partialNames = array('');
        while (count($nameParts) > 0) {
            $nextPart = array_shift($nameParts);
            $lastPartialNames = $partialNames;
            $partialNames = array();
            foreach ($lastPartialNames as $partial) {
                foreach ($namespaceSeperators as $sep) {
                    $partialNames[] = $partial . $sep . $nextPart;
                }
            }
            $namespaceSeperators = array('\\', "\\\\", "\\\\\\");
        }
        $result = array( $partialNames[0] );
        for ($i = 1; $i < count($partialNames); $i ++) {
            if ($includePermutation($partialNames[$i]) ) {
                $result[] = $partialNames[$i];
            }
        }
        $this->assertGreaterThanOrEqual(1, count($result));
        return $result;
    }

    public function testClassPermutations()
    {
        $this->assertEquals(
            array('Foo', "\\Foo", "\\\\Foo", "\\\\\\Foo"),
            $this->getClassPermutations('Foo', 0, 1)
        );
        $this->assertEquals(
            array(
                "Foo\\Bar\\Baz",
                "Foo\\Bar\\\\Baz",
                "Foo\\Bar\\\\\\Baz",
                "Foo\\\\Bar\\Baz",
                "Foo\\\\Bar\\\\Baz",
                "Foo\\\\Bar\\\\\\Baz",
                "Foo\\\\\\Bar\\Baz",
                "Foo\\\\\\Bar\\\\Baz",
                "Foo\\\\\\Bar\\\\\\Baz",
                "\\Foo\\Bar\\Baz",
                "\\Foo\\Bar\\\\Baz",
                "\\Foo\\Bar\\\\\\Baz",
                "\\Foo\\\\Bar\\Baz",
                "\\Foo\\\\Bar\\\\Baz",
                "\\Foo\\\\Bar\\\\\\Baz",
                "\\Foo\\\\\\Bar\\Baz",
                "\\Foo\\\\\\Bar\\\\Baz",
                "\\Foo\\\\\\Bar\\\\\\Baz",
                "\\\\Foo\\Bar\\Baz",
                "\\\\Foo\\Bar\\\\Baz",
                "\\\\Foo\\Bar\\\\\\Baz",
                "\\\\Foo\\\\Bar\\Baz",
                "\\\\Foo\\\\Bar\\\\Baz",
                "\\\\Foo\\\\Bar\\\\\\Baz",
                "\\\\Foo\\\\\\Bar\\Baz",
                "\\\\Foo\\\\\\Bar\\\\Baz",
                "\\\\Foo\\\\\\Bar\\\\\\Baz",
                "\\\\\\Foo\\Bar\\Baz",
                "\\\\\\Foo\\Bar\\\\Baz",
                "\\\\\\Foo\\Bar\\\\\\Baz",
                "\\\\\\Foo\\\\Bar\\Baz",
                "\\\\\\Foo\\\\Bar\\\\Baz",
                "\\\\\\Foo\\\\Bar\\\\\\Baz",
                "\\\\\\Foo\\\\\\Bar\\Baz",
                "\\\\\\Foo\\\\\\Bar\\\\Baz",
                "\\\\\\Foo\\\\\\Bar\\\\\\Baz"
            ),
            $this->getClassPermutations('Foo\\Bar\\Baz', 0, 1)
        );
        $this->assertEquals(
            array("Foo\\Bar\\Baz"),
            $this->getClassPermutations('Foo\\Bar\\Baz', 0, 0)
        );
        $this->assertEquals(
            array("Foo\\Bar\\Baz"),
            $this->getClassPermutations('Foo\\Bar\\Baz', 0, 0.0001)
        );
        $this->assertNotEquals(
            json_encode($this->getClassPermutations('Foo\\Bar\\Baz', 1, 0.5)),
            json_encode($this->getClassPermutations('Foo\\Bar\\Baz', 0, 0.5))
        );
        foreach (array(0.1, 0.5, 0.8) as $prob) {
            $total = 0;
            $ITERATIONS = 100;
            for ( $i = 0; $i < $ITERATIONS; $i++ ) {
                $total += count($this->getClassPermutations('Foo\\Bar\\Baz', $i, $prob));
            }
            $avg = $total / $ITERATIONS;
            $expected = 1 + (((4 * 3 * 3) - 1) * $prob);
            $this->assertLessThan(0.1, abs($expected - $avg) / $expected);
        }
    }

    public function getParentDerivedClassPermutations()
    {
        $basePermutations = array(
            array('Foo', 'Bar', "class Foo extends Bar\n{\n}\n"),
            array('A\\Foo', 'Bar', "namespace A;\n\nuse Bar;\n\nclass Foo extends Bar\n{\n}\n"),
            array('Foo', 'A\\Bar', "use A\\Bar;\n\nclass Foo extends Bar\n{\n}\n"),
            array('A\\Foo', 'A\\Bar', "namespace A;\n\nclass Foo extends Bar\n{\n}\n"),
            array('A\\Foo', 'B\\Bar', "namespace A;\n\nuse B\\Bar;\n\nclass Foo extends Bar\n{\n}\n"),
            array('A\\Foo', 'B\\Foo', "namespace A;\n\nuse B\\Foo as Foo_2;\n\nclass Foo extends Foo_2\n{\n}\n")
        );
        $retVal = array();
        $namespacePermutationCount = 0;
        foreach ($basePermutations as $testCase) {
            $relevantNamespaces =
                array_values(array_filter(
                    $this->extractNamespaces(array($testCase[0], $testCase[1]))
                ));
            for ($i = 0; $i < (1 << count($relevantNamespaces)); $i++) {
                $class = $testCase[0];
                $parent = $testCase[1];
                $source = $testCase[2];
                for ($j = 0; $j < count($relevantNamespaces); $j++) {
                    if (((1 << $j) & $i) != 0) {
                        $class = preg_replace("/^(" . preg_quote($relevantNamespaces[$j]) . "\\\\)/", '\\1\\1\\1', $class);
                        $parent = preg_replace("/^(" . preg_quote($relevantNamespaces[$j]) . "\\\\)/", '\\1\\1\\1', $parent);
                        $source = preg_replace("/\\b(" . preg_quote($relevantNamespaces[$j]) . ")\\b/", "\\1\\\\\\1\\\\\\1", $source);
                    }
                }
                $namespacePermutationCount++;
                $classPermutations = $this->getClassPermutations($class, $i);
                $parentPermutations = $this->getClassPermutations($parent, $i);
                foreach ($classPermutations as $classInput) {
                    foreach ($parentPermutations as $parentInput) {
                        $retVal[] = array($classInput, $parentInput, $source);
                    }
                }
            }
        }
        $this->assertEquals(15, $namespacePermutationCount);
        $this->assertGreaterThanOrEqual(
            $namespacePermutationCount,
            count(array_unique(array_map('json_encode', $retVal)))
        );
        return $retVal;
    }

    /**
      * @dataProvider getParentDerivedClassPermutations
      */
    public function testParentClass($class, $base, $expected)
    {
        $newClass = new DynamicallyGeneratedClass($class);
        $newClass->setParentClass($base);
        $this->assertEquals($expected, $newClass->getSource());
    }

    /**
      * @dataProvider getParentDerivedClassPermutations
      */
    public function testSingleInterface($class, $base, $expected)
    {
        $newClass = new DynamicallyGeneratedClass($class);
        $newClass->addInterface($base);
        $this->assertEquals(preg_replace('/\\bextends\\b/', 'implements', $expected), $newClass->getSource());
    }
}

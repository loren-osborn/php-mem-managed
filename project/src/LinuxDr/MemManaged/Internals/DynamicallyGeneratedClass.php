<?php
namespace LinuxDr\MemManaged\Internals;

class DynamicallyGeneratedClass
{
    private $className;
    private $namespace;
    private $baseClass = null;
    private $interface = null;
    private $shortNameMap = array();
    private $symbolLookup = array();

    public function __construct($className)
    {
        $parsedClass = $this->parseSymbolName($className);
        $this->namespace = $parsedClass['namespace'];
        $this->className = $parsedClass['symbolName'];
        $this->getCleanAllocatedSymbol($this->className);
    }

    private function parseSymbolName($symbolName)
    {
        $symbolName = preg_replace("/^\\\\*" . "/", '', $symbolName);
        $symbolName = preg_replace("/\\\\{2,}/", "\\\\", $symbolName);
        $retVal = array(
            'namespace' => '',
            'bareName' => $symbolName,
            'symbolName' => $symbolName);
        $matches = array();
        if (preg_match("/^(.*)\\\\([^\\\\]+)\$/", $symbolName, $matches)) {
            $retVal['namespace'] = $matches[1];
            $retVal['bareName'] = $matches[2];
        }
        return $retVal;
    }

    private function getCleanAllocatedSymbol($symbolName)
    {
        $parsedClass = $this->parseSymbolName($symbolName);
        $shortName = $parsedClass['bareName'];
        $deferedClassToAlloc = null;
        if (
            array_key_exists($shortName, $this->shortNameMap) // &&
            // ($this->shortNameMap[$shortName] !== $parsedClass['symbolName'])
        ) {
            /*
            if ($parsedClass['namespace'] === $this->getNamespace()) {
                $deferedClassToAlloc = $this->shortNameMap[$shortName];
                unset($this->shortNameMap[$shortName]);
                unset($this->symbolLookup[$deferedClassToAlloc]);
            } else {
            */
                $counter = 2;
                /*
                while (
                    array_key_exists($shortName . '_' . $counter, $this->shortNameMap) // &&
                    // ($this->shortNameMap[$shortName . '_' . $counter] !== $parsedClass['symbolName'])
                ) {
                    $counter++;
                }
                */
                $shortName = $shortName . '_' . $counter;
            /*
            }
            */

        }
        $this->shortNameMap[$shortName] = $parsedClass['symbolName'];
        $this->symbolLookup[$parsedClass['symbolName']] = $shortName;
        /*
        if ($deferedClassToAlloc) {
            $this->getCleanAllocatedSymbol($deferedClassToAlloc);
        }
        */
        return $parsedClass['symbolName'];
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setParentClass($baseClass)
    {
        $this->baseClass = $this->getCleanAllocatedSymbol($baseClass);
    }

    public function addInterface($newInterface)
    {
        $this->interface = $this->getCleanAllocatedSymbol($newInterface);
    }

    public function getSource()
    {
        $parsedClass = $this->parseSymbolName($this->className);
        $source = '';
        if ($parsedClass['namespace'] !== '') {
            $source .= 'namespace ' . $this->getNamespace() . ";\n\n";
        }
        $useLineCount = 0;
        foreach ($this->shortNameMap as $short => $full) {
            $parsedSymName = $this->parseSymbolName($full);
            if ($parsedSymName['namespace'] !== $this->getNamespace()) {
                $useLineCount++;
                $asClause = '';
                if ($short !== $parsedSymName['bareName']) {
                    $asClause = " as $short";
                }
                $source .= "use {$full}{$asClause};\n\n";
            }
        }
        /*
        if ($useLineCount > 0) {
            $source .= "\n";
        }
        */
        $source .= "class " . $parsedClass['bareName'];
        if ($this->baseClass) {
            $source .= " extends " . $this->symbolLookup[$this->baseClass];
        }
        if ($this->interface) {
            $source .= " implements " . $this->symbolLookup[$this->interface];
        }
        $source .= "\n{\n}\n";
        return $source;
    }
}

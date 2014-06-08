<?php
namespace LinuxDr\MemManaged;

trait MemManaged {

	private $referencedBy = null;

    private function __construct($argArray)
	{
		$this->referencedBy = $this->getCallersObject(1);
		call_user_func_array(array($this, 'init'), $argArray);
	}

    public static function create()
	{
		$obj = new static(func_get_args());
		return $obj;
	}

    public function getClass()
	{
		return get_class($this);
	}

	public function newReference()
	{
		$this->referencedBy = $this->getCallersObject();
		return $this;
	}

	public function getReferencedObject()
	{
		return $this;
	}

	public function getReferencingObject()
	{
		return $this->referencedBy;
	}

    private function getCallersObject($depth = 0)
    {
        $backtrace = debug_backtrace((DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS), 3 + $depth);
        return $backtrace[2 + $depth]['object'];
    }

	/* Stubs to be overridden */
    protected function init()
    {}
}
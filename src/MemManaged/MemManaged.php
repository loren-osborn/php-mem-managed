<?php
namespace LinuxDr\MemManaged;

use Weakref;

trait MemManaged {

	private $referencedBy = null;

    private function __construct($argArray)
	{
		$this->referencedBy = new Weakref($this->getCallersObject(1));
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
		$this->referencedBy = new Weakref($this->getCallersObject());
		return $this;
	}

	public function getReferencedObject()
	{
		return $this;
	}

	public function getReferencingObject()
	{
		return $this->referencedBy->get();
	}

    private function getCallersObject($depth = 0)
    {
        $backtrace = debug_backtrace((DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS), 3 + $depth + 2);
        // var_dump("Stack frame to use: " . (2 + $depth), array_map(function ($stackFrame) {
        // 	if (array_key_exists('object', $stackFrame)) {
        // 		$stackFrame['object'] = get_class($stackFrame['object']) . ' (' . spl_object_hash($stackFrame['object']) . ')';
        // 	}
        // 	return $stackFrame;
        // }, $backtrace));
        return $backtrace[2 + $depth]['object'];
    }

	/* Stubs to be overridden */
    protected function init()
    {}
}
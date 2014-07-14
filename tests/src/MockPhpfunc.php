<?php
namespace Aura\Session;

class MockPhpfunc extends Phpfunc
{
    public $extensions = array();

    public $functions = array();

    public function __construct()
    {
        $this->extensions = get_loaded_extensions();
    }

    public function extension_loaded($name)
    {
        // for parent coverage
        $this->__call('extension_loaded', array($name));

        // for testing
        return in_array($name, $this->extensions);
    }
}

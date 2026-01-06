<?php
namespace Aura\Session;

class FakePhpfunc extends Phpfunc
{
    public $functions = array();

    public function function_exists($name)
    {
        if (isset($this->functions[$name])) {
            return $this->functions[$name];
        } else {
            return $this->__call('function_exists', array($name));
        }
    }
}

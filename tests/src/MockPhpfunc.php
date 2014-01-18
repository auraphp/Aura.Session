<?php
namespace Aura\Session;

class MockPhpfunc extends Phpfunc
{
    protected $extensions = [];
    
    public function __construct()
    {
        $this->setExtensions(get_loaded_extensions());
    }
    
    public function setExtensions(array $extensions)
    {
        $this->extensions = $extensions;
    }
    
    public function extension_loaded($name)
    {
        // for parent coverage
        $this->__call('extension_loaded', [$name]);
        
        // for testing
        return in_array($name, $this->extensions);
    }
}

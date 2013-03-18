<?php
namespace Aura\Session;

class Phpfunc
{
    public function __call($func, $args)
    {
        return call_user_func_array($func, $args);
    }
}

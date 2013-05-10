<?php
require dirname(__DIR__) . '/src.php';
return new \Aura\Session\Manager(
    new \Aura\Session\SegmentFactory,
    new \Aura\Session\CsrfTokenFactory(
        new \Aura\Session\Randval(
            new \Aura\Session\Phpfunc
        )
    ),
    $_COOKIE
);

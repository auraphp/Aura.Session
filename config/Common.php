<?php
namespace Aura\Session\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Common extends Config
{
    public function define(Container $di)
    {
        /**
         * Services
         */
        $di->set('session', $di->lazyNew('Aura\Session\Session'));

        /**
         * Aura\Session\CsrfTokenFactory
         */
        $di->params['Aura\Session\CsrfTokenFactory']['randval'] = $di->lazyNew('Aura\Session\Randval');

        /**
         * Aura\Session\Session
         */
        $di->params['Aura\Session\Session'] = [
            'segment_factory' => $di->lazyNew('Aura\Session\SegmentFactory'),
            'csrf_token_factory' => $di->lazyNew('Aura\Session\CsrfTokenFactory'),
            'cookies' => $_COOKIE,
        ];

        /**
         * Aura\Session\Randval
         */
        $di->params['Aura\Session\Randval']['phpfunc'] = $di->lazyNew('Aura\Session\Phpfunc');

        /**
         * Aura\Session\Segment
         */
        $di->params['Aura\Session\Segment'] = [
            'session' => $di->lazyGet('session'),
        ];
    }
}

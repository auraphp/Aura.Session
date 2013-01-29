<?php
/**
 * Loader
 */
$loader->add('Aura\Session\\', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src');

/**
 * Services
 */
$di->set('session_manager', $di->lazyNew('Aura\Session\Manager'));

/**
 * Aura\Session\Manager
 */
$di->params['Aura\Session\Manager'] = [
    'segment_factory' => $di->lazyNew('Aura\Session\SegmentFactory'),
    'csrf_token_factory' => $di->lazyNew('Aura\Session\CsrfTokenFactory'),
    'cookies' => $_COOKIE,
];

/**
 * Aura\Session\Segment
 */
$di->params['Aura\Session\Segment'] = [
    'session' => $di->lazyGet('session_manager'),
];

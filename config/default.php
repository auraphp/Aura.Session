<?php
/**
 * Package prefix for autoloader.
 */
$loader->add('Aura\Session\\', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src');

/**
 * Constructor params.
 */
$di->params['Aura\Session\Manager'] = [
    'segment_factory' => $di->lazyNew('Aura\Session\SegmentFactory'),
    'csrf_token_factory' => $di->lazyNew('Aura\Session\CsrfTokenFactory'),
];

/**
 * Dependency services.
 */
$di->set('session_manager', $di->lazyNew('Aura\Session\Manager'));

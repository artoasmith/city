<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#checking-symfony-application-configuration-and-setup
// for more information
//umask(0000);

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !(in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1')) || php_sapi_name() === 'cli-server')
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
Debug::enable();

$request = Request::createFromGlobals();

if(strpos($request->getRequestUri(),'/app_dev.php/?_escaped_fragment_=/')===0) {
    $_SERVER['REQUEST_URI'] = '/app_dev.php'.$request->query->get('_escaped_fragment_');
    $request = Request::createFromGlobals();
}

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

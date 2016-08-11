<?php

use T3G\Elasticorn\Commands\InitCommand;
use T3G\Elasticorn\Commands\RemapCommand;


// dependency injection initialization
$di = new \T3G\Elasticorn\Bootstrap\DependencyInjectionContainer();
$container = $di->init();
unset($di);


// application
$application = new \Symfony\Component\Console\Application();
$application->add(new InitCommand());
$application->add(new RemapCommand());
$application->run();
<?php

use T3G\Elasticorn\Commands\Mapping\CompareCommand;
use T3G\Elasticorn\Commands\Index\InitCommand;
use T3G\Elasticorn\Commands\Index\RemapCommand;
use T3G\Elasticorn\Commands\Mapping\ShowCommand;


// dependency injection initialization
$di = new \T3G\Elasticorn\Bootstrap\DependencyInjectionContainer();
$container = $di->init();
unset($di);


// application
$application = new \Symfony\Component\Console\Application();
$application->add(new InitCommand());
$application->add(new RemapCommand());
$application->add(new CompareCommand());
$application->add(new ShowCommand());
$application->run();
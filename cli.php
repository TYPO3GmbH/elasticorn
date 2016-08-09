<?php
declare(strict_types = 1);

require_once ("vendor/autoload.php");

define('APPLICATION_PATH', __DIR__ . '/');

$application = new \Symfony\Component\Console\Application();
$application->add(new \T3G\Elasticorn\Commands\InitCommand());
$application->add(new \T3G\Elasticorn\Commands\RemapCommand());
$application->run();
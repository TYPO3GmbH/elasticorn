<?php

declare(strict_types=1);

/*
 * This file is part of the package t3g/elasticorn.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Dotenv\Dotenv;
use T3G\Elasticorn\Commands\Index\CornifyCommand;
use T3G\Elasticorn\Commands\Index\InitCommand;
use T3G\Elasticorn\Commands\Index\RemapCommand;
use T3G\Elasticorn\Commands\Mapping\CompareCommand;
use T3G\Elasticorn\Commands\Mapping\ShowCommand;
use T3G\Elasticorn\Commands\Self\RollbackCommand;
use T3G\Elasticorn\Commands\Self\UpdateCommand;
use T3G\Elasticorn\Commands\Type\TruncateCommand;

// env config
// Determine the .env file in package directory ($baseBath === __DIR__) and getcwd()
// this prevent path errors in case of global composer installation and package requirement
foreach ([$basePath, getcwd()] as $directory) {
    if (file_exists($directory . DIRECTORY_SEPARATOR . '.env.dist')) {
        $dotenvDist = new Dotenv($directory, '.env.dist');
        $dotenvDist->load();
    }
    if (file_exists($directory . DIRECTORY_SEPARATOR . '.env')) {
        $dotenv = new Dotenv($directory, '.env');
        $dotenv->overload();
    }
    if (file_exists($directory . DIRECTORY_SEPARATOR . '.env.local')) {
        $dotenv = new Dotenv($directory, '.env.local');
        $dotenv->overload();
    }
}

// dependency injection initialization
$di = new \T3G\Elasticorn\Bootstrap\DependencyInjectionContainer();
$container = $di->init();
unset($di);

// application
$application = new \Symfony\Component\Console\Application();

// commands
$application->add(new InitCommand());
$application->add(new RemapCommand());
$application->add(new CompareCommand());
$application->add(new ShowCommand());
$application->add(new CornifyCommand());
$application->add(new TruncateCommand());

if (true === $phar) {
    $application->add(new UpdateCommand());
    $application->add(new RollbackCommand());
}

$application->setName(
    <<<ASCIIART
                    Elasticorn!
                             \
                              \
                               \\
                                \\
                                 >\/7
                             _.-(6'  \
                            (=___._/` \
                                 )  \ |
                                /   / |
                               /    > /
                              j    < _\
                          _.-' :      ``.
                          \ r=._\        `.
                         <`\\_  \         .`-.
                          \ r-7  `-. ._  ' .  `\
                           \`,      `-.`7  7)   )
                            \/         \|  \'  / `-._
                                       ||    .'
                                        \\  (
                                         >\  >
                                     ,.-' >.'
                                    <.'_.''
                                      <'
ASCIIART
);
$application->run();

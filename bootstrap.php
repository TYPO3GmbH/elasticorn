<?php
declare(strict_types = 1);

use T3G\Elasticorn\Commands\Mapping\CompareCommand;
use T3G\Elasticorn\Commands\Index\InitCommand;
use T3G\Elasticorn\Commands\Index\RemapCommand;
use T3G\Elasticorn\Commands\Mapping\ShowCommand;

// env config
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = new Dotenv\Dotenv(__DIR__);
    $dotenv->load();
}

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
<?php
declare(strict_types = 1);

use T3G\Elasticorn\Commands\Index\CornifyCommand;
use T3G\Elasticorn\Commands\Mapping\CompareCommand;
use T3G\Elasticorn\Commands\Index\InitCommand;
use T3G\Elasticorn\Commands\Index\RemapCommand;
use T3G\Elasticorn\Commands\Mapping\ShowCommand;
use T3G\Elasticorn\Commands\Self\RollbackCommand;
use T3G\Elasticorn\Commands\Self\UpdateCommand;

// env config
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = new Dotenv\Dotenv($basePath);
    $dotenv->load();
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

if(true === $phar) {
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

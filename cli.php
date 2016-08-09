<?php
declare(strict_types = 1);

require_once ("vendor/autoload.php");

define('APPLICATION_PATH', __DIR__ . '/');

$elasticaTest = new \T3G\Elasticorn\IndexUtility();
$elasticaTest->test();
$elasticaTest->applyMapping();
$elasticaTest->addDocument();

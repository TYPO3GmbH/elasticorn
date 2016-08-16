#!/usr/bin/env php
<?php
declare(strict_types = 1);

$phar = false;
$basePath = __DIR__;
if (method_exists(Phar::class, 'running')) {
    $pharPath = Phar::running(false);
    if (!empty($pharPath)) {
        $phar = true;
        $basePath = dirname($pharPath);
    }
}

require_once $basePath . '/vendor/autoload.php';
require_once $basePath . '/bootstrap.php';
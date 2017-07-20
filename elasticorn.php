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

// Determine the autoload.php file this prevent path errors
// in case of global composer installation and package requirement
$autoloadFile = 'vendor/autoload.php';
foreach ([__DIR__ . '/../../autoload.php', __DIR__ . '/../vendor/autoload.php', __DIR__ . '/vendor/autoload.php'] as $file) {
    if (file_exists($file)) {
        $autoloadFile = $file;
        break;
    }
}

// Determine the bootstrap.php file this prevent path errors
// in case of global composer installation and package requirement
$bootstrapFile = 'bootstrap.php';
foreach ([__DIR__ . '/../../bootstrap.php', __DIR__ . '/../bootstrap.php', __DIR__ . '/bootstrap.php'] as $file) {
    if (file_exists($file)) {
        $bootstrapFile = $file;
        break;
    }
}

require_once $autoloadFile;
require_once $bootstrapFile;

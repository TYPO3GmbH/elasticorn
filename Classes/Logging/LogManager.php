<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Logging;


use Psr\Log\LoggerInterface;

class LogManager
{
    protected static $logger;

    public static function setLogger(LoggerInterface $logger)
    {
        self::$logger = $logger;
    }

    public static function getLogger()
    {
        return self::$logger;
    }
}
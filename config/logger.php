<?php

//setup log level from env

switch(env('APP_LOG_LEVEL','debug')) {
    case 'debug':
        $logLevel = \Monolog\Level::Debug;
        break;
    case 'info':
        $logLevel = \Monolog\Level::Info;
        break;
    case 'notice':
        $logLevel = \Monolog\Level::Notice;
        break;
    case 'warning':
        $logLevel = \Monolog\Level::Warning;
        break;
    case 'error':
        $logLevel = \Monolog\Level::Error;
        break;
    case 'critical':
        $logLevel = \Monolog\Level::Critical;
        break;
    case 'alert':
        $logLevel = \Monolog\Level::Alert;
        break;
    case 'emergency':
        $logLevel = \Monolog\Level::Emergency;
        break;
    default:
        $logLevel = \Monolog\Level::Debug;
}

//setup log with BetterStack
$logger = new \Monolog\Logger(env('APP_NAME','PHP-HTTP-PROXY'));

// log rotation
$logPath = env('APP_LOG_PATH', __DIR__.'/../storage/logs/log.log');

$output = "%datetime% > %level_name% > %message% %context% %extra%\n";

// finally, create a formatter
$formatter = new \Monolog\Formatter\LineFormatter($output);

$rotatingFileHandler = new \Monolog\Handler\RotatingFileHandler($logPath, (int) env('APP_LOG_ROTATION', 7), $logLevel);
$rotatingFileHandler->setFormatter($formatter); 

$formatter->setJsonPrettyPrint(true);

$formatter->includeStacktraces(true);

$logger->pushHandler($rotatingFileHandler);

// log on Logtail only in prod
if(env('APP_ENV') == 'prod') {
    $logger->pushHandler(new \Logtail\Monolog\LogtailHandler(env('LOGTAIL_API_KEY'), $logLevel));
}
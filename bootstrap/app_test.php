<?php
// Autoload Composer dependencies

use \Illuminate\Support\Carbon as Date;
use Illuminate\Support\Facades\Facade;
use Illuminate\Validation\Validator;
use Monolog\Level;

require_once __DIR__ . '/../vendor/autoload.php';

// Set up your application configuration
// Initialize slim application
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Crea un'istanza del gestore del database (Capsule)
$capsule = new \Illuminate\Database\Capsule\Manager();

// Aggiungi la configurazione del database al Capsule
$connections = require_once __DIR__.'/../config/database.php';
$capsule->addConnection($connections['mysql']);

// Esegui il boot del Capsule
$capsule->bootEloquent();
$capsule->setAsGlobal();

//setup log level from env
switch(env('APP_LOG_LEVEL','debug')) {
    case 'debug':
        $logLevel = Level::Debug;
        break;
    case 'info':
        $logLevel = Level::Info;
        break;
    case 'notice':
        $logLevel = Level::Notice;
        break;
    case 'warning':
        $logLevel = Level::Warning;
        break;
    case 'error':
        $logLevel = Level::Error;
        break;
    case 'critical':
        $logLevel = Level::Critical;
        break;
    case 'alert':
        $logLevel = Level::Alert;
        break;
    case 'emergency':
        $logLevel = Level::Emergency;
        break;
    default:
        $logLevel = Level::Debug;
}


// config cahce
$fs = new Illuminate\Filesystem\Filesystem();
$store = new Illuminate\Cache\FileStore($fs, env('CACHE_PATH', __DIR__ . '/../storage/cache'));
$cache = new Illuminate\Cache\Repository($store);

// validator laravel
$validator = new Validator(
    new Illuminate\Translation\Translator(
        new Illuminate\Translation\ArrayLoader(),
        'en'
    ),
    [],
    []
);
// AWS Cognito client
$awsCognitoClient = new \Budgetcontrol\Test\Libs\AwsCognitoClient();

// Set up the logger
require_once __DIR__ . '/../config/logger.php';

/** mail configuration */
require_once __DIR__ . '/../config/mail.php';

// Set up the Facade application
Facade::setFacadeApplication([
    'log' => $logger,
    'date' => new Date(),
    'cache' => $cache,
    'validator' => $validator,
    'aws-cognito-client' => $awsCognitoClient,
    'mail' => $mail
]);

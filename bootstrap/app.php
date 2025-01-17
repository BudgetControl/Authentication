<?php
// Autoload Composer dependencies

use \Illuminate\Support\Carbon as Date;
use Illuminate\Support\Facades\Facade;
use Illuminate\Validation\Validator;

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

// config cahce
require_once __DIR__ . '/../config/cache.php';

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
require_once __DIR__ . '/../config/aws-cognito.php';

// Set up the logger
require_once __DIR__ . '/../config/logger.php';

/** mail configuration */
require_once __DIR__ . '/../config/mail.php';

// Set up cryptable facade
require_once __DIR__ . '/../config/cryptable.php';

/** Budget Control connector */
require_once __DIR__ . '/../config/bc-connector.php';

// Set up the Facade application
Facade::setFacadeApplication([
    'log' => $logger,
    'date' => new Date(),
    'cache' => $cache,
    'validator' => $validator,
    'aws-cognito-client' => $awsCognitoClient,
    'mail' => $mail,
    'bc-connector' => $connector,
    'crypt' => $crypt,
]);

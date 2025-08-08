<?php

require_once __DIR__ . "/../bootstrap/app.php";

if (($_SERVER['HTTP_X_API_SECRET'] !== env('SECRET_KEY')) && env('APP_ENV') !== 'testing') {
    http_response_code(403);
    Log::warning('Access denied for IP: ' . $_SERVER['REMOTE_ADDR'] . ' - Invalid API secret');
    echo 'Access Denied - You are not allowed to access this resource.';
    exit;
}

$app = \Slim\Factory\AppFactory::create();

/**
 * The routing middleware should be added earlier than the ErrorMiddleware
 * Otherwise exceptions thrown from it will not be handled by the middleware
 */
require_once __DIR__ . "/../config/middleware.php";

require_once __DIR__ . "/../routes/api.php";

// Run app
$app->run();

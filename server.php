<?php

declare(strict_types=1);

use App\Application;
use App\Standalone\ResponseEmitter;
use Slim\Factory\ServerRequestCreatorFactory;

require __DIR__ . '/vendor/autoload.php';

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Create App
$application = new Application();
$application->initialize('prod', true);

// Run App & Emit Response
$response = $application->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);

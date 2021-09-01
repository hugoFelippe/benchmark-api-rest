<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\App;
use Slim\Factory\AppFactory;

return function (): App {
    // Instantiate PHP-DI ContainerBuilder
    $containerBuilder = new ContainerBuilder();

    // Set up settings
    $settings = require(__DIR__ . '/settings.php');
    $settings($containerBuilder);

    // Set up dependencies
    $dependencies = require(__DIR__ . '/dependencies.php');
    $dependencies($containerBuilder);

    // Set up repositories
    $repositories = require(__DIR__ . '/repositories.php');
    $repositories($containerBuilder);

    // Build PHP-DI Container instance
    $container = $containerBuilder->build();

    // Instantiate the app
    AppFactory::setContainer($container);
    $app = AppFactory::create();

    // Register middleware
    $middleware = require __DIR__ . '/middleware.php';
    $middleware($app);

    // Register routes
    $routes = require __DIR__ . '/routes.php';
    $routes($app);

    /** @var SettingsInterface $settings */
    // $settings = $container->get(SettingsInterface::class);

    // Add Routing Middleware
    $app->addRoutingMiddleware();

    // Add Error Middleware
    // $displayErrorDetails = $settings->get('displayErrorDetails');
    // $logError = $settings->get('logError');
    // $logErrorDetails = $settings->get('logErrorDetails');

    // $errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, $logError, $logErrorDetails);
    // $errorMiddleware->setDefaultErrorHandler($errorHandler);
    return $app;
};

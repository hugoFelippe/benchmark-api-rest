<?php

namespace App;

use App\Dependencies\Logger;
use App\Handlers\HttpErrorHandler;
use DI\Container;
use DI\ContainerBuilder;
use PHPPM\Bootstraps\ApplicationEnvironmentAwareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Valitron\Validator;

class Application implements ApplicationEnvironmentAwareInterface
{
    /**
     * @var App
     */
    public $app;

    /**
     * @var Container
     */
    public $container;

    public $environment;

    public $debug;

    public $routes;

    public $settings;

    public $errorMiddleware;

    public function __construct()
    {
        Validator::langDir(__DIR__.'/vendor/vlucas/valitron/lang/');
        Validator::lang('pt-br');
        
        $this->settings = new Settings();
        $this->logger = new Logger($this->settings);

        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            SettingsInterface::class => $this->settings,
            LoggerInterface::class => $this->logger->get()
        ]);

        $this->container = $builder->build();

        AppFactory::setContainer($this->container);
        
        $this->app = AppFactory::create();
        $this->routes = new Routes($this->app);
        $this->app->addRoutingMiddleware();
    }

    public function initialize($appenv, $debug)
    {
        $this->debug = $debug;
        $this->environment = $appenv;

        $this->errorMiddleware = $this->app->addErrorMiddleware(
            $this->settings->get('slim.error.display_details'),
            $this->settings->get('slim.error.log'),
            $this->settings->get('slim.error.log_details')
        );
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->errorMiddleware->setDefaultErrorHandler(
            new HttpErrorHandler(
                $this->app->getCallableResolver(),
                $this->app->getResponseFactory(),
                $this->container->get(LoggerInterface::class)
            )
        );

        return $this->app->handle($request);
    }
}

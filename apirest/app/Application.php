<?php

namespace App;

use App\Dependencies\Logger;
use DI\Container;
use DI\ContainerBuilder;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;

class Application
{
    /**
     * @var App
     */
    public $app;

    /**
     * @var Container
     */
    public $container;

    public function __construct()
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions(Application::dependencies());
        $builder->addDefinitions(Application::repositories());

        $this->container = $builder->build();

        AppFactory::setContainer($this->container);
        $this->app = AppFactory::create();

        
    }

    public static function dependencies(): array
    {
        return [
            SettingsInterface::class => Settings::class,
            LoggerInterface::class => Logger::class
        ];
    }

    public static function repositories(): array
    {
        return [];
    }

    public static function initialize(): ?Application
    {
        return new Application();
    }
}

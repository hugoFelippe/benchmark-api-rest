<?php

namespace App\PPM;

use PHPPM\Bootstraps\ApplicationEnvironmentAwareInterface;
use Slim\App;
use Slim\Psr7\Request;
use Valitron\Validator;

/**
 * A default bootstrap for the Symfony framework
 */
class Bootstrap implements ApplicationEnvironmentAwareInterface
{
    /**
     * @var string|null The application environment
     */
    protected $appenv;

    /**
     * @var boolean
     */
    protected $debug;

    /**
     * @var App
     */
    protected $app;

    /**
     * Instantiate the bootstrap, storing the $appenv
     *
     * @param string $appenv
     * @param boolean $debug
     */
    public function initialize($appenv, $debug)
    {
        $this->appenv = $appenv;
        $this->debug = $debug;

        Validator::langDir(__DIR__.'/vendor/vlucas/valitron/lang/');
        Validator::lang('pt-br');

        $this->app = $this->createSlimApp();
    }

    public function getApp(): ?App
    {
        return $this->app;
    }

    protected function createSlimApp(): App
    {
        $app = require __DIR__ . '/app/app.php';
        $app = $app();

        return $app;
    }

    public function flush(Request $slimRequest)
    {
        // $container = $this->app->getContainer();

        // unset($container['request']);
        // unset($container['environment']);

        // // Reset app request instance
        // $container['request'] = function ($c) use ($slimRequest) {
        //     return $slimRequest;
        // };

        // // Reset app environment instance
        // $container['environment'] = function ($c) use ($slimRequest) {
        //     return new Environment($slimRequest->getServerParams());
        // };

        // // Reset cached route args
        // $routes = $container['router']->getRoutes();
        // foreach ($routes as $route) {
        //     $route->setArguments([]);
        // } 
    }
}

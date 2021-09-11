<?php

namespace App\PPM;

use App\Application;
use PHPPM\Bridges\BridgeInterface;
use Psr\Http\Message\{
    ServerRequestInterface,
    ResponseInterface
};

class Adapter implements BridgeInterface
{
    /**
     * @var Application
     */
    protected $application;

    public function bootstrap($appBootstrap, $appenv, $debug)
    {
        $this->application = new Application();
        $this->application->initialize($appenv, $debug);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->application->handle($request);
    }
}
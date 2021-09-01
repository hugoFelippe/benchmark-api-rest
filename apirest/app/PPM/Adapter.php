<?php

namespace App\PPM;

use PHPPM\{
    Bootstraps\BootstrapInterface,
    Bridges\BridgeInterface
};
use Psr\Http\Message\{
    ServerRequestInterface,
    ResponseInterface
};
use Slim\Psr7\{
    Headers as Psr7Headers,
    Request as Psr7Request,
    Response as Psr7Response
};

class Adapter implements BridgeInterface
{
    /**
     * Slim application instance
     *
     * @var \Slim\App
     */
    protected $app;

    /**
     * @var BootstrapInterface
     */
    protected $bootstrap;

    public function bootstrap($appBootstrap, $appenv, $debug)
    {
        $this->bootstrap = new Bootstrap();
        $this->bootstrap->initialize($appenv, $debug);
        $this->app = $this->bootstrap->getApp();
    }

    
    protected function mapRequest(ServerRequestInterface $psrRequest): Psr7Request
    {
        $_COOKIE = [];

        foreach ($psrRequest->getHeader('Cookie') as $cookieHeader) {
            $cookies = explode(';', $cookieHeader);
            foreach ($cookies as $cookie) {
                if (strpos($cookie, '=') == false) {
                    continue;
                }
                list($name, $value) = explode('=', trim($cookie));
                $_COOKIE[$name] = $value;
                if ($name === session_name()) {
                    session_id($value);
                }
            }
        }

        return new Psr7Request(
            $psrRequest->getMethod(),
            $psrRequest->getUri(),
            new Psr7Headers($psrRequest->getHeaders()),
            $psrRequest->getCookieParams(),
            $psrRequest->getServerParams(),
            $psrRequest->getBody(),
            $psrRequest->getUploadedFiles()
        );
    }

    protected function mapResponse(ResponseInterface $slimResponse)
    {
        $nativeHeaders = [];
        foreach (headers_list() as $header) {
            if (false !== $pos = strpos($header, ':')) {
                $name = substr($header, 0, $pos);
                $value = trim(substr($header, $pos + 1));
                if (isset($nativeHeaders[$name])) {
                    if (!is_array($nativeHeaders[$name])) {
                        $nativeHeaders[$name] = [$nativeHeaders[$name]];
                    }
                    $nativeHeaders[$name][] = $value;
                } else {
                    $nativeHeaders[$name] = $value;
                }
            }
        }

        session_unset();
        unset($_SESSION);

        header_remove();

        $headers = array_merge($nativeHeaders, $slimResponse->getHeaders());

        return new Psr7Response(
            $slimResponse->getStatusCode(),
            new Psr7Headers($headers),
            $slimResponse->getBody()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $slimRequest = $this->mapRequest($request);

        $this->bootstrap->flush($slimRequest);

        $slimResponse = $this->app->handle($slimRequest, new Psr7Response());

        return $this->mapResponse($slimResponse);
    }
}
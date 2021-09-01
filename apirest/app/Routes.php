<?php

namespace App;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

class Routes
{
    public function __construct(App $app)
    {
        $app->options('/{routes:.*}', function (Request $request, Response $response) {
            return $response;
        });
    
        $app->get('/', function (Request $request, Response $response) {
            $response->getBody()->write("Hello World");
            return $response;
        });
    }
}
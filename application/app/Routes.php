<?php

namespace App;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Psr7\Factory\StreamFactory;

class Routes
{
    public function __construct(App $app)
    {
        $app->options('/{routes:.*}', function (Request $request, Response $response) {
            return $response;
        });

        $sleep = function (Request $request, Response $response) {
            sleep(2.5);
            $response->getBody()->write('{"Hello": "World"}');
            return $response;
        };

        $basic = function (Request $request, Response $response) {
            sleep(0.5);
            $response->getBody()->write('{"Hello": "World"}');
            return $response;
        };

        $hashSnefru = function (Request $request, Response $response) {
            $password = hash('snefru256', random_bytes(500));

            $response->getBody()->write("{\"Hello\": \"$password\"}");
            return $response;
        };

        $hashGhost = function (Request $request, Response $response) {
            $password = hash('gost-crypto', random_bytes(500));

            $response->getBody()->write("{\"Hello\": \"$password\"}");
            return $response;
        };

        $stream = function (Request $request, Response $response) {
            $path = __DIR__ . '/../data/1000_sales_records.csv';

            $streamFactory = new StreamFactory();
            $stream = $streamFactory->createStreamFromFile($path);

            $disposition = 'inline';
            $length = filesize($path);
            $type = mime_content_type($path);

            return $response
                ->withHeader('Content-Disposition', $disposition)
                ->withHeader('Content-Length', $length)
                ->withHeader('Content-Type', $type)
                ->withHeader('Accept-Ranges', 'bytes')
                ->withStatus(200)
                ->withBody($stream);
        };

        $bigStream = function (Request $request, Response $response) {
            $path = __DIR__ . '/../data/10000_sales_records.csv';

            $streamFactory = new StreamFactory();
            $stream = $streamFactory->createStreamFromFile($path);

            $disposition = 'inline';
            $length = filesize($path);
            $type = mime_content_type($path);

            return $response
                ->withHeader('Content-Disposition', $disposition)
                ->withHeader('Content-Length', $length)
                ->withHeader('Content-Type', $type)
                ->withHeader('Accept-Ranges', 'bytes')
                ->withStatus(200)
                ->withBody($stream);
        };

        $list = [
            $sleep,
            $basic,
            $hashSnefru,
            $hashGhost,
            $stream,
            $bigStream
        ];

        $app->get('/sleep', $sleep);
        $app->get('/basic', $basic);
        $app->get('/hash-snefru', $hashSnefru);
        $app->get('/hash-ghost', $hashGhost);
        $app->get('/stream', $stream);
        $app->get('/big-stream', $bigStream);
        $app->get('/random', function (Request $request, Response $response) use ($list) {
            return array_rand($list, 1)($request, $response);
        });
    }
}

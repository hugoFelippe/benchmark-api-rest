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
            $response->getBody()->write("Hello World 2");
            return $response;
        });


        // $app->post('/aws/sync/{file_id:\d+}/', SyncAction::class);
        // $app->get('/test/', TestAction::class);
        // $app->get('/logs.txt', LogAction::class);

        // $app->group('/uploads/', function (Group $group) {
        //     $group->get('{path:[^(user|usuario)].*}', StreamAction::class)
        //         ->add(PublicRoute::class);

        //     $group->get('{path:.*}', StreamAction::class);
        // });

        // $app->group('/', function (Group $group) {
        //     $group->get('', IssueTokenAction::class);

        //     $group->post('rename/', RenameAction::class);

        //     $group->post('', UploadAction::class)
        //         ->addMiddleware(new Validation([
        //             'required' => ['categoryId', 'files']
        //         ]));
        // })->add(PrivateRoute::class);
    }
}

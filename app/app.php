<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = require __DIR__ . '/bootstrap.php';

// Stock routes
$app->get('/cms{uri}', function (\Silex\Application $app, Request $request) {
    return file_get_contents(dirname(__DIR__) . '/public_html/cms/index.html');
})->assert('uri', '.+');
$app->post('/api/login', 'APICMS\Controller\AuthController::login');
$app->post('/api/logout', 'APICMS\Controller\AuthController::logout');
$app->post('/api/forgot', 'APICMS\Controller\AuthController::forgot');
$app->post('/api/reset', 'APICMS\Controller\AuthController::reset');

// Stock CRUD operations
foreach (['user', 'role', 'entity', 'field'] as $single) {
    $capital = ucfirst($single);
    $app->get("/api/{$single}", "APICMS\\Controller\\{$capital}Controller::getList");
    $app->post("/api/{$single}", "APICMS\\Controller\\{$capital}Controller::post");
    $app->match("/api/{$single}/{id}", "APICMS\\Controller\\{$capital}Controller::singleRouter")
        ->assert('id', '\d+')
        ->method('GET|PUT|DELETE');
}


$app->before(function (Request $request, \Silex\Application $app) {
    // Security
    $secureRoutes = [ // TODO: get secure routes list from config
        '/',
        '/admin'
    ];
    if (in_array($request->getRequestUri(), $secureRoutes)) {
        $token = $request->headers->get('X-Auth-Token', false);
        if ($token !== false) {
            $usersRepo = new \APICMS\QueryRepository\UserQueryRepository($app['db']);
            $user = $usersRepo->getUserByToken($token);
            if ($user) {
                $app['user'] = $user;
            } else {
                throw new \APICMS\Exception\RequiresAuthenticationException('Invalid Auth Token');
            }
        } else {
            throw new \APICMS\Exception\RequiresAuthenticationException('Invalid Auth Token');
        }
    }

    // REST API or Web App?
    if (strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

// 404 handling
$app->error(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) use ($app) {
    if (!$app['debug']) {
        return Response::create(\APICMS\Controller\AbstractEntityController::NOT_FOUND_MSG, 404);
    }
});

// Authentication Error Handling
$app->error(function (\APICMS\Exception\RequiresAuthenticationException $e, $code) {
    return new \Symfony\Component\HttpFoundation\JsonResponse([
        'status' => 'error',
        'message' => $e->getMessage()
    ], $code);
});


// Enabling CORS
$app->after(function (Request $request, Response $response) {
    $response->headers->set('Access-Control-Allow-Origin', '*'); // TODO: get this setting from config, encourage strict rules
});

return $app;
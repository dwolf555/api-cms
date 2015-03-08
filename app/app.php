<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = require __DIR__ . '/bootstrap.php';

// Routes
$app->post('/login', 'APICMS\Controller\AuthController::login');
$app->post('/logout', 'APICMS\Controller\AuthController::logout');
$app->post('/forgot', 'APICMS\Controller\AuthController::forgot');
$app->post('/reset', 'APICMS\Controller\AuthController::reset');

$app->get('/users', 'APICMS\Controller\UserController::getList');
$app->post('/users', 'APICMS\Controller\UserController::post');
$app->match('/users/{userId}', 'APICMS\Controller\UserController::singleRoute')
    ->assert('userId', '\d+')
    ->method('GET|PUT|DELETE');

// Security
$secureRoutes = [ // TODO: get secure routes list from config
    '/',
    '/admin'
];
$app->before(function (Request $request, \Silex\Application $app) use ($secureRoutes) {
    if (in_array($request->getRequestUri(), $secureRoutes)) {
        $token = $request->headers->get('X-Auth-Token', false);
        if ($token !== false) {
            $usersRepo = new \APICMS\QueryRepository\UserQueryRepository($app['db']);
            $user = $usersRepo->getUserByToken($token);
            if ($user) {
                $app['user'] = $user;
                return;
            }
        }
        throw new \APICMS\Exception\RequiresAuthenticationException('Invalid Auth Token');
    }
});
$app->error(function (\APICMS\Exception\RequiresAuthenticationException $e, $code) {
    return new \Symfony\Component\HttpFoundation\JsonResponse([
        'status' => 'error',
        'message' => $e->getMessage()
    ], $code);
});


// Accepting JSON Body
$app->before(function (Request $request) {
    if (strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});


// Enabling CORS
$app->after(function (Request $request, Response $response) {
    $response->headers->set('Access-Control-Allow-Origin', '*'); // TODO: get this setting from config, encourage strict rules
});

return $app;
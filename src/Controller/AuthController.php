<?php
/**
 * User: danielwolf
 * Date: 3/5/15
 * Time: 3:56 PM
 */

namespace APICMS\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class AuthController
{

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function login(Application $app, Request $request)
    {
        $user = [];
        return $app->json(
            [
                'result' => 'success',
                'data'   => [
                    'user' => $user
                ]
            ],
            200
        );
        //todo
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function logout(Application $app, Request $request)
    {
        $user = [];
        return $app->json(
            [
                'result' => 'success',
                'data'   => [
                    'user' => $user
                ]
            ],
            200
        );
        //todo
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function forgot(Application $app, Request $request)
    {
        $user = [];
        return $app->json(
            [
                'result' => 'success',
                'data'   => [
                    'user' => $user
                ]
            ],
            200
        );
        // todo
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function reset(Application $app, Request $request)
    {
        $user = [];
        return $app->json(
            [
                'result' => 'success',
                'data'   => [
                    'user' => $user
                ]
            ],
            200
        );
        // todo
    }
}
<?php
/**
 * User: danielwolf
 * Date: 3/5/15
 * Time: 3:56 PM
 */

namespace APICMS\Controller;

use Rhumsaa\Uuid\Uuid;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

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
        $input = $request->request->all();
        // validation
        $inputConstraints = new Assert\Collection([
            'email' => new Assert\Email(),
            'password' => [
                new Assert\Length(['min' => 8])
            ]
        ]);
        $errors = $app['validator']->validateValue($input, $inputConstraints);

        if (count($errors) > 0) {
            $errorArray = [];
            foreach ($errors as $e) {
                $errorArray[$e->getPropertyPath()] = $e->getMessage();
            }
            return $this->jsonResponse($errorArray, 400);
        }

        $userQuery = $app['db']->createQueryBuilder()
            ->select('u.id, u.email, u.password')
            ->from('users', 'u')
            ->where('u.email = :email')
            ->setParameters([
                'email' => $input['email'],
            ]);
        $user = $app['db']->fetchAssoc($userQuery->getSQL(), $userQuery->getParameters());

        // if email not found
        if ($user === false) {
            // todo this needs to live somewhere it can be accessed for $app->before and routes
            return JsonResponse::create([
                'code' => 400,
                'data' => [
                    'message' => 'Invalid credentials.'
                ]
            ], 400);

        }

        // password match check
        if ($app['encoder.bcrypt']->isPasswordValid($user['password'], $input['password'], null) === false) {
            // todo this needs to live somewhere it can be accessed for $app->before and routes
            return JsonResponse::create([
                'code' => 400,
                'data' => [
                    'message' => 'Invalid credentials.'
                ]
            ], 400);
        }

        // create token, send it back
        $token = Uuid::uuid4()->toString();
        $app['db']->insert('tokens', [
            'user_id' => $user['id'],
            'token' => $token
        ]);
        // todo expire tokens based on settings?

        unset($user['password']);
        return $app->json(
            [
                'result' => 'success',
                'data'   => [
                    'user' => $user,
                    'token' => $token
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
        // todo delete token
        $app['db']->createQueryBuilder()
            ->select('u.id, u.email, u.created, t.token')
            ->from('users', 'u')
            ->join('u', 'tokens', 't', 't.user_id = u.id')
            ->where('t.token = :token');
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
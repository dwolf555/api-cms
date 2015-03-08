<?php
/**
 * User: danielwolf
 * Date: 3/5/15
 * Time: 3:56 PM
 */

namespace APICMS\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class UserController extends BaseController
{

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function singleRoute(Application $app, Request $request, $userId)
    {
        return $this->{strtolower($request->getMethod())}($app, $request, $userId);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function get(Application $app, Request $request)
    {
        return 'get';
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function put(Application $app, Request $request)
    {
        return 'put';
    }

    /**
     * Delete a User
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function delete(Application $app, Request $request)
    {
        return 'delete';
    }

    /**
     * Create a User
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function post(Application $app, Request $request)
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
            return $this->jsonResponse('error', $errorArray, 400);
        }

        // encrypt password
        $encoder = $app['security.encoder.bcrypt'];
        $input['password'] = $encoder->encodePassword($input['password'], null);

        // create user
        try {
            $app['db']->insert('users', $input);
        } catch (UniqueConstraintViolationException $e) {
            return $this->jsonResponse('error', [
                '[email]' => 'This email address has already been registered.'
            ], 400);
        }

        // clean up response
        $response = $input;
        unset($response['password']);
        $response['id'] = $app['db']->lastInsertId();

        return new JsonResponse($response, 200);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function getList(Application $app, Request $request)
    {
        return 'list';
        // TODO
    }
}
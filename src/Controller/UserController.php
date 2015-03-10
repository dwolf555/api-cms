<?php
/**
 * User: danielwolf
 * Date: 3/5/15
 * Time: 3:56 PM
 */

namespace APICMS\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class UserController extends AbstractEntityController
{

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function get(Application $app, Request $request, $userId, $format)
    {
        //todo check permissions
        $query = $app['db']->createQueryBuilder()
            ->select('u.id, u.email')
            ->from('users', 'u')
            ->where('u.id = :user_id')
            ->setParameter('user_id', $userId);

        $user = $app['db']->fetchAssoc(
            $query->getSQL(),
            $query->getParameters()
        );

        if (!$user) {
            return $this->jsonResponse('error', [
                'message' => 'User not found.'
            ], 404);
        }

        return $this->jsonResponse('success', $user, 200);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function put(Application $app, Request $request, $userId, $format)
    {
        // todo check perms
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
        if (isset($input['password'])) {
            $encoder = $app['encoder.bcrypt'];
            $input['password'] = $encoder->encodePassword($input['password'], null);
        }

        // update user
        try {
            $app['db']->update('users', $input, ['id' => $userId]);
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
     * Delete a User
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function delete(Application $app, Request $request, $userId, $format)
    {
        //todo check perms
        $app['db']->delete('users', ['id' => $userId]);
        return $this->jsonResponse('success', ['message' => 'User deleted successfully.'], 200);
    }

    /**
     * Create a User
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function post(Application $app, Request $request, $format)
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
        $encoder = $app['encoder.bcrypt'];
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

        return new JsonResponse($response, 201);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function getList(Application $app, Request $request)
    {
        // todo check perms
        /**@var */
        $query = $app['db']->createQueryBuilder();
        $query->select()
            ->from()
            ->
        $app['db']->fetchAll();
        return 'list';
        // TODO
    }
}
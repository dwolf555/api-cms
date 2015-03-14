<?php
/**
 * User: danielwolf
 * Date: 3/5/15
 * Time: 3:56 PM
 */

namespace APICMS\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class UserController extends AbstractEntityController
{
    const SELECT_STATEMENT = 'u.id, u.email, DATE_FORMAT(u.created, "%Y-%m-%dT%TZ") as created';


    /**
     * {@inheritdoc}
     */
    public function get(Application $app, Request $request, $userId)
    {
        //todo check permissions
        $query = $app['db']->createQueryBuilder()
            ->select(self::SELECT_STATEMENT)
            ->from('users', 'u')
            ->where('u.id = :user_id')
            ->setParameter('user_id', $userId);

        $user = $app['db']->fetchAssoc(
            $query->getSQL(),
            $query->getParameters()
        );

        if (!$user) {
            return $this->jsonResponse(['message' => self::NOT_FOUND_MSG], 404);
        }

        return $this->jsonResponse($user, 200);
    }

    /**
     * {@inheritdoc}
     */
    public function put(Application $app, Request $request, $userId)
    {
        // todo check perms
        $input = $request->request->all();

        // validation
        $inputConstraints = new Assert\Collection([
            'email' => new Assert\Optional(new Assert\Email()),
            'password' => new Assert\Optional(new Assert\Length(['min' => 8]))
        ]);
        $errors = $app['validator']->validateValue($input, $inputConstraints);

        if (count($errors) > 0) {
            $errorArray = [];
            foreach ($errors as $e) {
                $errorArray[$e->getPropertyPath()] = $e->getMessage();
            }
            return $this->jsonResponse($errorArray, 400);
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
            return $this->jsonResponse([
                '[email]' => 'This email address has already been registered.'
            ], 400);
        }

        $roleQuery = $app['db']->createQueryBuilder()
            ->select(self::SELECT_STATEMENT)
            ->from('users', 'u')
            ->where('u.id = :id')
            ->setParameter('id', $userId);

        $role = $app['db']->fetchAssoc($roleQuery->getSQL(), $roleQuery->getParameters());

        return $this->jsonResponse($role, 201);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Application $app, Request $request, $id)
    {
        // todo check perms
        return $this->deleteEntity($app['db'], 'users', 'User', $id);
    }

    /**
     * {@inheritdoc}
     */
    public function post(Application $app, Request $request)
    {
        //todo check perms
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

        // encrypt password
        $input['password'] = $app['encoder.bcrypt']->encodePassword($input['password'], null);

        // create user
        try {
            $app['db']->insert('users', $input);
        } catch (UniqueConstraintViolationException $e) {
            return $this->jsonResponse([
                '[email]' => 'This email address has already been registered.'
            ], 400);
        }

        $userQuery = $app['db']->createQueryBuilder()
            ->select(self::SELECT_STATEMENT)
            ->from('users', 'u')
            ->where('u.id = :id')
            ->setParameter('id', $app['db']->lastInsertId());
        $user = $app['db']->fetchAssoc($userQuery->getSQL(), $userQuery->getParameters());

        return $this->jsonResponse($user, 201);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(Application $app, Request $request)
    {
        // todo check perms
        return $this->paginate(
            $app,
            $app['db']->createQueryBuilder()
                ->select(self::SELECT_STATEMENT)
                ->from('users', 'u')
        );
    }
}
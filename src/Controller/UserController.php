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
    const SELECT_STATEMENT = 'u.id user_id, u.email, DATE_FORMAT(u.created, "%Y-%m-%dT%TZ") as created';


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
            'password' => new Assert\Optional(new Assert\Length(['min' => 8])),
            'roles' => new Assert\Optional(new Assert\Type('array')) // todo validate array (Assert\Collection?)
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

        if (isset($input['roles'])) {
            $roles = $input['roles'];
            unset($input['roles']);
        }

        // update user
        try {
            $app['db']->update('users', $input, ['id' => $userId]);
        } catch (UniqueConstraintViolationException $e) {
            return $this->jsonResponse([
                '[email]' => 'This email address has already been registered.'
            ], 400);
        }

        if (isset($roles)) {
            $newRoles = [];
            foreach ($roles as $inputRole) {
                if (isset($inputRole['role_id'])) {
                    $newRoles[] = $inputRole['role_id'];
                } else if (preg_match('/^[0-9]{1,}$/', $inputRole)) { // wouldn't be required if we had validation above
                    $newRoles[] = $inputRole;
                } else {
                    // todo throw error here or validate above (above is better)
                }
            }

            $currentRoles = [];
            $currentRolesQuery = $app['db']->createQueryBuilder()
                ->select('ur.role_id')
                ->from('users_roles', 'ur')
                ->where('ur.user_id = :userId')
                ->setParameter('userId', $userId);
            $currentRolesResults = $app['db']->fetchAll($currentRolesQuery->getSQL(), $currentRolesQuery->getParameters());
            foreach ($currentRolesResults as $roleResult) {
                $currentRoles[] = $roleResult['role_id'];
            }

            $rolesToInsert = array_diff($newRoles, $currentRoles);
            $rolesToDelete = array_diff($currentRoles, $newRoles);
            if (count($rolesToDelete)) {
                $deleteQB = $app['db']->createQueryBuilder()
                    ->delete('users_roles', 'ur')
                    ->where('ur.role_id IN(:rolesToDelete)')
                    ->setParameter('rolesToDelete', $rolesToDelete);
                $app['db']->executeQuery($deleteQB->getSQL(), $deleteQB->getParameters());
            }

            foreach ($rolesToInsert as $roleId) {
                try {
                    $app['db']->insert('users_roles', ['user_id' => $userId, 'role_id' => $roleId]);
                } catch (UniqueConstraintViolationException $e) {}
            }
        }

        $roleQuery = $app['db']->createQueryBuilder()
            ->select(self::SELECT_STATEMENT)
            ->from('users', 'u')
            ->where('u.id = :id')
            ->setParameter('id', $userId);

        if (isset($roles)) {
            $roleQuery->addSelect('GROUP_CONCAT(r.id) roles, r.name')
                ->leftJoin('u', 'users_roles', 'ur', 'ur.user_id = u.id')
                ->innerJoin('ur', 'roles', 'r', 'r.id = ur.role_id');
        }

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
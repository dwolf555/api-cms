<?php
/**
 * Role: danielwolf
 * Date: 3/5/15
 * Time: 3:56 PM
 */

namespace APICMS\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use APICMS\Controller\AbstractEntityController as BaseController;

class RoleController extends BaseController
{
    const SELECT_STATEMENT = 'r.id role_id, r.name, DATE_FORMAT(r.created, "%Y-%m-%dT%TZ") as created';

    /**
     * {@inheritdoc}
     */
    public function get(Application $app, Request $request, $roleId)
    {
        //todo check permissions
        $query = $app['db']->createQueryBuilder()
            ->select(self::SELECT_STATEMENT)
            ->from('roles', 'r')
            ->where('r.id = :role_id')
            ->setParameter('role_id', $roleId);

        $role = $app['db']->fetchAssoc(
            $query->getSQL(),
            $query->getParameters()
        );

        if (!$role) {
            return $this->jsonResponse([
                'message' => BaseController::NOT_FOUND_MSG
            ], 404);
        }

        return $this->jsonResponse($role, 200);
    }

    /**
     * {@inheritdoc}
     */
    public function put(Application $app, Request $request, $roleId)
    {
        $input = $request->request->all();

        // validation
        $inputConstraints = new Assert\Collection([
            'name' => new Assert\Optional(new Assert\NotBlank())
        ]);
        $errors = $app['validator']->validateValue($input, $inputConstraints);

        if (count($errors) > 0) {
            $errorArray = [];
            foreach ($errors as $e) {
                $errorArray[$e->getPropertyPath()] = $e->getMessage();
            }
            return $this->jsonResponse($errorArray, 400);
        }

        // create role
        try {
            $app['db']->update('roles', $input, ['id' => $roleId]);
        } catch (UniqueConstraintViolationException $e) {
            return $this->jsonResponse([
                '[name]' => 'This role has already been created.'
            ], 400);
        }

        $roleQuery = $app['db']->createQueryBuilder()
            ->select(self::SELECT_STATEMENT)
            ->from('roles', 'r')
            ->where('r.id = :id')
            ->setParameter('id', $roleId);
        $role = $app['db']->fetchAssoc($roleQuery->getSQL(), $roleQuery->getParameters());

        return $this->jsonResponse($role, 201);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Application $app, Request $request, $id)
    {
        // todo check perms
        return $this->deleteEntity($app['db'], 'roles', 'Role', $id);
    }

    /**
     * {@inheritdoc}
     */
    public function post(Application $app, Request $request)
    {
        $input = $request->request->all();

        // validation
        $inputConstraints = new Assert\Collection([
            'name' => new Assert\NotBlank()
        ]);
        $errors = $app['validator']->validateValue($input, $inputConstraints);

        if (count($errors) > 0) {
            $errorArray = [];
            foreach ($errors as $e) {
                $errorArray[$e->getPropertyPath()] = $e->getMessage();
            }
            return $this->jsonResponse($errorArray, 400);
        }

        // create role
        try {
            $app['db']->insert('roles', $input);
        } catch (UniqueConstraintViolationException $e) {
            return $this->jsonResponse([
                '[name]' => 'This role has already been created.'
            ], 400);
        }

        $roleQuery = $app['db']->createQueryBuilder()
            ->select(self::SELECT_STATEMENT)
            ->from('roles', 'r')
            ->where('r.id = :id')
            ->setParameter('id', $app['db']->lastInsertId());
        $role = $app['db']->fetchAssoc($roleQuery->getSQL(), $roleQuery->getParameters());

        return $this->jsonResponse($role, 201);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(Application $app, Request $request)
    {
        // todo check perms
        $query = $app['db']->createQueryBuilder()
            ->select(self::SELECT_STATEMENT)
            ->from('roles', 'r');
        return $this->paginate($app, $query);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param int $roleId
     * @return Response
     */
    public function getUsers(Application $app, Request $request, $roleId)
    {
        // todo check perms
        $userQuery = $app['db']->createQueryBuilder()
            ->select('u.id user_id, u.email, DATE_FORMAT(r.created, "%Y-%m-%dT%TZ") as created')
            ->from('roles', 'r')
            ->join('r', 'users_roles', 'ur', 'ur.role_id = r.id')
            ->join('ur', 'users', 'u', 'u.id = ur.user_id')
            ->where('r.id = :roleId')
            ->setParameter('roleId', $roleId);
        return $this->paginate($app, $userQuery);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function putUsers(Application $app, Request $request, $id)
    {
        $input = $request->request->all();

        // validation
        // should be array of user_ids,
        $inputConstraints = new Assert\Collection([ // todo abstract this
//            'user_id' => new Assert\NotBlank() // todo work out the validation rules
        ]);
        $errors = $app['validator']->validateValue($input, $inputConstraints);

        if (count($errors) > 0) {
            $errorArray = [];
            foreach ($errors as $e) {
                $errorArray[$e->getPropertyPath()] = $e->getMessage();
            }
            return $this->jsonResponse($errorArray, 400);
        }
    }


    /**
     * @param Application $app
     * @param Request $request
     * @param int $roleId
     * @param int $userId
     * @return Response
     */
    public function deleteUser(Application $app, Request $request, $roleId, $userId)
    {
        return Response::create();//todo
    }

}
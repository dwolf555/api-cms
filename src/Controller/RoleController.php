<?php
/**
 * Role: danielwolf
 * Date: 3/5/15
 * Time: 3:56 PM
 */

namespace APICMS\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class RoleController extends BaseController
{

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function singleRouter(Application $app, Request $request, $roleId)
    {
        return $this->{strtolower($request->getMethod())}($app, $request, $roleId);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function get(Application $app, Request $request, $roleId)
    {
        //todo check permissions
        $query = $app['db']->createQueryBuilder()
            ->select('r.id, r.name')
            ->from('roles', 'r')
            ->where('r.id = :role_id')
            ->setParameter('role_id', $roleId);

        $role = $app['db']->fetchAssoc(
            $query->getSQL(),
            $query->getParameters()
        );

        if (!$role) {
            return $this->jsonResponse('error', [
                'message' => 'Role not found.'
            ], 404);
        }

        return $this->jsonResponse('success', $role, 200);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function put(Application $app, Request $request, $roleId)
    {
        return 'put';
    }

    /**
     * Delete a Role
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function delete(Application $app, Request $request, $roleId)
    {
        //todo check perms
        $app['db']->delete('roles', ['id' => $roleId]);
        return $this->jsonResponse('success', ['message' => 'Role deleted successfully.'], 200);
    }

    /**
     * Create a Role
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
            'name' => new Assert\NotBlank()
        ]);
        $errors = $app['validator']->validateValue($input, $inputConstraints);

        if (count($errors) > 0) {
            $errorArray = [];
            foreach ($errors as $e) {
                $errorArray[$e->getPropertyPath()] = $e->getMessage();
            }
            return $this->jsonResponse('error', $errorArray, 400);
        }

        // create role
        try {
            $app['db']->insert('roles', $input);
        } catch (UniqueConstraintViolationException $e) {
            return $this->jsonResponse('error', [
                '[name]' => 'This role has already been created.'
            ], 400);
        }


        // clean up response
        $response = $input;
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
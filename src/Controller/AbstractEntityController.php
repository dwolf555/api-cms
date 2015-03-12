<?php

namespace APICMS\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractEntityController implements EntityControllerInterface
{
    const ERR_STATUS = 'error';
    const NOT_FOUND_MSG = 'Not Found';
    const OK_STATUS = 'success';
    const SELECT_STATEMENT = '*';
    const QUERY_LIMIT = 25;

    /**
     * @param Application $app
     * @param Request $request
     * @param int $id
     * @return mixed
     */
    public function singleRouter(Application $app, Request $request, $id = 1)
    {
        return $this->{strtolower($request->getMethod())}($app, $request, $id);
    }

    /**
     * @param array $data
     * @param int $code
     * @return Response
     */
    protected function jsonResponse($data, $code)
    {
        return JsonResponse::create([
            'code' => $code,
            'data' => $data
        ], $code);
    }

    /**
     * @param string $status
     * @param array $data
     * @param int $code
     * @return Response
     */
    protected function xmlResponse($status, $data, $code)
    {
        // todo format this in xml
        return Response::create([
            'status' => $status,
            'data' => $data
        ], $code);
    }

    protected function paginate(Application $app, QueryBuilder $query)
    {
        $page = $app['request']->query->get('page', 1);
        $select = implode(', ', $query->getQueryPart('select'));

        $query->select('COUNT(0) as count')->setMaxResults(1);
        $count = $app['db']->fetchColumn($query->getSQL(), $query->getParameters(), 0);

        $query->select($select)->setMaxResults(self::QUERY_LIMIT)->setFirstResult(($page - 1) * self::QUERY_LIMIT);
        $results = $app['db']->fetchAll($query->getSQL(), $query->getParameters());

        if (count($results) === 0) {
            return $this->jsonResponse([
                'message' => self::NOT_FOUND_MSG
            ], 404);
        }

        return $this->jsonResponse([
            'pagination' => [
                'records' => (int) $count
            ],
            'results' => $results
        ], 200);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return Response
     */
    public function getList(Application $app, Request $request) {
        return $this->jsonResponse(['message' => 'Not found.'], 400);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function get(Application $app, Request $request, $id) {
        return $this->jsonResponse(['message' => 'Not found.'], 400);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return Response
     */
    public function post(Application $app, Request $request) {
        return $this->jsonResponse(['message' => 'Not found.'], 400);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function put(Application $app, Request $request, $id) {
        return $this->jsonResponse(['message' => 'Not found.'], 400);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function delete(Application $app, Request $request, $id) {
        return $this->jsonResponse(['message' => 'Not found.'], 400);
    }
}
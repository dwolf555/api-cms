<?php

namespace APICMS\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractEntityController implements EntityControllerInterface
{
    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @todo: throws
     */
    public function singleRouter(Application $app, Request $request, $id, $format)
    {
        return $this->{strtolower($request->getMethod())}($app, $request, $userId, $format);
    }

    /**
     * @param $status string
     * @param $data array
     * @param $code int
     * @return \Symfony\Component\HttpFoundation\Response|static
     */
    protected function jsonResponse($status, $data, $code)
    {
        return JsonResponse::create([
            'status' => $status,
            'data' => $data
        ], $code);
    }

    protected function xmlResponse($status, $data, $code)
    {
        return Response::create([
            'status' => $status,
            'data' => $data
        ], $code);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param $format 'json' or 'xml'
     * @return Response
     */
    public function getList(Application $app, Request $request, $format) {
        return $this->jsonResponse('error', ['message' => 'Not found.'], 400);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param $roleId
     * @param $format 'json' or 'xml'
     * @return Response
     */
    public function get(Application $app, Request $request, $roleId, $format) {
        return $this->jsonResponse('error', ['message' => 'Not found.'], 400);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param $format 'json' or 'xml'
     * @return Response
     */
    public function post(Application $app, Request $request, $format) {
        return $this->jsonResponse('error', ['message' => 'Not found.'], 400);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param $roleId
     * @param $format 'json' or 'xml'
     * @return Response
     */
    public function put(Application $app, Request $request, $roleId, $format) {
        return $this->jsonResponse('error', ['message' => 'Not found.'], 400);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param $roleId
     * @param $format 'json' or 'xml'
     * @return Response
     */
    public function delete(Application $app, Request $request, $roleId, $format) {
        return $this->jsonResponse('error', ['message' => 'Not found.'], 400);
    }
}
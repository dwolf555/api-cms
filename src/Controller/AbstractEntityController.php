<?php

namespace APICMS\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractEntityController implements EntityControllerInterface
{
    const OK_STATUS = 'ok';
    const ERR_STATUS = 'error';
    const NOT_FOUND_MSG = 'Not found.';

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
     * @param string $status
     * @param array $data
     * @param int $code
     * @return Response
     */
    protected function jsonResponse($status, $data, $code)
    {
        return JsonResponse::create([
            'status' => $status,
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
        return Response::create([
            'status' => $status,
            'data' => $data
        ], $code);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return Response
     */
    public function getList(Application $app, Request $request) {
        return $this->jsonResponse('error', ['message' => 'Not found.'], 400);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function get(Application $app, Request $request, $id) {
        return $this->jsonResponse('error', ['message' => 'Not found.'], 400);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return Response
     */
    public function post(Application $app, Request $request) {
        return $this->jsonResponse('error', ['message' => 'Not found.'], 400);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function put(Application $app, Request $request, $id) {
        return $this->jsonResponse('error', ['message' => 'Not found.'], 400);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function delete(Application $app, Request $request, $id) {
        return $this->jsonResponse('error', ['message' => 'Not found.'], 400);
    }
}
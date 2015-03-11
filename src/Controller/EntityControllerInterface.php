<?php

namespace APICMS\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface EntityControllerInterface
{
    /**
     * @param Application $app
     * @param Request $request
     * @param $roleId
     * @param $format 'json' or 'xml'
     * @return Response
     */
    public function getList(Application $app, Request $request);
    /**
     * @param Application $app
     * @param Request $request
     * @param $roleId
     * @param $format 'json' or 'xml'
     * @return Response
     */
    public function get(Application $app, Request $request, $id);

    /**
     * @param Application $app
     * @param Request $request
     * @param $format 'json' or 'xml'
     * @return Response
     */
    public function post(Application $app, Request $request);

    /**
     * @param Application $app
     * @param Request $request
     * @param $roleId
     * @param $format 'json' or 'xml'
     * @return Response
     */
    public function put(Application $app, Request $request, $id);

    /**
     * @param Application $app
     * @param Request $request
     * @param $roleId
     * @param $format 'json' or 'xml'
     * @return Response
     */
    public function delete(Application $app, Request $request, $id);

}
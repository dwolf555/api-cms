<?php

namespace APICMS\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class BaseController
{

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
}
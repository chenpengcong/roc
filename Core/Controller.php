<?php

namespace App\Core;

use App\Core\Psr\RequestInterface;
use App\Core\Psr\ResponseInterface;

class Controller
{
    protected $request;

    protected $response;

    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
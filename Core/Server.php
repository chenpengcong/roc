<?php

namespace App\Core;

use App\Core\Http\ServerRequest;
use App\Core\Http\Response;

use App\Core\Psr\ResponseInterface;

class Server
{
    public function run()
    {
        $this->router = new Router();
        
        $this->view = new View(APPPATH . 'views');
        $this->request = $this->getRequest();
        $this->response = $this->getResponse();
        
        $result = $this->router->handle($this->request->getUri()->getPath());

        //当匹配到控制器文件名但类不存在 || 当匹配到控制器但匹配不到方法名
        if ($result == false) {
            $response = $this->response->withStatus(404);
            $response->getBody()->write('404 Not Found');
            $this->sendResponse($response);
            return;
        }
        $response = $this->runController();

        $this->sendResponse($response);
        
    }

    private function getRequest()
    {
        $scheme = isHttps() ? 'https': 'http';
        $uri = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $request = new ServerRequest($_SERVER['REQUEST_METHOD'], $uri);
        $request = $request->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST);
        
        return $request;
    }

    private function getResponse()
    {
        $response = new Response();
        return $response;
    }

    private function runController()
    {
        $controller = $this->router->getController();
        $method = $this->router->getMethod();
        $params = $this->router->getParams();

        $class = new $controller($this->request, $this->response);

        $class->view = $this->view;

        $response = $class->$method(...$params);
        return $response;
    }

    private function sendResponse(ResponseInterface $response)
    {
        //是否已发送header
        if (!headers_sent()) {
            header(sprintf(
                'HTTP/%s %s %s', 
                $response->getProtocolVersion(), 
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));
            foreach (array_keys($response->getHeaders()) as $fieldName) {
                header(sprintf('%s: %s', $fieldName, $response->getHeaderLine($fieldName)), false);
            }
        }
        echo $response->getBody();
    }


}
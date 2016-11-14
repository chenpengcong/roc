<?php

namespace App\Core;

use App\Core\Psr\ResponseInterface;

class View
{

    private $templatePath = '';

    public function __construct($templatePath)
    {
        $this->templatePath = rtrim($templatePath, '/') . '/';
    }


    public function render(ResponseInterface $response, $view, array $data = [])
    {
        $file = $this->templatePath . $view;
        if (!file_exists($file)) {
            throw new \RuntimeException('failed to render the view, the view does not exist.');
        }
        extract($data);
        //视图内容先缓存起来，因为header还未发送
        ob_start();
        include $file;
        $output = ob_get_contents();
        $response->getBody()->write($output);
        ob_end_clean();
        return $response;
    }
}
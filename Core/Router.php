<?php

namespace App\Core;

use Roc\Config\Routes;

class Router
{
    private $routes = [];

    private $segments = [];

    private $directory = '';

    private $controller = '';

    private $method = '';

    private $params = [];

    public function __construct($routes = [])
    {
        $this->routes = $routes;
    }

    public function handle($path = '')
    {
        $path = $this->matchRoute($path);
        $isMatch = $this->matchController($path);
        if ($isMatch === false) {
            //(url为/) || (url段全是目录) || (没有对应控制器且没有对应目录)
            $this->setDefaultController();
            return true;
        }
 
        $this->setController(ucfirst($this->segments[0]));
        if (!class_exists($this->controller, true)) {
            return false;
        }
        $method = isset($this->segments[1]) ? $this->segments[1]: 'index';
        if (!method_exists($this->controller, $method)) {
            return false;
        }
        $this->setMethod($method);
        $this->setParams(array_slice($this->segments, 2));
        return true;
    }

    //匹配自定义路由规则
    private function matchRoute($path)
    {
        $routes = Routes::$routes;
        foreach ($routes as $pattern => $mapPath) {
            if (preg_match('#^' . $pattern . '$#', $path)) {
                if (strpos($pattern, '(') != false && strpos($mapPath, '$')!= false) {
                    $newPath = preg_replace('#^' . $pattern . '$#', $mapPath, $path);
                    return $newPath;
                }

            }
        }
        return $path;
    }

    /**
     * 根据url匹配相应控制器 
     * 
     * 若成功匹配，this->segments[0]填充controller，this->segments[1]填充method，剩余的填充参数
     * 
     * @param string $path uri的path
     * @return  boolean 是否找到相应的控制器
     */
    private function matchController($path)
    {
        $path = trim($path, '/');
        if (empty($path)) {
            //url为/
            return false;
        }
        foreach (explode('/', $path) as $val) {
            if (trim($val) !== '') {
               $this->segments[] = $val; 
            }
        }
        $cnt = count($this->segments);
        //从第一个url段开始，若不存在相应控制器&&存在相应目 录，则将此url端设置为目录,最终$this->segments存的是controller和method
        while ($cnt-- > 0) {
            $tmp = $this->directory . ucfirst($this->segments[0]);
            if (!file_exists(APPPATH . 'Controllers/' . $tmp . '.php')) {
                if (is_dir(APPPATH . 'Controllers/' . $this->directory . ucfirst($this->segments[0]))) {
                    //该url段没有对应控制器但有对应目录
                    $this->directory = $this->directory . ucfirst(array_shift($this->segments)) . '/';
                } else {
                    //该url段没有对应控制器且没有对应目录
                    return false;
                }
            } else {
                //该url段有对应控制器
                return true;
            }
        }
        //执行到此表示url段全是目录
        return false;
    }

    private function setDefaultController()
    {
        $this->controller = 'Roc\Controllers\Index';
        $this->method = 'index';
    }

    private function setController($controller)
    {
        $this->controller = str_replace('/', '\\', 'Roc\Controllers\\' . $this->directory .$controller);
    }

    private function setMethod($method)
    {
        $this->method = $method;
    }

    private function setParams(array $params)
    {
        $this->params = $params;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getParams()
    {
        return $this->params;
    }
} 
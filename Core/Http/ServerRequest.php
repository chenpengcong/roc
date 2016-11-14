<?php

namespace App\Core\Http;

use App\Core\Psr\ServerRequestInterface;

class ServerRequest extends Request implements ServerRequestInterface
{

    private $serverParams = [];

    private $cookieParams = [];

    private $queryParams = [];

    private $parsedBody;

    /**
     * 构造方法
     * @param string $method
     * @param string|UriInterface $uri
     * @param array  $headers
     * @param [type] $body         [description]
     * @param string $version      [description]
     * @param array  $serverParams [description]
     */
    public function __construct(
        $method,
        $uri, 
        array $headers = [], 
        $body = null, 
        $version = '1.1', 
        array $serverParams = []
    )
    {
        $this->serverParams = $serverParams;
        parent::__construct($method, $uri, $headers, $body, $version);
    }

    /**
     * 获得服务端参数
     * 
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }


    /**
     * 获得客户端发送给服务端的cookie数据
     *
     * 该数据必须和超全局变量$_COOKIE兼容
     * 
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * 指定cookie并返回ServerRequest实例
     * 
     * @param  array  $cookies
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;
        return $clone;
    }

    /**
     * 获得query string
     * @return [type] [description]
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * 
     * @param  array  $query
     * @return static
     */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->queryParams = $query;
        return $clone;
    }

    public function getUploadedFiles()
    {
        //TODO
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        //TODO
    }

    /**
     * 获得请求主体(body)
     *
     * 如果请求内容类型是x-www-form-urlencoded/或者multipart/form-data,而且请求方法是POST，该方法必须返回$_POST的内容
     *
     * 否则，该方法应该反序列化请求主体内容并返回为数组或对象类型。null值表示请求主题为空
     * @return array|object|null 
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * 指定body parameters并返回实例
     * @param  null|array|object $data The deserialized body data.
     * @return static
     * @throws \InvalidArgumentException if an unsupported argument type is provided
     */
    public function withParsedBody($data)
    {
        if (!is_null($data) && !is_array($data) && !is_object($data)) {
            throw new \InvalidArgumentException('parsed body value must be an array, an object or null.');
        }
        $clone = clone $this;
        $clone->parsedBody = $data;
        return $clone;
    }

    /**
     * 获得请求属性
     * @return array Attributes
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * 获得一个请求属性
     * [getAttribute description]
     * @param  string $name The attribute name.
     * @param  mixed $default 属性不存在时的默认返回值
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        if (array_key_exists($name, $this->attributes) === false) {
            return $default;
        }
        return $this->attributes[$name];
    }

    /**
     * 指定请求属性并返回ServerRequest实例
     * @param  string $name
     * @param  mixed $value 
     * @return static 
     */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    /**
     * 删除指定请求属性并返回ServerRequest实例
     * @param  string $name
     * @return static
     */
    public function withoutAttribute($name)
    {
        if (array_key_exists($name, $clone->attributes) === false) {
            return $this;
        }
        $clone = clone $this;
        unset($clone->attributes[$name]);
        return $clone;
    }
}
<?php

namespace App\Core\Http;

use App\Core\Psr\RequestInterface;
use App\Core\Psr\UriInterface;
use App\Core\Psr\StreamInterface;

class Request extends Message implements RequestInterface
{

    private $requestTarget;

    private $method;

    private $uri;

    public function __construct($method, $uri, array $headers = [], StreamInterface $body = null, $version = '1.1')
    {
        if (!($uri instanceof UriInterface)) {
            $this->uri = new Uri($uri);
        } else {
            $this->uri = $uri;
        }
        $this->method = strtoupper($method);
        $this->headers = $headers; 
        if (is_null($body)) {
            $this->stream = new Stream(fopen('php://input', 'r'));
        } else {
            $this->stream = $body;
        }
        
        $this->protocolVersion = $version;
 
    }

    /**
     * 获得message的请求目标
     *
     * 一般情况下request-target从URI中获取，除非通过withRequestTarget指定
     *
     * 如果没有可用URI，也没有指定request-target，该方法返回"/"
     * 
     * @return string
     */
    public function getRequestTarget()
    {
        if (!empty($this->requestTarget)) {
            return $this->requestTarget;
        }
        if (empty($this->uri)) {
            return '/';
        }
        $requestTarget = $this->uri->getPath();
        $query = $this->uri->getQuery();
        if (!empty($query)) {
           $requestTarget .= $query; 
        }
        return $requestTarget;
    }

    /**
     *  指定request-target并返回request实例
     * @param  mixed $requestTarget
     * @return static
     */
    public function withRequestTarget($requestTarget)
    {
        $clone = clone $this;
        $clone->requestTarget = $requestTarget;
        return $clone;
    }

    /**
     * 获得http的请求方法
     * @return string 
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * 指定http方法并返回request实例
     * @param  string $method case-sensitive
     * @return static
     */
    public function withMethod($method)
    {
        if (!is_string($method)) {
            throw new \InvalidArgumentException('method must be a string.');
        }
        $clone = clone $this;
        $clone->method = strtoupper($method);
        return $clone;
    }

    /**
     * 获得URI实例
     * @return UriInterface
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * 指定uri并返回request实例
     *
     * 当preserveHost为false
     * - 如果URI的host header为空，则不更新host header
     * 
     * 当preserveHost为true
     * - 如果host header为空，URI包含host header，更新host header
     * - 如果host header非空，则无需更新host header
     *
     * @param  UriInterface $uri
     * @param  boolean $preserveHost
     * @return static
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $clone = clone $this;
        $clone->uri = $uri;
        $hostHeader = $clone->getHeader('host');
        $host = $clone->uri->getHost();
        if ($preserveHost === false && !empty($host)) {
            $clone->withHeader('host', [$host]);
        }

        if ($preserveHost === true && empty($hostHeader) && !empty($host)) {
            $clone->withHeader('host', [$host]);
        }
        return $clone;
    }
}
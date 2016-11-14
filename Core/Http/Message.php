<?php

namespace App\Core\Http;

use App\Core\Psr\MessageInterface;
use App\Core\Psr\StreamInterface;

class Message implements MessageInterface
{
    protected $protocolVersion = '1.1';

    protected $headers = [];

    //key为小写的header名称, 值为header原始名称
    protected $headerNames = [];

    protected $stream;

    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * 获取使用的HTTP版本
     * @param  string $version 
     * @return string          
     */
    public function withProtocolVersion($version)
    {
        $clone = clone $this;
        $clone->protocolVersion = $version;
        return $clone;
    }

    /**
     * 获取所有header
     * @return array key: string, value: array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * 指定header是否存在
     * @param  string  $name Case-insensitive
     * @return boolean
     */
    public function hasHeader($name)
    {
        return isset($this->headerNames[strtolower($name)]);
    }

    /**
     * 获得指定header的值(array类型)
     * @param  string $name Case-insensitive
     * @return array 
     */
    public function getHeader($name)
    {
        $name = strtolower($name);

        if (!isset($this->headerNames[$name])) {
            return [];
        }
        $header = $this->headerNames[$name];
        return $this->headers[$header];
    }

    /**
     * 获得指定header的值(string类型, 以','分隔多个值), 
     * @param  string $name Case-insensitive
     * @return string       
     */
    public function getHeaderLine($name)
    {
        $headerVal = $this->getHeader($name);
        $headerLine = implode(',', $headerVal);
        return $headerLine;
    }

    /**
     * 更新(header已存在)或插入(header不存在)指定header的值,并返回一个message实例 
     * @param  [type] $name  [description]
     * @param  string|string[] $value 
     * @return static
     */
    public function withHeader($name, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $lName = strtolower($name);
        $clone = clone $this;
        if (isset($clone->headerNames[$lName])) {
            unset($clone->headers[$clone->headerNames[$lName]]);
        }

        $clone->headerNames[$lName] = $name;
        $clone->headers[$name] = $value;
        return $clone;
    }

    /**
     * 添加(header已存在)或插入(header不存在)指定header的值,并返回一个message实例 
     * @param  string $name  Case-insensitive
     * @param  string|string[] $value 
     * @return static
     */
    public function withAddedHeader($name, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $lName = strtolower($name);
        $clone = clone $this;
        if (isset($clone->headerNames[$lName])) {
            $header = $this->headerNames[$lName];
            $clone->headers[$header] = array_merge($clone->headers[$header], $value);
        } else {
            $clone->headerNames[$lName] = $name;
            $clone->headers[$name] = $value;
        }
        return $clone;
    }

    /**
     * 返回一个不带指定header的message实例
     * @param  [string] $name Case-insensitive
     * @return static
     */
    public function withoutHeader($name)
    {
        $lName = strtolower($name);
        if (!isset($this->headerNames[$lName])) {
            return $this;
        }
        $clone = clone $this;
        $header = $clone->headerNames[$lName];
        unset($clone->headers[$header], $clone->headerNames[$lName]);
        return $clone;
    }

    /**
     * 获得message的body
     * @return StreamInterface Returns the body as a stream
     */
    public function getBody()
    {
        return $this->stream;
    }

    /**
     * 指定message body，并返回message实例
     * @param  StreamInterface $body 
     * @return static
     * @throws \InvalidArgumentException When the body is not valid. 
     */
    public function withBody(StreamInterface $body)
    {
        $clone = clone $this;
        $clone->stream = $body;
        return $clone;
    }
}
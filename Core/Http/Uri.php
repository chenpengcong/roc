<?php

namespace App\Core\Http;

use App\Core\Psr\UriInterface;

//scheme:[//[user:password@]host[:port]][/]path[?query][#fragment]
class Uri implements UriInterface
{

    private $scheme = '';

    private $host = '';

    private $userInfo = '';

    private $port = null;

    private $path = '';

    private $query = '';

    private $fragment = '';

    private static $deafultPorts = array(
        'http' => 80,
        'https' => 443,
    );

    private static $charUnreserved = 'a-zA-Z0-9_\-\.~';
    private static $charGenDelims = ':\/?#\[\]@';
    private static $charSubDelims = '!\$&\'\(\)\*\+,;=';

    public function __construct($uri)
    {
        if (!empty($uri)) {
            $components = parse_url($uri);
            if ($components === false) {
                throw new \InvalidArgumentException('fail to parse uri.');
            }
            $this->setUricomponent($components);
        }
    }

    /**
     * 获得URI的scheme.
     * 
     * @return string The URI scheme.
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * 获得URI的authority.
     *
     * 没有authority则返回空字符串.
     * 
     * authority 语法: [user-info@]host[:port].
     *
     * 没有port或port是当前scheme的默认port则不被包含.
     * 
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority()
    {
        $authority = empty($this->userInfo) ? '': $this->userInfo . '@';
        $authority .= empty($this->host) ? '': $this->host;
        $authority .= is_null($this->port) ? '': ':' . (string)$this->port;
        return $authority;
    }

    /**
     * 获得URI中的用户信息.
     * 
     * 没有用户信息则返回空字符串.
     *
     * 如果有password，添加到user后面，并以':'符号分隔, '@'符号不属于用户信息中一部分.
     * 
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * 获得URI中的host.
     *
     * 没有host则返回空字符串.
     *
     * 返回值必须是小写.
     * 
     * @return string The URI host.
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * 取出URI中的port.
     *
     * 如果是当前scheme的默认端口, 返回null, 否则返回integer数值.
     *
     * 如果port跟scheme都不存在，返回null.
     *
     * 如果port不存在，但scheme存在，返回该scheme的默认port.
     * 
     * @return null|int The URI port.
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * 获得URI中的path.
     *
     * 返回的path必须是编码过的.
     * 
     * @return string The URI path.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * 获得URI中的query string.
     *
     * 不存在query string则返回空字符串
     *
     * 字符'?'不属于query string的一部分
     *
     * 返回值必须是经过编码的
     * 
     * @return string The URI query string
     */
    public function getQuery()
    {   
        return $this->query;
    }

    /**
     * 获得URI中的fragment
     *
     * 没有fragment则返回空字符串
     *
     * 字符'#'不属于fragment的一部分
     * 
     * @return string The URI fragment
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * 指定scheme, 并返回一个Uri实例
     * @param  string $scheme 
     * @return static 
     */
    public function withScheme($scheme)
    {
        $clone = clone $this;
        $clone->scheme = $clone->filterScheme($scheme);
        return $clone;
    }


    /**
     * 指定userInfo, 并返回uri实例
     *
     * $user为空串时相当于删除user information
     * 
     * @param  string $user
     * @param  string $password
     * @return static
     */
    public function withUserInfo($user, $password = null)
    {
        $clone = clone $this;
        if (empty($user)) {
            $clone->userInfo = '';
        } else {
            $clone->userInfo = isset($password) ? $user . ':' . $password: $user; 
        }
        return $clone;
    }


    /**
     * 指定host并返回uri实例
     *
     * $host为空时相当于删除host
     * 
     * @param  string $host 
     * @return static
     */
    public function withHost($host)
    {
        $clone = clone $this;
        $clone->host = $clone->filterHost($host);
        return $clone;
    }

    /**
     * 指定port并返回uri实例
     *
     * 当端口超出TCP和UDP端口范围时必须抛出异常
     * 
     * $post为nurll时相当于删除port
     * 
     * @param  int|null $port
     * @return static      
     */
    public function withPort($port)
    {
        $clone = clone $this;
        $clone->port = $clone->filterPort($port);
        $clone->removeDefaultPort();
        return $clone;
    }

    /**
     * 指定path并返回uri实例
     *
     * 如果path将作为domain-relative, path必须以'/'开始.
     * (domain-relative即该路径是相对域名路径的，
     * eg: www.example.com对应/root/html, 
     * 若想访问/root/html目录下的index.html文件，path应为/index.html)
     *
     * 不以'/'开始的path被当作相对某基本路径
     * 
     * @param  string $path 
     * @return static       
     */
    public function withPath($path)
    {
        $clone = clone $this;
        $clone->path = $clone->filterPath($path);
        return $clone;   
    }

    /**
     * 指定query string并返回uri实例
     * @param  [type] $query [description]
     * @return [type]        [description]
     */
    public function withQuery($query)
    {
        $clone = clone $this;
        $clone->query = $clone->filterQueryAndFragment($query);
        return $clone;
    }

    /**
     * 指定fragment并返回uri实例
     *
     * 
     * 
     * @param  string $fragment
     * @return static
     */
    public function withFragment($fragment)
    {
        $clone = clone $this;
        $clone->fragment = $fragment;
        return $clone;
    }

    private function setUricomponent($components)
    {
        $this->scheme = isset($components['scheme']) 
            ? strtolower($components['scheme'])
            : '';

        $this->host = isset($components['host'])
            ? strtolower($components['host'])
            : '';

        $this->port = isset($components['port'])
            ? $this->filterPort($components['port'])
            : null;

        $this->path = isset($components['path'])
            ? $this->filterPath($components['path'])
            : '';

        $this->query = isset($components['query'])
            ? $this->filterQueryAndFragment($components['query'])
            : '';

        $this->fragment = isset($components['fragment'])
            ? $this->filterQueryAndFragment($components['fragment'])
            : '';

        $this->userInfo = isset($components['user'])
            ? $components['user']
            : '';
        $this->userInfo .= isset($components['pass'])
            ? ':' . $components['pass']
            : '';
        $this->removeDefaultPort();
    }


    private function filterScheme($scheme)
    {
        if (!is_string($scheme)) {
            throw new \InvalidArgumentException('scheme must be string.');
        }
        return strtolower($scheme);
    }

    private function filterHost($host)
    {
        if (!is_string($host)) {
            throw new \InvalidArgumentException('host must be string.');
        }
        return strtolower($host);
    }

    private function filterPort($port)
    {
        if (is_null($port)) {
            return null;
        }

        $port = (int)$port;
        if ($port < 1 || $port > 0xffff) {
            throw new \InvalidArgumentException('port must be between 1 and 65535.');
        }
        return $port;
    }

    private function filterPath($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('path must be string.');
        }
        $path = preg_replace_callback(
            '/(?:[^' 
            . self::$charUnreserved 
            . self::$charGenDelims 
            . self::$charSubDelims 
            . ']+|%(?![A-Fa-f0-9]{2}))/', 
            function ($match) {
                return rawurlencode($match[0]);
            }, 
            $path
        );
        return $path;
    }

    private function filterQueryAndFragment($str)
    {
        if (!is_string($str)) {
            throw new \InvalidArgumentException('query and fragment must be string.');
        }

        $str = preg_replace_callback(
            '/(?:[^' 
            . self::$charUnreserved
            . self::$charGenDelims
            . self::$charSubDelims 
            . '])/', 
            function ($match) {
                return rawurlencode($match[0]);
            }, 
            $str);
        return $str;
    }

    private function removeDefaultPort()
    {
        if ((is_null($this->port) === false) && ($this->port === $this->getDefaultPort())) {
            $this->port = null;
        }
    }

    private function getDefaultPort()
    {
        return self::$deafultPorts[$this->getScheme()];
    }

    public function __toString()
    {
        $uri = '';
        $uri .= empty($this->scheme) ? '': $this->scheme . ':';
        $uri .= empty($this->authority) ? '': '//' . $this->authority;
        $uri .= $this->path;
        $uri .= empty($this->query) ? '': '?' . $this->query;
        $uri .= empty($this->fragment) ? '': '#' . $this->fragment;
        return $uri;
    }
}
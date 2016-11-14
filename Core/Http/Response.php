<?php

namespace App\Core\Http;

use App\Core\Psr\StreamInterface;
use App\Core\Psr\ResponseInterface;

class Response extends Message implements ResponseInterface
{

    private $statusCode;

    private $reasonPhrase;

    //HTTP状态码
    //@link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml 
    private static $statusCodes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    );

    public function __construct($status = 200, StreamInterface $body = null)
    {
        $this->withStatus($status);
        $this->stream = is_null($body) ? new Stream(fopen('php://temp', 'r+')): $body;
    }


    /**
     * 返回response状态码
     * @return int status code
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * 指定状态码和原因短语(可选)并返回response实例
     * @param  int $code
     * @param  string $reasonPhrase
     * @return static
     * @throws \InvalidArgumentException 
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        if (!is_int($code) || $code < 100 || $code > 599) {
            throw new \InvalidArgumentException('invalid http status code.');
        }
        $clone = clone $this;
        $clone->statusCode = $code;
        if (empty($reasonPhrase) && isset(self::$statusCodes[$code])) {
            $clone->reasonPhrase = self::$statusCodes[$code];
        }
        return $clone;

    }

    /**
     * 获得响应原因短语
     * @return [type] [description]
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }
}
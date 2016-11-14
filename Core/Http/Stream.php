<?php

namespace App\Core\Http;

use App\Core\Psr\StreamInterface;

class Stream implements StreamInterface
{

    private $stream;

    private $readable;

    private $writable;

    private $size;
    
    private $seekable;

    private $readWriteMap = array(
        'read' => array(
            'r' => true, 'r+' => true, 'w+' => true, 'a+' => true, 'x+' => true, 'c+' => true,
            'rb' => true, 'r+b' => true, 'w+b' => true, 'a+b' => true, 'x+b' => true, 'c+b' => true,
            'rt' => true, 'r+t' => true, 'w+t' => true, 'a+t' => true, 'x+t' => true, 'c+t' => true,
        ),
        'write' => array(
            'r+' => true, 'w' => true, 'w+' => true, 'a' => true, 'a+' => true, 'x' => true, 'x+' => true, 'c' => true, 'c+' => true,
            'r+b' => true, 'wb' => true, 'w+b' => true, 'ab' => true, 'a+b' => true, 'xb' => true, 'x+b' => true, 'cb' => true, 'c+b' => true,
            'r+t' => true, 'wt' => true, 'w+t' => true, 'at' => true, 'a+t' => true, 'xt' => true, 'x+t' => true, 'wt' => true, 'c+t' => true,
        ),
    );

    public function __construct($stream)
    {
        if (is_resource($stream) === false) {
            throw new \InvalidArgumentException('Stream must be a resource');
        }
        $this->stream = $stream;
        $metaData = stream_get_meta_data($stream);
        $this->readable = isset($this->readWriteMap['read'][$metaData['mode']]);
        $this->writable = isset($this->readWriteMap['write'][$metaData['mode']]);
        $this->seekable = $metaData['seekable'];
    }

    /**
     * 读取流的所有数据
     * 要求此方法不能抛出Exception
     * @return string
     */
    public function __toString()
    {
        try {
            $this->rewind();
            $contents = $this->getContents();
            return $contents;
        } catch (\RuntimeException $e) {
            return '';
        }
    }

    /**
     * 关闭流
     * @return void 
     */
    public function close()
    {
        if (isset($this->stream)) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
        }
        $this->detach();
    }

    /**
     * 释放流资源
     * @return resource|null 
     */
    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }
        $stream = $this->stream;
        $this->stream = null;
        $this->size = null;
        $this->readable = false;
        $this->writable = false;
        return $stream;
    }

    /**
     * 获得流的大小
     * @return [type] [description]
     */
    public function getSize()
    {
        if (!is_null($this->size)) {
            return $this->size;
        }

        if (!isset($this->stream)) {
            return null;
        }

        $stat = fstat($this->stream);
        $this->size = $stat['size'];
        return $this->size;
    }

    /**
     * 返回文件当前的读/写指针位置
     * @return int 
     * @throws \RuntimeException on error
     */
    public function tell()
    {
        if (isset($this->stream)) {
            if (($pos = ftell($this->stream)) !== false) {
                return $pos;
            }
        }
        throw new \RuntimeException('failed to get current position of the file read/write pointer.');
    }

    /**
     * 是否流指针已经到了结束位
     * @return bool
     */
    public function eof()
    {
        if (!isset($this->stream)) {
            return false;
        }
        $eof = feof($this->stream);
        return $eof;
    }

    /**
     * 否可以在当前流中定位。
     * @return bool
     */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * 设置流的位置
     * @param  int $offset stream offset
     * @param  int $whence 设置偏移规则
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->isSeekable()) {
            throw new \RuntimeException('stream is not seekable.');
        }
        $r = fseek($this->stream, $offset, $whence);
        if ($r !== 0) {
            throw new \RuntimeException('fail to seek in the stream.');
        }
    }

    /**
     * 将流指针定位到开始位置
     * @throws \RuntimeException on failure
     */
    public function rewind()
    {
        if (!$this->isSeekable()) {
            throw new \RuntimeException('stream is not seekable.');
        }
        if (!rewind($this->stream)) {
            throw new \RuntimeException('fail to rewind in the stream.');
        }
    }

    /**
     * 流是否可写
     * @return boolean
     */
    public function isWritable()
    {   
        return $this->writable;
    }

    /**
     * 往流写入数据
     * @param  string $string
     * @return int 实际写入数据长度
     * @throws \RuntimeException on failure.
     */
    public function write($string) 
    {
        if (!$this->isWritable()) {
            throw new \RuntimeException('stream is not writable.');
        }
        $len = fwrite($this->stream, $string);
        if ($len === false) {
            throw new \RuntimeException('fail to write data to the stream.');
        }
        //写入数据后需要等待getSize()重新计算
        $this->size = null;
        return $len;
    }

    /**
     * 流是否是可读的
     * @return boolean 
     */
    public function isReadable()
    {
        return $this->readable;
    }

    /**
     * 读取流数据
     * @param  int $length 
     * @return string
     * @throws \RuntimeException on failure
     */
    public function read($length)
    {
        if (!$this->isReadable()) {
            throw new \RuntimeException('stream is not readable.');
        }
        $str = fread($this->stream, $length);
        if ($str === false) {
            throw new \RuntimeException('fail to read in the stream.');
        }
        return $str;
    }

    /**
     * 返回stream中剩余数据
     * @return string 
     * @throws  \RuntimeException
     */
    public function getContents()
    {
        if (!$this->isReadable()) {
            throw new \RuntimeException('stream is not readable.');
        }
        $str = stream_get_contents($this->stream);
        if ($str === false) {
            throw new \RuntimeException();
        }
        return $str;
    }

    /**
     * 取得流的元数据
     * @param  string $key
     * @return array|mixed|null    
     */
    public function getMetadata($key = null)
    {
        //考虑到stream有些元信息会变，因此不缓存在成员变量中，而是每次进行新的获取
        $metaData = stream_get_meta_data($this->stream);
        if (!is_null($key)) {
            $r = isset($metaData[$key]) ? $metaData[$key]: null;
            return $r;
        }
        return $metaData;
    }
}
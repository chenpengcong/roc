<?php

namespace Roc\Models;

class Urls extends Model
{
    public function __construct()
    {
        $this->pdo = parent::connect('urls');
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }


    //将长url存入数据库
    public function save($longUrl)
    {
        $sql = 'INSERT INTO urls (long_url, created_time) VALUES (:long_url, :created_time)';
        try {
            $statement = $this->pdo->prepare($sql);
        } catch (\Exception $e) {
            return false;
        }
        $statement->execute(array(':long_url' => $longUrl, ':created_time' => time()));
        $insertId = $this->pdo->lastInsertId();
        return $insertId;
    }

    //从数据库取出长url
    public function getLongUrl($id)
    {
        $sql = 'SELECT long_url FROM urls WHERE id = :id';
        $statement = $this->pdo->prepare($sql);
        $statement->execute(array(':id' => $id));
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        return $result['long_url'];
    }

    //从缓存中取出短url
    public function getShortUrlByCache($longUrl)
    {
        $shortUrl = $this->redis->get($longUrl);
        if ($shortUrl !== false) {
            $this->redis->setTimeout($longUrl, 7200); 
        }
        return $shortUrl;
    }

    //将长url到短url的映射存入缓存
    public function setUrlMapCache($longUrl, $shortUrl, $expire = 7200)
    {
        $this->redis->setEx($longUrl, $expire, $shortUrl);
    }
    
}
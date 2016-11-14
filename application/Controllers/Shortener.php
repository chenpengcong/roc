<?php

namespace Roc\Controllers;
use Roc\Models\Urls;

use App\Core\Controller;

class Shortener extends Controller
{
    public function __construct(...$params)
    {
        parent::__construct(...$params);
        $this->urlsModel = new Urls();
    }

    public function shorten()
    {
        //获取表单数据
        $parsedBody = $this->request->getParsedBody();
        $longUrl = $parsedBody['long_url'];

        $body = $this->response->getBody();

        //优先从缓存中获取短url，此策略对常用的url起到多次转换得到相同短url(不是总得到相同短url)
        $shortUrl = $this->urlsModel->getShortUrlByCache($longUrl);
        if ($shortUrl !== false) {
            $body->write(json_encode(['err_code' => '0000', 'short_url' => $shortUrl]));
            return $this->response;
        }

        $insertId = $this->urlsModel->save($longUrl);
        
        if ($insertId === false) {
            $body->write(json_encode(['err_code' => '0001', 'err_msg' => 'db error, please try again.']));
        } else {
            //对长url在数据库中的id字段进行进制转换，作为短url
            $shortUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/' . toBase($insertId, 62);
            //redis存储longUrl到shortUrl的映射,默认存储7200s
            $this->urlsModel->setUrlMapCache($longUrl, $shortUrl);
            $body->write(json_encode(['err_code' => '0000', 'short_url' => $shortUrl]));      
        }

        return $this->response;
    }

    public function s2lAndRedirect($shortPath)
    {
        $id = to10($shortPath);
        $longUrl = $this->urlsModel->getLongUrl($id);
        return $this->response
            ->withStatus(302)
            ->withHeader('Location', $longUrl);
    }   
}
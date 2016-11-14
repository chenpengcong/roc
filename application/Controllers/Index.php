<?php

namespace Roc\Controllers;

use App\Core\Controller;

class Index extends Controller
{
    public function index()
    {
        $data['title'] = 'ROC';
        $data['desc'] = 'The simple, powerful URL shortener';
        return $this->view->render($this->response, 'index.php', $data);
    }
}
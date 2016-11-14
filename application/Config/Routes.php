<?php

namespace Roc\Config;

class Routes 
{
    public static $routes = [
        '/([0-9a-zA-Z]+)' => 'shortener/s2lAndRedirect/$1',
    ];
}
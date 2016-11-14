<?php

namespace App\Autoload;

class Autoloader
{
    protected $prefixs = [];

    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    //指定命名空间前缀对应的基目录
    public function addNamespace($prefix, $baseDir)
    {
        $prefix = trim($prefix, '\\') . '\\';

        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';

        if (isset($this->prefixs[$prefix]) === false) {
            $this->prefixs[$prefix] = array();
        }
        array_push($this->prefixs[$prefix], $baseDir);
    }

    public function loadClass($class)
    {
        $prefix = $class;

        //从类名右边开始截取
        while (($pos = strrpos($prefix, '\\')) !== false) {
            $prefix = substr($class, 0, $pos + 1);
            $relativeClass = substr($class, $pos + 1);
            $mappedFile = $this->loadMappedFile($prefix, $relativeClass);
            if ($mappedFile) {
                return $mappedFile;
            }
            $prefix = rtrim($prefix, '\\');
        }
        return false;


    }

    protected function loadMappedFile($prefix, $relativeClass)
    {
        if (empty($this->prefixs[$prefix])) {
            return false;
        }

        foreach ($this->prefixs[$prefix] as $baseDir) {
            $file = $baseDir
                . str_replace('\\', '/', $relativeClass)
                . '.php';
            if ($this->requireFile($file)) {
                return $file;
            }
        }
        return false;
    }

    protected function requireFile($file)
    {
        if (file_exists($file)) {
            require $file;
            return $file;
        }
        return false;
    }

    
}





<?php
// +----------------------------------------------------------------------
// | cloud-dns
// +----------------------------------------------------------------------
// | Copyright (c) 2023
// +----------------------------------------------------------------------
// | Licensed MIT
// +----------------------------------------------------------------------
// | Author: js
// +----------------------------------------------------------------------
// | Date: 2023-05-02
// +----------------------------------------------------------------------
// | Time: 下午 03:22
// +----------------------------------------------------------------------

namespace phpth\dns;

spl_autoload_register(function ($class_name) {
    if(stripos($class_name, 'phpth\dns') === false) {
        return false;
    }
    $class_name = str_ireplace('phpth\dns', '', $class_name);
    $class_name = str_replace('\\', '/', ltrim($class_name, '\\'));
    $path       = realpath(__DIR__);
    $file       = $path."/{$class_name}.php";
    if(file_exists($file)) {
        return require $file;
    } else {
        return false;
    }
});

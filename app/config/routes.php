<?php
defined('ROOT') OR exit('No direct script access allowed');

//默认模块
$route['default_module'] = 'def';

//默认控制器
$route['default_controller'] = 'app';

//默认方法
$route['default_action'] = 'index';

//404处理
$route['404_override'] = 'def/app/page404';

//是否把模块/控制器/方法名称里的横线"-"转换成下划线"_"，如： my-controller/my-method -> my_controller/my_method
$route['translate_uri_dashes'] = FALSE;

// 模式（URI） = 模块/控制器/action/参数 

//$route['product'] = 'def/product/index';
//$route['product/(:num)'] = 'def/product/detail/id=$1';
//$route['product/(:any)'] = 'def/product/detail/id=$1';
//$route['([a-z]+)/(\w+)'] = 'def/$1/$2';
//$route['(201\d)/([\w\d-_]*)/([\w\d-_]*)'] = 'y_$1/$2/$3';
//$route['login/(.+)'] = 'auth/login/login/$1';

// $route['product/([a-zA-Z]+)/edit/(\d+)'] = function ($product_type, $id)
// {
//     return 'def/product/edit/type=' . strtolower($product_type) . '/id=' . $id;
// };

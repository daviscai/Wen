<?php
//defined('ROOT') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_module'] = 'def';
$route['default_controller'] = 'app';
$route['404_override'] = 'def/app/page404';
$route['translate_uri_dashes'] = FALSE;

// 模式（URI） = 模块/控制器/action/参数 

$route['product'] = 'def/product/index';
$route['product/(:num)'] = 'def/product/detail/id=$1';
$route['product/(:any)'] = 'def/product/detail/id=$1';
//$route['([a-z]+)/(\w+)'] = 'def/$1/$2';

//$route['(201\d)/([\w\d-_]*)/([\w\d-_]*)'] = 'y_$1/$2/$3';
//$route['login/(.+)'] = 'auth/login/login/$1';

$route['product/([a-zA-Z]+)/edit/(\d+)'] = function ($product_type, $id)
{
    return 'def/product/edit/type=' . strtolower($product_type) . '/id=' . $id;
};

//
$route['goods']['put'] = 'def/product/insert';
$route['goods/(:num)']['DELETE'] = 'def/product/delete/id=$1';


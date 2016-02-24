<?php
/**
 * Wen, an open source application development framework for PHP
 *
 * @link http://www.wenzzz.com/
 * @copyright Copyright (c) 2015 Wen
 * @license http://opensource.org/licenses/MIT	MIT License
 */

namespace app\modules\def;

use app\core\controller\CoreController;

class ProductController extends CoreController
{
	public function index()
	{
		echo 'hello ci ';
	}

	public function detail()
	{
		var_dump($_GET);
		var_dump($_REQUEST);
	}

	public function edit()
	{
		var_dump($_GET);
	}

	public function insert()
	{
		var_dump($_GET);
	}
}
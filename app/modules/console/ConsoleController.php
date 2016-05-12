<?php
/**
 * Wen, an open source application development framework for PHP
 *
 * @link http://wen.wenzzz.com/
 * @copyright Copyright (c) 2016 Wen
 * @license http://opensource.org/licenses/MIT	MIT License
 */

namespace app\modules\console;

use app\core\base\Wen;
use app\core\controller\CoreController;
use app\core\helpers\StringHelper;
use app\core\helpers\HttpHelper;
use app\core\helpers\ArrayHelper;

class ConsoleController extends CoreController
{
	public function test()
	{
		$app = Wen::app();

		$app->logger->error('msg','test_console');
	}

	public function index()
	{

		$app = Wen::app();

		//写日志
		$app->logger->error('msg','test');

		//i18n多国语言本地化提示
		$t = Wen::t('Action not exists',['action'=>'getUser']);
		var_dump($t); // 输出： 请求方法：getUser 不存在！

		//字符串助手函数
		$a = StringHelper::starReplace('rrsdljslasffs');
		var_dump($a); //输出：rrs****ffs

		//获取表单数据，以做恶意代码过滤
		$a = HttpHelper::v('t');
		
		//缓存
		$this->cacheTest();

		//数据库操作
		$this->wenDb();

		//视图层数据绑定，没有采用模板语言，直接传递数据
		$data = array('a'=>'ttt');
		$this->render('post/list.html', $data);

	}

	private function cacheTest()
	{
		$cache = Wen::app()->cache;

		if($cache){
			$cache->set('t','ggsss4');
			$rs = $cache->get('t');
			var_dump($rs);
		}
	}

	private function  wenDb()
	{
		$db = Wen::app()->db;
		
		$rs = $db->insert('t_userinfo', array('name'=>'davis','title'=>'ssss','status'=>1))->execute();

		$rs = $db->select('u.id, u.name, u.title')->from('t_userinfo','u')->limit(0,10)->execute();
		$rs = $db->select('id, name, title')->from('t_userinfo')->limit(0,10)->execute();
		
		$rs = $db->selectCount('u.id')->from('t_userinfo','u')->where('u.id=:id',array(':id'=>4))->execute();
		var_dump($rs);

		$rs = $db->selectCount('u.id')->from('t_userinfo','u')->where('u.status=:status',array(':status'=>1))->execute();
		var_dump($rs);
		
		$rs = $db->selectOne('id, name, title')->from('t_userinfo')->where('status=:status',array(':status'=>1))->execute();
		var_dump($rs);

		$p = ' and 0<>(select count(*) from admin)  '; //sql注入语句失效，预编译防止注入
		$rs = $db->select('u.id, u.name')->from('t_userinfo','u')->where('u.name=:name',array(':name'=>$p))->execute();
		var_dump($rs);


		$rs = $db->update('t_userinfo',array('title'=>'mmmm'))->where('id=:id',array(':id'=>5))->execute();
		var_dump($rs);

		$rs = $db->delete('t_userinfo')->where('id=:id',array(':id'=>3))->execute();
		var_dump($rs);
	}


	public function page404()
	{
		echo 'page not found!';
	}
}
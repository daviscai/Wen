<?php
/**
 * Wen, an open source application development framework for PHP
 *
 * @link http://www.wenzzz.com/
 * @copyright Copyright (c) 2015 Wen
 * @license http://opensource.org/licenses/MIT	MIT License
 */

namespace app\modules\def;

use app\core\base\Wen;
use app\core\controller\CoreController;
use app\core\helpers\StringHelper;
use app\core\helpers\HttpHelper;
use app\core\helpers\ArrayHelper;

use Doctrine\DBAL\DriverManager;

class AppController extends CoreController
{
	public function index()
	{
		$app = Wen::app();

		//$app->logger->error('msg','test');
		//var_dump($app);

		//$t = Wen::t('test error',['abc'=>'999','user'=>'是是是']);
		//var_dump($t);

		//$a = StringHelper::starReplace('rrsdljslasffs');
		//var_dump($a);

		//$a = HttpHelper::v('t');
		//var_dump($a);
		//$this->doctrineDBAL();

		//$this->wenDb();

		//$this->cacheTest();

		$data = array('a'=>'ttt');

		$this->render('post/list.html', $data);

	}

	private function cacheTest()
	{
		$cache = Wen::app()->cache;

		$cache->set('t','ggss');
		$rs = $cache->get('t');
		var_dump($rs);
	}

	private function  wenDb()
	{
		$db = Wen::app()->db;
		//var_dump($db);
		//$rs = $db->selectOne('u.id')->from('t_userinfo','u')->limit(0,10)->execute();
		//$rs = $db->select('u.id,u.name')->from('t_userinfo','u')->limit(0,2)->execute();
		//$rs = $db->selectCount('u.id')->from('t_userinfo','u')->where('u.id=:id',array(':id'=>101))->execute();
		
		//$rs = $db->selectOne('u.id,u.name')->from('t_userinfo','u')
			//->where('u.id=:id',array(':id'=>10001))->groupby('u.id')->execute();
		//$rs = $db->select('u.id,u.name')->from('t_userinfo','u')
			//->where('u.id=:id',array(':id'=>10001))->groupby('u.id')->execute();

		// $p=' and 0<>(select count(*) from admin)  '; //sql注入语句失效，预编译防止注入
		// $rs = $db->select('u.id,u.name')->from('t_userinfo','u')
		// 	->where('u.name=:name',array(':name'=>$p))->execute();
		
		//$rs = $db->select()->from('pk_blog_post','u')->execute();
		
		//->leftJoin('Phone','p','u.id = p.user_id')->where('u.id>:id',array(':id'=>1))->limit(0,10)->groupby('u.id')->execute();

		//$rs = $db->insert('pk_blog_post',
				//array('user_id'=>5,'title'=>'ssss','slug'=>10,'status'=>1,'modified'=>date('y-m-d'),'content'=>'rrr','excerpt'=>'pp','comment_status'=>1,'comment_count'=>2)
				//)->execute();
		//var_dump($rs);

		//$rs = $db->update('pk_blog_post',array('title'=>'mmmm'))->where('id=:id',array(':id'=>5))->execute();
		//var_dump($rs);

		//$rs = $db->delete('pk_blog_post')->where('id=:id',array(':id'=>3))->execute();
		//var_dump($rs);
	}

	private function doctrineDBAL()
	{
		$config = new \Doctrine\DBAL\Configuration();
		//..
		$connectionParams = array(
		    'dbname' => 'dev_meituhd',
		    'user' => 'root',
		    'password' => 'cai1774',
		    'host' => 'localhost',
		    'driver' => 'pdo_mysql',
		);
		$conn = DriverManager::getConnection($connectionParams, $config);

		$articles = $conn->fetchAll('select * from t_userinfo');
		var_dump($articles);
		//$count = $conn->executeUpdate('UPDATE user SET username = ? WHERE id = ?', array('jwage', 1));

		//$conn->insert('user', array('username' => 'jwage'));

		//$conn->update('user', array('username' => 'jwage'), array('id' => 1));

		// $qb = $conn->createQueryBuilder()
		//     ->select('u.id')
		//     ->addSelect('p.id')
		//     ->from('users', 'u')
		//     ->leftJoin('u', 'phonenumbers', 'u.id = p.user_id');

		// $results = $qb->getQuery()->execute();
	}

	public function page404()
	{
		echo 'page not found!';
	}
}
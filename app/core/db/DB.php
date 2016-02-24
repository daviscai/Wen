<?php
/**
 * Wen, an open source application development framework for PHP
 *
 * @link http://www.wenzzz.com/
 * @copyright Copyright (c) 2015 Wen
 * @license http://opensource.org/licenses/MIT  MIT License
 */

namespace app\core\db;

use PDO;
use Exception;
use app\core\base\Wen;
use app\core\db\DBConnection;


/**
 * 多国语言国际化服务提供者，统一接口，通过set方法实例化具体的实现类，实现依赖注入
 * 
 * 这样做的好处：解耦，高层业务不依赖底层实现
 *
 *
 * @author WenXiong Cai <caiwxiong@qq.com>
 * @since 1.0
 */
class DB
{
    
    public $db;

    public $pdo;

    private $connect;

    private $selectSql;

    private $fromTable;

    private $leftJoin = [];

    private $where;

    private $whereParam = [];

    private $limit;

    private $orderBy;

    private $groupBy;

    private $isFetchOne = false;

    private $isCount = false;

    private $isUpdate = false;

    private $isInsert = false;

    private $isDelete = false;

    private $updateSql = '';

    private $updateFieldData = [];

    private $insertSql = '';

    private $insertFieldData = [];

    private $deleteSql = '';


    public function __construct($config)
    {
        $this->connect = new DBConnection($config);
        $this->connect->open();

        //$this->pdo = $this->connect->getMasterPdo();
    } 
 
    public function select($str='')
    {
        if(empty($str)){
            $str = '*';
        }
        $this->selectSql = 'SELECT '.$str.' ';
        return $this;
    }

    public function selectOne($str='')
    {
        $this->select($str);
        $this->isFetchOne = true;
        return $this;
    }

    public function selectCount()
    {
        $this->select($str);
        $this->isCount = true;
        return $this;
    }

    public function from($tableName, $as='t')
    {
        if(empty($tableName)){
            $this->fromTable = '';
        }else{
            $this->fromTable = ' FROM '.$tableName. ' as ' .$as.' ';
        }
        return $this;
    }

    public function leftJoin($tableName, $as='lt', $JoinCondition)
    {
        if(empty($tableName) || empty($JoinCondition)){
            $this->leftJoin[] = '';
        }else{
            $this->leftJoin[] = ' LEFT JOIN '.$tableName.' as '.$as.' on '.$JoinCondition.' ';
        }
        
        return $this;
    }

    public function where($sql, $param=[])
    {
        if(empty($sql)) {
            $this->where = '';
        }else{
            $this->where = ' WHERE '.$sql.' ';
        }
        $this->whereParam = $param;
        
        return $this;
    }

    public function limit($start=0, $num)
    {
        if(empty($num)) {
            $this->limit = '';
        }else{
            $this->limit = ' LIMIT '.$start.','.$num.' ';
        }
        return $this;
    }

    public function orderBy($orderBy)
    {
        if(empty($orderBy)){
            $this->orderBy = '';
        }else{
            $this->orderBy = ' ORDER BY '.$orderBy.' ';
        }
        return $this;
    }

    public function groupBy($groupBy)
    {
        if(empty($groupBy)){
            $this->groupBy = '';
        }else{
            $this->groupBy = ' GROUP BY '.$groupBy.' ';
        }
        return $this;
    }

    public function execute()
    {   
        if($this->isUpdate === true) {
            return $this->executeUpdate();
        }elseif($this->isInsert === true) {
            return $this->executeInsert();
        }elseif($this->isDelete === true){
            return $this->executeDelete();
        }else{
            return $this->executeSelect();
        }
    }

    private function executeSelect()
    {
        $pdo = $this->connect->getSlavePdo();
        $leftJoin = implode(' ', $this->leftJoin);
        $sql = $this->selectSql . $this->fromTable . $leftJoin . $this->where . $this->limit . $this->groupBy .$this->orderBy;

        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute($this->whereParam);

        if($this->isFetchOne){
            $rs = $sth->fetch(PDO::FETCH_ASSOC);
        }else{
            $ret = $sth->fetchAll(PDO::FETCH_ASSOC);
            if($this->isCount){
                $rs = empty($ret) ? 0 : count($ret);
            }else{
                $rs = $ret;
            }
        }
        
        $this->clearParameters();

        return $rs;
    }

    private function executeUpdate()
    {
        if(empty($this->where)){
            return false;
        }
        $pdo = $this->connect->getMasterPdo();
        try {
            $pdo->setAttribute( PDO::ATTR_AUTOCOMMIT, 0 );
            $pdo->beginTransaction();
            
            $holderData = array_merge($this->whereParam, $this->updateFieldData);

            $sql = $this->updateSql . $this->where;

            $st = $pdo->prepare($sql);
            $st->execute( $holderData );

            $pdo->commit();
            $pdo->setAttribute( PDO::ATTR_AUTOCOMMIT, 1 );

            $this->clearParameters();
            return true;

        } catch(PDOException $e) {
            $pdo->rollBack();
            $this->clearParameters();
            return false;
        }
    }

    private function executeInsert()
    {
        $pdo = $this->connect->getMasterPdo();
        try {
            $pdo->setAttribute( PDO::ATTR_AUTOCOMMIT, 0 );
            $pdo->beginTransaction();
            
            $st = $pdo->prepare($this->insertSql);
            $st->execute( $this->insertFieldData );

            $pdo->commit();
            $pdo->setAttribute( PDO::ATTR_AUTOCOMMIT, 1 );

            $this->clearParameters();
            return true;

        } catch(PDOException $e) {
            $pdo->rollBack();
            $this->clearParameters();
            return false;
            //throw $e;
        }
    }

    private function executeDelete()
    {
        if(empty($this->where)){
            return false;
        }
        $pdo = $this->connect->getMasterPdo();
        try {
            $pdo->setAttribute( PDO::ATTR_AUTOCOMMIT, 0 );
            $pdo->beginTransaction();
            
            $sql = $this->deleteSql . $this->where;
            $st = $pdo->prepare($sql);
            $st->execute( $this->whereParam );

            $pdo->commit();
            $pdo->setAttribute( PDO::ATTR_AUTOCOMMIT, 1 );

            $this->clearParameters();
            return true;

        } catch(PDOException $e) {
            $pdo->rollBack();
            $this->clearParameters();
            return false;
            //throw $e;
        }
    }

    public function insert($tableName, $data=[])
    {
        $this->isInsert = true;

        if(empty($data)){
            return $this;
        }
            
        $dataKeyArr = array_keys($data);
        $field = implode(',', $dataKeyArr);

        $holderArr = [];
        $holderData = [];
        foreach ($data as $_field => $val) {
            $holderArr[] = ':'.$_field;
            $holderData[':'.$_field] = $val;
        }
        $placeholder = implode(',', $holderArr);

        $sql = 'INSERT INTO '.$tableName.'( '.$field.' ) VALUES( '.$placeholder.' )' ;
        $this->insertSql = $sql;
        $this->insertFieldData = $holderData;
        return $this;
    }

    public function update($tableName, $data=[])
    {
        $this->isUpdate = true;

        $holderArr = [];
        $holderData = [];
        foreach ($data as $_field => $val) {
            $holderArr[] = $_field . '=:'.$_field;
            $holderData[':'.$_field] = $val;
        }
        $placeholder = implode(',', $holderArr);

        $sql = 'UPDATE '.$tableName.' SET '.$placeholder.' ' ;

        $this->updateSql = $sql;
        $this->updateFieldData = $holderData;
        return $this;
    }

    public function delete($tableName)
    {
        $this->isDelete = true;
        $sql = 'DELETE FROM '.$tableName.' ';
        $this->deleteSql =  $sql;
        return $this;
    }

    private function clearParameters()
    {
        $this->isFetchOne = false;
        $this->selectSql  = '';
        $this->fromTable  = '';
        $this->leftJoin   = [];
        $this->where      = '';
        $this->whereParam = [];
        $this->limit      = '';
        $this->orderBy    = '';
        $this->groupBy    = '';
        $this->isInsert   = false;
        $this->isUpdate   = false;
        $this->isDelete   = false;
        $this->isCount    = false;
        $this->updateSql  = '';
        $this->updateFieldData = [];
        $this->insertSql  = '';
        $this->insertFieldData = [];
        $this->deleteSql  = '';
    }

}
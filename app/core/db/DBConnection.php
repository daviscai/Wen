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
use \app\core\base\Wen;


/**
 * 多国语言国际化服务提供者，统一接口，通过set方法实例化具体的实现类，实现依赖注入
 * 
 * 这样做的好处：解耦，高层业务不依赖底层实现
 *
 *
 * @author WenXiong Cai <caiwxiong@qq.com>
 * @since 1.0
 */
class DBConnection
{
    /**
     * @var string the Data Source Name, or DSN, contains the information required to connect to the database.
     * Please refer to the [PHP manual](http://www.php.net/manual/en/function.PDO-construct.php) on
     * the format of the DSN string.
     *
     * For [SQLite](http://php.net/manual/en/ref.pdo-sqlite.connection.php) you may use a path alias
     * for specifying the database path, e.g. `sqlite:@app/data/db.sql`.
     *
     * @see charset
     */
    public $dsn;

    /**
     * @var string the username for establishing DB connection. Defaults to `null` meaning no username to use.
     */
    public $username;

    /**
     * @var string the password for establishing DB connection. Defaults to `null` meaning no password to use.
     */
    public $password;

    /**
     * @var array PDO attributes (name => value) that should be set when calling [[open()]]
     * to establish a DB connection. Please refer to the
     * [PHP manual](http://www.php.net/manual/en/function.PDO-setAttribute.php) for
     * details about available attributes.
     */
    public $attributes;

    /**
     * @var PDO the PHP PDO instance associated with this DB connection.
     * This property is mainly managed by [[open()]] and [[close()]] methods.
     * When a DB connection is active, this property will represent a PDO instance;
     * otherwise, it will be null.
     */
    public $pdo;

    /**
     * @var string the charset used for database connection. The property is only used
     * for MySQL, PostgreSQL and CUBRID databases. Defaults to null, meaning using default charset
     * as specified by the database.
     *
     * Note that if you're using GBK or BIG5 then it's highly recommended to
     * specify charset via DSN like 'mysql:dbname=mydatabase;host=127.0.0.1;charset=GBK;'.
     */
    public $charset;

    /**
     * @var boolean whether to turn on prepare emulation. Defaults to false, meaning PDO
     * will use the native prepare support if available. For some databases (such as MySQL),
     * this may need to be set true so that PDO can emulate the prepare support to bypass
     * the buggy native prepare support.
     * The default value is null, which means the PDO ATTR_EMULATE_PREPARES value will not be changed.
     */
    public $emulatePrepare;

    /**
     * @var string the common prefix or suffix for table names. If a table name is given
     * as `{{%TableName}}`, then the percentage character `%` will be replaced with this
     * property value. For example, `{{%post}}` becomes `{{tbl_post}}`.
     */
    public $tablePrefix = '';

    /**
     * @var string Custom PDO wrapper class. If not set, it will use "PDO" or "yii\db\mssql\PDO" when MSSQL is used.
     */
    public $pdoClass;

    /**
     * @var Cache|string the cache object or the ID of the cache application component that is used to store
     * the health status of the DB servers specified in [[masters]] and [[slaves]].
     * This is used only when read/write splitting is enabled or [[masters]] is not empty.
     */
    public $serverStatusCache = 'cache';

    /**
     * @var integer the retry interval in seconds for dead servers listed in [[masters]] and [[slaves]].
     * This is used together with [[serverStatusCache]].
     */
    public $serverRetryInterval = 600;

    /**
     * @var boolean whether to enable read/write splitting by using [[slaves]] to read data.
     * Note that if [[slaves]] is empty, read/write splitting will NOT be enabled no matter what value this property takes.
     */
    public $enableSlaves = true;

    /**
     * @var array list of slave connection configurations. Each configuration is used to create a slave DB connection.
     * When [[enableSlaves]] is true, one of these configurations will be chosen and used to create a DB connection
     * for performing read queries only.
     * @see enableSlaves
     * @see slaveConfig
     */
    public $slaves = [];

    /**
     * @var array the configuration that should be merged with every slave configuration listed in [[slaves]].
     * For example,
     *
     * ```php
     * [
     *     'username' => 'slave',
     *     'password' => 'slave',
     *     'attributes' => [
     *         // use a smaller connection timeout
     *         PDO::ATTR_TIMEOUT => 10,
     *     ],
     * ]
     * ```
     */
    public $slaveConfig = [];

    /**
     * @var array list of master connection configurations. Each configuration is used to create a master DB connection.
     * When [[open()]] is called, one of these configurations will be chosen and used to create a DB connection
     * which will be used by this object.
     * Note that when this property is not empty, the connection setting (e.g. "dsn", "username") of this object will
     * be ignored.
     * @see masterConfig
     */
    public $masters = [];

    /**
     * @var array the configuration that should be merged with every master configuration listed in [[masters]].
     * For example,
     *
     * ```php
     * [
     *     'username' => 'master',
     *     'password' => 'master',
     *     'attributes' => [
     *         // use a smaller connection timeout
     *         PDO::ATTR_TIMEOUT => 10,
     *     ],
     * ]
     * ```
     */
    public $masterConfig = [];

    /**
     * @var Transaction the currently active transaction
     */
    private $_transaction;

    /**
     * @var Schema the database schema
     */
    private $_schema;

    /**
     * @var string driver name
     */
    private $_driverName;

    /**
     * @var Connection the currently active slave connection
     */
    private $_slave = false;

    /**
     * @var array query cache parameters for the [[cache()]] calls
     */
    private $_queryCacheInfo = [];


    public function __construct($config=[])
    {
       Wen::configure($this,$config);
    } 

    /**
     * Establishes a DB connection.
     * It does nothing if a DB connection has already been established.
     * @throws Exception if connection fails
     */
    public function open()
    {
        if ($this->pdo !== null) {
            return;
        }

        if (!empty($this->masters)) {
            $db = $this->openFromPool($this->masters, $this->masterConfig);
            if ($db !== null) {
                $this->pdo = $db->pdo;
                return;
            } else {
                throw new Exception('None of the master DB servers is available.');
            }
        }

        if (empty($this->dsn)) {
            throw new Exception('Connection::dsn cannot be empty.');
        }
        $token = 'Opening DB connection: ' . $this->dsn;
        try {
            //Yii::info($token, __METHOD__);
            //Yii::beginProfile($token, __METHOD__);
            $this->pdo = $this->createPdoInstance();
            $this->initConnection();
            //Yii::endProfile($token, __METHOD__);
        } catch (\PDOException $e) {
            //Yii::endProfile($token, __METHOD__);
            throw new Exception($e->getMessage(), $e->errorInfo, (int) $e->getCode(), $e);
        }
    } 

    /**
     * Opens the connection to a server in the pool.
     * This method implements the load balancing among the given list of the servers.
     * @param array $pool the list of connection configurations in the server pool
     * @param array $sharedConfig the configuration common to those given in `$pool`.
     * @return Connection the opened DB connection, or null if no server is available
     * @throws InvalidConfigException if a configuration does not specify "dsn"
     */
    protected function openFromPool(array $pool, array $sharedConfig)
    {
        if (empty($pool)) {
            return null;
        }

        if (!isset($sharedConfig['class'])) {
            $sharedConfig['class'] = get_class($this);
        }

        //$cache = is_string($this->serverStatusCache) ? Yii::$app->get($this->serverStatusCache, false) : $this->serverStatusCache;

        shuffle($pool);

        foreach ($pool as $config) {
            $config = array_merge($sharedConfig, $config);

            if (empty($config['dsn'])) {
                throw new Exception('The "dsn" option must be specified.');
            }
            Wen::configure($this,$config);

            // $key = [__METHOD__, $config['dsn']];
            // if ($cache instanceof Cache && $cache->get($key)) {
            //     // should not try this dead server now
            //     continue;
            // }

            /* @var $db Connection */
            $db = Wen::createObject($config);

            try {
                $db->open();
                return $db;
            } catch (\Exception $e) {
                //Yii::warning("Connection ({$config['dsn']}) failed: " . $e->getMessage(), __METHOD__);
                // if ($cache instanceof Cache) {
                //     // mark this server as dead and only retry it after the specified interval
                //     $cache->set($key, 1, $this->serverRetryInterval);
                // }
            }
        }

        return null;
    }

     /**
     * Creates the PDO instance.
     * This method is called by [[open]] to establish a DB connection.
     * The default implementation will create a PHP PDO instance.
     * You may override this method if the default PDO needs to be adapted for certain DBMS.
     * @return PDO the pdo instance
     */
    protected function createPdoInstance()
    {
        $pdoClass = $this->pdoClass;
        if ($pdoClass === null) {
            $pdoClass = 'PDO';
            if ($this->_driverName !== null) {
                $driver = $this->_driverName;
            } elseif (($pos = strpos($this->dsn, ':')) !== false) {
                $driver = strtolower(substr($this->dsn, 0, $pos));
            }
            if (isset($driver)) {
                if ($driver === 'mssql' || $driver === 'dblib') {
                    $pdoClass = 'app\core\db\driver\mssql\PDO';
                } elseif ($driver === 'sqlsrv') {
                    $pdoClass = 'app\core\db\driver\mssql\SqlsrvPDO';
                }
            }
        }

        $dsn = $this->dsn;
        // if (strncmp('sqlite:@', $dsn, 8) === 0) {
        //     $dsn = 'sqlite:' . Yii::getAlias(substr($dsn, 7));
        // }
        
        try {
            return new $pdoClass($dsn, $this->username, $this->password, $this->attributes);
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    /**
     * Initializes the DB connection.
     * This method is invoked right after the DB connection is established.
     * The default implementation turns on `PDO::ATTR_EMULATE_PREPARES`
     * if [[emulatePrepare]] is true, and sets the database [[charset]] if it is not empty.
     * It then triggers an [[EVENT_AFTER_OPEN]] event.
     */
    protected function initConnection()
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ($this->emulatePrepare !== null && constant('PDO::ATTR_EMULATE_PREPARES')) {
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, $this->emulatePrepare);
        }
        if ($this->charset !== null && in_array($this->getDriverName(), ['pgsql', 'mysql', 'mysqli', 'cubrid'])) {
            $this->pdo->exec('SET NAMES ' . $this->pdo->quote($this->charset));
        }
        //$this->trigger(self::EVENT_AFTER_OPEN);
    }

     /**
     * Returns the name of the DB driver. Based on the the current [[dsn]], in case it was not set explicitly
     * by an end user.
     * @return string name of the DB driver
     */
    public function getDriverName()
    {
        if ($this->_driverName === null) {
            if (($pos = strpos($this->dsn, ':')) !== false) {
                $this->_driverName = strtolower(substr($this->dsn, 0, $pos));
            } else {
                $this->_driverName = strtolower($this->getSlavePdo()->getAttribute(PDO::ATTR_DRIVER_NAME));
            }
        }
        return $this->_driverName;
    }

    /**
     * Changes the current driver name.
     * @param string $driverName name of the DB driver
     */
    public function setDriverName($driverName)
    {
        $this->_driverName = strtolower($driverName);
    }


    /**
     * Returns the PDO instance for the currently active slave connection.
     * When [[enableSlaves]] is true, one of the slaves will be used for read queries, and its PDO instance
     * will be returned by this method.
     * @param boolean $fallbackToMaster whether to return a master PDO in case none of the slave connections is available.
     * @return PDO the PDO instance for the currently active slave connection. Null is returned if no slave connection
     * is available and `$fallbackToMaster` is false.
     */
    public function getSlavePdo($fallbackToMaster = true)
    {
        $db = $this->getSlave(false);
        if ($db === null) {
            return $fallbackToMaster ? $this->getMasterPdo() : null;
        } else {
            return $db->pdo;
        }
    }

    /**
     * Returns the PDO instance for the currently active master connection.
     * This method will open the master DB connection and then return [[pdo]].
     * @return PDO the PDO instance for the currently active master connection.
     */
    public function getMasterPdo()
    {
        $this->open();
        return $this->pdo;
    }

    /**
     * Returns the currently active slave connection.
     * If this method is called the first time, it will try to open a slave connection when [[enableSlaves]] is true.
     * @param boolean $fallbackToMaster whether to return a master connection in case there is no slave connection available.
     * @return Connection the currently active slave connection. Null is returned if there is slave available and
     * `$fallbackToMaster` is false.
     */
    public function getSlave($fallbackToMaster = true)
    {
        if (!$this->enableSlaves) {
            return $fallbackToMaster ? $this : null;
        }

        if ($this->_slave === false) {
            $this->_slave = $this->openFromPool($this->slaves, $this->slaveConfig);
        }

        return $this->_slave === null && $fallbackToMaster ? $this : $this->_slave;
    }

    /**
     * Executes the provided callback by using the master connection.
     *
     * This method is provided so that you can temporarily force using the master connection to perform
     * DB operations even if they are read queries. For example,
     *
     * ```php
     * $result = $db->useMaster(function ($db) {
     *     return $db->createCommand('SELECT * FROM user LIMIT 1')->queryOne();
     * });
     * ```
     *
     * @param callable $callback a PHP callable to be executed by this method. Its signature is
     * `function (Connection $db)`. Its return value will be returned by this method.
     * @return mixed the return value of the callback
     */
    public function useMaster(callable $callback)
    {
        $enableSlave = $this->enableSlaves;
        $this->enableSlaves = false;
        $result = call_user_func($callback, $this);
        $this->enableSlaves = $enableSlave;
        return $result;
    }

 
}
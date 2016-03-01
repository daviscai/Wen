<?php
/**
 * Wen, an open source application development framework for PHP
 *
 * @link http://www.wenzzz.com/
 * @copyright Copyright (c) 2015 Wen
 * @license http://opensource.org/licenses/MIT  MIT License
 */


namespace app\core\db\driver\mssql;

/**
 * This is an extension of the default PDO class of SQLSRV driver.
 * It provides workarounds for improperly implemented functionalities of the SQLSRV driver.
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class SqlsrvPDO extends \PDO
{
    /**
     * Returns value of the last inserted ID.
     *
     * SQLSRV driver implements [[PDO::lastInsertId()]] method but with a single peculiarity:
     * when `$sequence` value is a null or an empty string it returns an empty string.
     * But when parameter is not specified it works as expected and returns actual
     * last inserted ID (like the other PDO drivers).
     * @param string|null $sequence the sequence name. Defaults to null.
     * @return integer last inserted ID value.
     */
    public function lastInsertId($sequence = null)
    {
        return !$sequence ? parent::lastInsertId() : parent::lastInsertId($sequence);
    }
}
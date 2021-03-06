<?php
namespace DbFacade;

use \DbFacade\DatabaseFacadeInterface;
use \DbFacade\DatabaseFacadeAbstract;
use \DbFacade\AdoDbQueryResult;

use \Exception;
use \ADOConnection;


/**
 * ADOdb facade concretion.
 *
 * @package Facades
 * @author  Carsten Witt <carsten.witt@gmail.com>
 */
class AdoDbFacade extends DatabaseFacadeAbstract
implements DatabaseFacadeInterface
{



/**
 * Stores the original ADOConnection FetchMode to make it restorable
 * after using in this context.
 *
 * The restauration will not be done automatically;
 * simply call restoreAttributes() whenever you need it.
 */
    public $ado_fetch_mode_backup = 0;


/**
 * Accepts a ADOCOnnection instance.
 *
 * Additionally, takes a backup from its current configuration.
 *
 * @param \ADOConnection $ado ADODB Connection
 * @uses  backupConfiguration()
 */
    public function __construct( \ADOConnection $ado )
    {
        $this->connection = $ado;
        $this->backupConfiguration();
    }


//  ======  Typical DB methods  =========


/**
 * Escapes and quotes the given string for using in the database.
 * Should not be needed when using prepared statements.
 *
 * @param  string $str
 * @return string
 * @uses   $connection
 * @uses   \ADOConnection::qstr()
 */
    public function quote( $str )
    {
        return $this->connection->qstr($str);
    }


/**
 * Executes the given SQL string.
 *
 * The SQL is prepared first, then executed. For preparing, pass an associative
 * array with named parameters as keys and values.
 *
 * @param  string $sql     SQL string, optionally with named parameters
 * @param  array  $context The named parameters and values, default empty
 * @return AdoDbQueryResult
 *
 * @uses   $connection
 * @uses   setResult()
 * @uses   getResult()
 * @uses   prepare()
 * @uses   getErrorMsg()
 * @uses   \ADOConnection::Execute()
 * @uses   AdoDbQueryResult
 *
 * @throws \Exception
 */
    public function execute( $sql, $context = array() )
    {
        $stmt = $this->prepare($sql, $context);

        if ($result = $this->connection->Execute($stmt, $context)) {
            return $this->setResult(
                new AdoDbQueryResult($result))->getResult();
        }

        throw new \Exception("Syntax Error: $sql, Error: " . $this->getErrorMsg());
    }


/**
 * Returns the number of affected rows.
 *
 * @return int
 * @uses   $result
 * @uses   ADOConnection::Affected_Rows()
 */
    public function affectedRows() {
        return $this->connection->Affected_Rows();
    }


/**
 * Returns the ID of the last inserted object.
 *
 * @return int
 * @uses   $connection
 * @uses   ADOConnection::Insert_ID()
 */
    public function getInsertId()
    {
        return $this->connection->Insert_ID();
    }


//  ==============  Helpers  =======================


/**
 * Returns ADOConnections' last error message.
 *
 * @return string
 * @uses  $connection
 * @uses   ADOConnection::ErrorMsg()
 */
    public function getErrorMsg( )
    {
        return $this->connection->ErrorMsg();
    }


/**
 * Creates a backup from the current ADOConnection configuration.
 *
 * The restauration will not be done automatically;
 * simply call restoreAttributes() whenever you need it.
 *
 * @return object Fluent Interface
 * @uses   $connection
 * @uses   $ado_fetch_mode_backup
 * @uses   ADOConnection::$fetchMode
 * @uses   ADOConnection::SetFetchMode()
 */
    public function backupConfiguration() {
        $this->ado_fetch_mode_backup = (int) $this->connection->fetchMode;
        $this->connection->SetFetchMode(\ADODB_FETCH_ASSOC);
        return $this;
    }


/**
 * Applies the former ADOConnection configuration from the backup taken before.
 *
 * @return object Fluent Interface
 * @uses   $connection
 * @uses   $ado_fetch_mode_backup
 * @uses   ADOConnection::SetFetchMode()
 */
    public function restoreConfiguration() {
        $this->connection->SetFetchMode($this->ado_fetch_mode_backup);
        return $this;
    }


//  ===========  Additional Stuff  ===============


/**
 * Prepares and returns the given SQL string.
 *
 * Optionally, pass an associative array with named paramters as keys and values.
 *
 * @param  string $sql     SQL string, optionally with named parameters
 * @param  array  $context The named parameters and values, default empty
 * @return string
 *
 * @uses   $connection
 * @uses   ADOConnection::Param()
 * @uses   ADOConnection::Prepare()
 */
    public function prepare( $sql, $context = array() )
    {
        foreach($context as $param => $value) {
            $sql = str_replace($param, $this->connection->Param(substr($param, 1)), $sql);
        }
        return $this->connection->Prepare($sql);
    }



}

<?php

require_once dirname(__FILE__).'/Base.php';

/**
 * Sqlite Driver
 *
 * @package PicoDb\Driver
 * @author  Frederic Guillot
 */
class Sqlite extends Base
{
    /**
     * List of required settings options
     *
     * @access protected
     * @var array
     */
    protected $requiredAttributes = array('filename');

    /**
     * Create a new ADOdb connection
     *
     * @access public
     * @param  array   $settings
     */
    public function createConnection($db)
    {
        $this->adodb = $db;
    }

    /**
     * Enable foreign keys
     *
     * @access public
     */
    public function enableForeignKeys()
    {
        $this->adodb->execute('PRAGMA foreign_keys = ON');
    }

    /**
     * Disable foreign keys
     *
     * @access public
     */
    public function disableForeignKeys()
    {
        $this->adodb->execute('PRAGMA foreign_keys = OFF');
    }

    /**
     * Return true if the error code is a duplicate key
     *
     * @access public
     * @param  integer  $code
     * @return boolean
     */
    public function isDuplicateKeyError($code)
    {
        return $code == 23000;
    }

    /**
     * Escape identifier
     *
     * @access public
     * @param  string  $identifier
     * @return string
     */
    public function escape($identifier)
    {
        return $identifier;
    }

    /**
     * Cast value
     *
     * @access public
     * @param  string  $value
     * @param  string  $type
     * @return string
     */
    public function cast($value, $type, $option = NULL)
    {
        switch ($type) {
            default:
                return $value;
                break;
        }
    }

    /**
     * Current date value
     *
     * @access public
     * @return string
     */
    public function date()
    {
        return "date('now')";
    }
    
    /**
     * Current timestamp value
     *
     * @access public
     * @return string
     */
    public function timestamp()
    {
        return "datetime(CURRENT_TIMESTAMP, 'localtime')";
    }

    /**
     * Date difference
     *
     * @access public
     * @param  string  $diff
     * @param  string  $date1
     * @param  string  $date2
     * @return string
     */
    public function datediff($diff, $date1, $date2)
    {
        return '';
    }

    /**
     * Get non standard operator
     *
     * @access public
     * @param  string  $operator
     * @return string
     */
    public function getOperator($operator)
    {
        if ($operator === 'LIKE' || $operator === 'ILIKE') {
            return 'LIKE';
        }

        return '';
    }

    /**
     * Get last inserted id
     *
     * @access public
     * @return integer
     */
    public function getLastId()
    {
        return $this->adodb->insert_Id();
    }

    /**
     * Get current schema version
     *
     * @access public
     * @return integer
     */
    public function getSchemaVersion()
    {
        $rq = $this->adodb->prepare('PRAGMA user_version');
        $rq->execute();

        return (int) $rq->fetchColumn();
    }

    /**
     * Set current schema version
     *
     * @access public
     * @param  integer  $version
     */
    public function setSchemaVersion($version)
    {
        $this->adodb->execute('PRAGMA user_version='.$version);
    }

    /**
     * Upsert for a key/value variable
     *
     * @access public
     * @param  string  $table
     * @param  string  $keyColumn
     * @param  string  $valueColumn
     * @param  array   $dictionary
     * @return bool    False on failure
     */
    public function upsert($table, $keyColumn, $valueColumn, array $dictionary)
    {
        try {
            $this->adodb->beginTransaction();

            foreach ($dictionary as $key => $value) {

                $sql = sprintf(
                    'INSERT OR REPLACE INTO %s (%s, %s) VALUES (?, ?)',
                    $this->escape($table),
                    $this->escape($keyColumn),
                    $this->escape($valueColumn)
                );

                $rq = $this->adodb->prepare($sql);
                $rq->execute(array($key, $value));
            }

            $this->adodb->commit();

            return true;
        }
        catch (ADODB_Exception $e) {
            $this->adodb->rollBack();
            return false;
        }
    }

    /**
     * Run EXPLAIN command
     *
     * @access public
     * @param  string $sql
     * @param  array  $values
     * @return array
     */
    public function explain($sql, array $values)
    {
        return $this->getConnection()->query('EXPLAIN QUERY PLAN '.$this->getSqlFromPreparedStatement($sql, $values))->fetchAll(ADOdb::FETCH_ASSOC);
    }

    /**
     * Get database version
     *
     * @access public
     * @return array
     */
    public function getDatabaseVersion()
    {
        return $this->getConnection()->query('SELECT sqlite_version()')->fetchColumn();
    }
}

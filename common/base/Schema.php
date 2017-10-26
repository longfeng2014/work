<?php
/**
 * Schema
 *
 * @author amdy<bin.wang@pcstars.com>
 * @version $Id: Schema.php 4665 2015-03-19 06:54:24Z A0433 $
 */

namespace common\base\mysql;

use Yii;
use Yii\db;
use yii\db\mysql;

class Schema extends \yii\db\mysql\Schema
{

     /**
     * Gets the CREATE TABLE sql string.
     * @param TableSchema $table the table metadata
     * @return string $sql the result of 'SHOW CREATE TABLE'
     */
    public function getCreateTableSql($table)
    {
        return $this->getCreateTableSql($table);

        /*$row = $this->db->createCommand('SHOW CREATE TABLE ' . $this->quoteTableName($table->fullName))->queryOne();
        if (isset($row['Create Table'])) {
            $sql = $row['Create Table'];
        } else {
            $row = array_values($row);
            $sql = $row[1];
        }

        return $sql;
        */

    }
    
        /**
     * Gets the CREATE TABLE sql string.
     * @param TableSchema $table the table metadata
     * @return string $sql the result of 'SHOW CREATE TABLE'
     */
    public function findTableNames($schema = '')
     {
        return $this->findTableNames($schema = '');
        /* $sql = 'SHOW TABLES';
        if ($schema !== '') {
            $sql .= ' FROM ' . $this->quoteSimpleTableName($schema);
        }

        return $this->db->createCommand($sql)->queryColumn();
        */

     }


}

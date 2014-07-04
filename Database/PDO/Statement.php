<?php
/*!
 * Radium
 * Copyright 2011-2014 Jack Polgar
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Radium\Database\PDO;

use Radium\Database;
use Radium\Database\PDO;

/**
 * PDO Database wrapper statement class
 *
 * @package Radium
 * @subpackage Database
 * @since 0.1
 * @author Jack P. <nrx@nirix.net>
 * @copyright Copyright (c) Jack P.
 */
class Statement
{
    private $connectionName;
    private $statement;
    private $model;

    /**
     * PDO Statement constructor.
     *
     * @param $statement
     *
     * @return object
     */
    public function __construct($statement, $connectionName = 'default')
    {
        $this->statement = $statement;
        $this->connectionName = $connectionName;
        return $this;
    }

    /**
     * Sets the model for the rows to use.
     *
     * @param string $model
     *
     * @return object
     */
    public function model($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Fetches all the rows.
     *
     * @return array
     */
    public function fetchAll()
    {
        $result = $this->statement->fetchAll(\PDO::FETCH_ASSOC);
        $rows = array();

        if ($this->model !== null) {
            foreach ($result as $row) {
                $model = $this->model;
                $rows[] = new $model($row, false);
            }
        } else {
            foreach ($result as $row) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Fetches the next row from a result set.
     *
     * @param integer $style Fetch style
     * @param integer $orientation Cursor orientation
     * @param integer $offset Cursor offset
     *
     * @return object
     */
    public function fetch($style = \PDO::FETCH_ASSOC, $orientation = \PDO::FETCH_ORI_NEXT, $offset = 0)
    {
        if ($this->rowCount() == 0) {
            return false;
        }

        $result = $this->statement->fetch($style, $orientation, $offset);

        if ($this->model !== null) {
            $model = $this->model;
            return new $model($result, false);
        } else {
            return $result;
        }
    }

    /**
     * Binds a parameter to the specified variable name.
     *
     * @param mixed $param Parameter
     * @param mixed &$value Variable
     * @param integer $type Data type
     * @param integer $length Length
     * @param mixed $options Driver options
     *
     * @return object
     */
    public function bindParam($param, &$value, $type = \PDO::PARAM_STR, $length = 0, $options = array())
    {
        $this->statement->bindParam($param, $value, $type, $length, $options);
        return $this;
    }

    /**
     * Binds a value to a parameter.
     *
     * @param mixed $param Parameter
     * @param mixed $value Value
     * @param integer $type Data type
     *
     * @return object
     */
    public function bindValue($param, $value, $type = \PDO::PARAM_STR)
    {
        $this->statement->bindValue($param, $value, $type);
        return $this;
    }

    /**
     * Executes a prepared statement.
     *
     * @return object
     */
    public function exec()
    {
        $result = $this->statement->execute();

        if ($result) {
            return $this;
        } else {
            Database::connection($this->connectionName)->halt($this->statement->errorInfo());
        }
    }

    /**
     * Returns the number of rows affected by the last SQL statement.
     *
     * @return integer
     */
    public function rowCount()
    {
        return $this->statement->rowCount();
    }
}

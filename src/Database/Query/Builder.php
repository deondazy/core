<?php

namespace Deondazy\Core\Database\Query;

use PDOStatement;
use InvalidArgumentException;
use Deondazy\Core\Database\Connection;
use Deondazy\Core\Database\Query\AbstractBuilder;

class Builder extends AbstractBuilder
{
    /**
     * Prefix parentheses in the query.
     *
     * @var bool
     */
    protected $startParentheses = false;

    /**
     * Suffix parentheses in the query.
     *
     * @var bool
     */
    protected $endParentheses = false;

    /**
     * Run a select query on the database.
     *
     * @param string $columns
     *
     * @return $this
     */
    public function select(...$columns)
    {
        $this->select = (!empty($columns)) ? implode(', ', $columns) : '*';
        return $this;
    }

    /**
     * Set the where clause condition.
     *
     * @param string $clause
     * @param string|callable $column
     * @param mixed $operator
     * @param mixed $value
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    protected function setWhereClause($clause, $column, $operator = null, $value = null)
    {
        if (!is_string($column) && !is_callable($column)) {
            throw new InvalidArgumentException("First parameter must of type  String or Closure");
        }

        if (is_callable($column) && !is_string($column)) {
                $this->startParentheses = true;
                call_user_func($column, $this);
                $last = (count($this->where)) - 1;
                $this->where[$last]['endParentheses'] = true;
        } else {
            $operatorValue = (!is_null($operator) && in_array($operator, $this->operators)) ? $operator : '=';
            $this->where[] = [
                'clause'           => $clause,
                'column'           => trim($column),
                'operator'         => trim($operatorValue),
                'value'            => ($value) ? $value : $operator,
                'startParentheses' => $this->startParentheses,
                'endParentheses'   => $this->endParentheses
            ];
        }

        $this->startParentheses = false;
        return $this;
    }

    /**
     * Set the where clause for the query.
     *
     * @param string|callable $column
     * @param mixed $operator
     * @param mixed|null $value
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function where($column, $operator = null, $value = null)
    {
        return $this->setWhereClause('AND', $column, $operator, $value);
    }

    /**
     * Chain the where clause with an OR operator.
     *
     * @param string|callable $column
     * @param mixed $operator
     * @param mixed $value
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->setWhereClause('OR', $column, $operator, $value);
    }

    /**
     * Set the WHERE IS NULL and AND IS NULL conditions.
     *
     * @param string $column
     *
     * @return $this
     */
    public function whereNull($column)
    {
        return $this->setWhereClause('AND', $column, 'IS NULL');
    }

    /**
     * Set a raw where clause.
     *
     * @param string $whereClause
     *
     * @return $this
     */
    public function rawWhere(string $where, array $values = []): self
    {
        return $this->setWhereClause('', $where, null, $values);
    }


    /**
     * Fetch all the results from the database.
     *
     * @return array
     */
    public function get()
    {
        $this->composeSelect();
        $this->composeWhereClauseConditions();

        $this->statement = $this->connection->prepare($this->query);
        $this->statement->execute($this->bindings);

        if ($this->statement->rowCount() > 1) {
            return $this->statement->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $this->statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Dump the SQl prepared command.
     *
     * @return string
     */
    public function dump()
    {
        ob_start();
        $this->statement->debugDumpParams();
        $output = ob_get_contents() ?: '';
        ob_end_clean();
        return '<pre>' . htmlspecialchars($output) . '</pre>';
    }
}

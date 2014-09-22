<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 6/3/14
 * Time: 12:07 AM
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NilPortugues\SqlQueryBuilder\Builder;

use NilPortugues\SqlQueryBuilder\Builder\Syntax\WriterFactory;
use NilPortugues\SqlQueryBuilder\Manipulation\QueryInterface;
use NilPortugues\SqlQueryBuilder\Manipulation\QueryFactory;
use NilPortugues\SqlQueryBuilder\Manipulation\Select;
use NilPortugues\SqlQueryBuilder\Syntax\Column;
use NilPortugues\SqlQueryBuilder\Syntax\Table;
use NilPortugues\SqlQueryFormatter\Formatter;

/**
 * Class Generic
 * @package NilPortugues\SqlQueryBuilder\BuilderInterface
 */
class GenericBuilder implements BuilderInterface
{
    /**
     * @var \NilPortugues\SqlQueryBuilder\Builder\Syntax\PlaceholderWriter
     */
    private $placeholderWriter;

    /**
     * @var \NilPortugues\SqlQueryBuilder\Builder\Syntax\DeleteWriter
     */
    private $deleteWriter;

    /**
     * @var \NilPortugues\SqlQueryBuilder\Builder\Syntax\InsertWriter
     */
    private $insertWriter;

    /**
     * @var \NilPortugues\SqlQueryBuilder\Builder\Syntax\SelectWriter
     */
    private $selectWriter;

    /**
     * @var \NilPortugues\SqlQueryBuilder\Builder\Syntax\UpdateWriter
     */
    private $updateWriter;

    /**
     * @var \NilPortugues\SqlQueryBuilder\Builder\Syntax\WhereWriter
     */
    private $whereWriter;

    /**
     * @var \NilPortugues\SqlQueryBuilder\Builder\Syntax\IntersectWriter
     */
    private $intersectWriter;

    /**
     * @var \NilPortugues\SqlQueryBuilder\Builder\Syntax\MinusWriter
     */
    private $minusWriter;

    /**
     * @var \NilPortugues\SqlQueryBuilder\Builder\Syntax\UnionWriter
     */
    private $unionWriter;

    /**
     * @var \NilPortugues\SqlQueryBuilder\Builder\Syntax\UnionAllWriter
     */
    private $unionAllWriter;

    /**
     * @var \NilPortugues\SqlQueryFormatter\Formatter
     */
    private $sqlFormatter;

    /**
     * Creates writers.
     */
    public function __construct()
    {
        $this->placeholderWriter = WriterFactory::createPlaceholderWriter();

        $this->selectWriter = WriterFactory::createSelectWriter($this, $this->placeholderWriter);
        $this->updateWriter = WriterFactory::createUpdateWriter($this, $this->placeholderWriter);
        $this->deleteWriter = WriterFactory::createDeleteWriter($this, $this->placeholderWriter);
        $this->insertWriter = WriterFactory::createInsertWriter($this, $this->placeholderWriter);
        $this->whereWriter  = WriterFactory::createWhereWriter($this, $this->placeholderWriter);
        $this->intersectWriter = WriterFactory::createIntersectWriter($this);
        $this->minusWriter = WriterFactory::createMinusWriter($this);
        $this->unionWriter = WriterFactory::createUnionWriter($this);
        $this->unionAllWriter = WriterFactory::createUnionAllWriter($this);

        $this->sqlFormatter = new Formatter();
    }

    /**
     * @return \NilPortugues\SqlQueryBuilder\Manipulation\Select
     */
    public function select()
    {
        return QueryFactory::createSelect();
    }

    /**
     * @return \NilPortugues\SqlQueryBuilder\Manipulation\Insert
     */
    public function insert()
    {
        return QueryFactory::createInsert();
    }

    /**
     * @return \NilPortugues\SqlQueryBuilder\Manipulation\Update
     */
    public function update()
    {
        return QueryFactory::createUpdate();
    }

    /**
     * @return \NilPortugues\SqlQueryBuilder\Manipulation\Delete
     */
    public function delete()
    {
        return QueryFactory::createDelete();
    }

    /**
     * @return \NilPortugues\SqlQueryBuilder\Manipulation\Intersect
     */
    public function intersect()
    {
        return QueryFactory::createIntersect();
    }

    /**
     * @return \NilPortugues\SqlQueryBuilder\Manipulation\Union
     */
    public function union()
    {
        return QueryFactory::createUnion();
    }

    /**
     * @return \NilPortugues\SqlQueryBuilder\Manipulation\UnionAll
     */
    public function unionAll()
    {
        return QueryFactory::createUnionAll();
    }

    /**
     * @param \NilPortugues\SqlQueryBuilder\Manipulation\Select $first
     * @param \NilPortugues\SqlQueryBuilder\Manipulation\Select $second
     *
     * @return \NilPortugues\SqlQueryBuilder\Manipulation\Minus
     */
    public function minus(Select $first, Select $second)
    {
        return QueryFactory::createMinus($first, $second);
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->placeholderWriter->get();
    }

    /**
     * @param QueryInterface $query
     * @param bool           $resetPlaceholders
     *
     * @return string
     */
    public function write(QueryInterface $query, $resetPlaceholders = true)
    {
        if ($resetPlaceholders) {
            $this->placeholderWriter->reset();
        }

        $sql = '';

        switch ($query->partName()) {

            case 'SELECT':
                $sql = $this->selectWriter->writeSelect($query);
                break;

            case 'INSERT':
                $sql = $this->insertWriter->writeInsert($query);
                break;

            case 'UPDATE':
                $sql = $this->updateWriter->writeUpdate($query);
                break;

            case 'DELETE':
                $sql = $this->deleteWriter->writeDelete($query);
                break;

            case 'INTERSECT':
                $sql = $this->intersectWriter->writeIntersect($query);
                break;

            case 'MINUS':
                $sql = $this->minusWriter->writeMinus($query);
                break;

            case 'UNION':
                $sql = $this->unionWriter->writeUnion($query);
                break;

            case 'UNION ALL':
                $sql = $this->unionAllWriter->writeUnionAll($query);
                break;
        }

        return $sql;
    }

    /**
     * Returns a SQL string in a readable human-friendly format.
     *
     * @param QueryInterface $query
     *
     * @return string
     */
    public function writeFormatted(QueryInterface $query)
    {
        return $this->sqlFormatter->format($this->write($query));
    }

    /**
     * @param Select $select
     *
     * @return string
     */
    public function writeJoin(Select $select)
    {
        $sql = ($select->getJoinType()) ? "{$select->getJoinType()} " : "";
        $sql .= "JOIN ";
        $sql .= $this->writeTableWithAlias($select->getTable());
        $sql .= " ON ";
        $sql .= $this->whereWriter->writeWhere($select->getJoinCondition());

        return $sql;
    }

    /**
     * @param Table $table
     *
     * @return string
     */
    public function writeTableWithAlias(Table $table)
    {
        $alias  = ($table->getAlias()) ? " AS {$this->writeTableAlias($table->getAlias())}" : '';
        $schema = ($table->getSchema()) ? "{$table->getSchema()}." : '';

        return $schema.$this->writeTableName($table).$alias;
    }

    /**
     * @param $alias
     *
     * @return mixed
     */
    public function writeTableAlias($alias)
    {
        return $alias;
    }

    /**
     * @param string $alias
     *
     * @return string
     */
    public function writeColumnAlias($alias)
    {
        return "'{$alias}'";
    }

    /**
     * Returns the table name.
     *
     * @param Table $table
     *
     * @return string
     *
     */
    public function writeTableName(Table $table)
    {
        return $table->getName();
    }

    /**
     * @param Table $table
     *
     * @return string
     */
    public function writeTable(Table $table)
    {
        $schema = ($table->getSchema()) ? "{$table->getSchema()}." : '';

        return $schema.$this->writeTableName($table);
    }

    /**
     * @param array $values
     *
     * @return array
     */
    public function writeValues(array &$values)
    {
        array_walk(
            $values,
            array($this, 'writeEachPlaceholderValue')
        );

        return $values;
    }

    /**
     * @param $value
     */
    private function writeEachPlaceholderValue(&$value)
    {
        $value = $this->writePlaceholderValue($value);
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function writePlaceholderValue($value)
    {
        return $this->placeholderWriter->add($value);
    }

    /**
     * @param $operator
     *
     * @return string
     */
    public function writeConjunction($operator)
    {
        return ' '.$operator.' ';
    }

    /**
     * @return string
     */
    public function writeIsNull()
    {
        return " IS NULL";
    }

    /**
     * @return string
     */
    public function writeIsNotNull()
    {
        return " IS NOT NULL";
    }

    /**
     * Returns the column name.
     *
     * @param Column $column
     *
     * @return string
     */
    public function writeColumnName(Column $column)
    {
        $name = $column->getName();
        if ($name === Column::ALL) {
            return $this->writeColumnAll();
        }

        return $name;
    }

    /**
     * @return string
     */
    protected function writeColumnAll()
    {
        return '*';
    }
}

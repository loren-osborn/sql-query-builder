<?php

namespace NilPortugues\SqlQueryBuilder\Builder\Syntax;

use NilPortugues\SqlQueryBuilder\Builder\GenericBuilder;
use NilPortugues\SqlQueryBuilder\Manipulation\Select;
use NilPortugues\SqlQueryBuilder\Syntax\Column;
use NilPortugues\SqlQueryBuilder\Syntax\SyntaxFactory;
use NilPortugues\SqlQueryBuilder\Syntax\Where;

/**
 * Class WhereWriter
 * @package NilPortugues\SqlQueryBuilder\Builder\Syntax
 */
class WhereWriter
{
    /**
     * @var GenericBuilder
     */
    private $writer;

    /**
     * @var PlaceholderWriter
     */
    private $placeholderWriter;

    /**
     * @param GenericBuilder    $writer
     * @param PlaceholderWriter $placeholder
     */
    public function __construct(GenericBuilder $writer, PlaceholderWriter $placeholder)
    {
        $this->writer            = $writer;
        $this->placeholderWriter = $placeholder;

        $this->columnWriter = WriterFactory::createColumnWriter($writer, $placeholder);
    }

    /**
     * @param Where $where
     *
     * @return string
     */
    public function writeWhere(Where $where)
    {
        $clauses = $this->writeWhereClauses($where);
        $clauses = array_filter($clauses);

        if (empty($clauses)) {
            return '';
        }

        return implode($this->writer->writeConjunction($where->getConjunction()), $clauses);
    }

    /**
     * @param Where $where
     *
     * @return array
     */
    public function writeWhereClauses(Where $where)
    {
        $matches     = $this->writeWhereMatches($where);
        $ins         = $this->writeWhereIns($where);
        $notIns      = $this->writeWhereNotIns($where);
        $betweens    = $this->writeWhereBetweens($where);
        $comparisons = $this->writeWhereComparisons($where);
        $isNulls     = $this->writeWhereIsNulls($where);
        $isNotNulls  = $this->writeWhereIsNotNulls($where);
        $booleans    = $this->writeWhereBooleans($where);
        $subWheres   = $where->getSubWheres();

        array_walk(
            $subWheres,
            function (&$subWhere) {
                $subWhere = "({$this->writeWhere($subWhere)})";
            }
        );

        $clauses = array_merge(
            $matches,
            $ins,
            $notIns,
            $betweens,
            $comparisons,
            $isNulls,
            $isNotNulls,
            $booleans,
            $subWheres
        );

        return $clauses;
    }

    /**
     * @param Where $where
     *
     * @return array
     */
    protected function writeWhereMatches(Where $where)
    {
        $matches = array();

        foreach ($where->getMatches() as $values) {

            $columns = SyntaxFactory::createColumns($values['columns'], $where->getTable());

            $columnNames = array();
            foreach ($columns as &$column) {
                $columnNames[] = $this->columnWriter->writeColumn($column);
            }
            $columnNames = implode(', ', $columnNames);

            $columnValues = $values['values'];
            $columnValues = array(implode(" ", $columnValues));
            $columnValues = implode(", ", $this->writer->writeValues($columnValues));

            switch ($values['mode']) {
                case 'natural':
                    $matches[] = "(MATCH({$columnNames}) AGAINST({$columnValues}))";
                    break;

                case 'boolean':
                    $matches[] = "(MATCH({$columnNames}) AGAINST({$columnValues} IN BOOLEAN MODE))";
                    break;

                case 'query_expansion':
                    $matches[] = "(MATCH({$columnNames}) AGAINST({$columnValues} WITH QUERY EXPANSION))";
                    break;
            }
        }

        return $matches;
    }

    /**
     * @param Where $where
     *
     * @return array
     */
    protected function writeWhereIns(Where $where)
    {
        $ins = array();

        foreach ($where->getIns() as $column => $values) {

            $newColumn = array($column);
            $column    = SyntaxFactory::createColumn($newColumn, $where->getTable());
            $column    = $this->columnWriter->writeColumn($column);

            $values = $this->writer->writeValues($values);
            $values = implode(", ", $values);

            $ins[] = "({$column} IN ({$values}))";
        }

        return $ins;
    }

    /**
     * @param Where $where
     *
     * @return array
     */
    protected function writeWhereNotIns(Where $where)
    {
        $notIns = $where->getNotIns();

        foreach ($notIns as $column => &$values) {

            $newColumn = array($column);
            $column    = SyntaxFactory::createColumn($newColumn, $where->getTable());
            $column    = $this->columnWriter->writeColumn($column);

            $values = $this->writer->writeValues($values);
            $values = implode(", ", $values);

            $values = "({$column} NOT IN ({$values}))";
        }

        return $notIns;
    }

    /**
     * @param Where $where
     *
     * @return array
     */
    protected function writeWhereBetweens(Where $where)
    {
        $between = $where->getBetweens();
        array_walk(
            $between,
            function (&$between) {

                $between = "("
                    . $this->columnWriter->writeColumn($between["subject"])
                    . " BETWEEN "
                    . $this->writer->writePlaceholderValue($between["a"])
                    . " AND "
                    . $this->writer->writePlaceholderValue($between["b"])
                    . ")";
            }
        );

        return $between;
    }

    /**
     * @param Where $where
     *
     * @return array
     */
    protected function writeWhereComparisons(Where $where)
    {
        $comparisons = $where->getComparisons();
        array_walk(
            $comparisons,
            function (&$comparison) {

                $str = $this->writeWherePartialCondition($comparison["subject"]);
                $str .= $this->writer->writeConjunction($comparison["conjunction"]);
                $str .= $this->writeWherePartialCondition($comparison["target"]);

                $comparison = "($str)";
            }
        );

        return $comparisons;
    }

    /**
     * @param $subject
     *
     * @return string
     */
    protected function writeWherePartialCondition(&$subject)
    {
        if ($subject instanceof Column) {
            $str = $this->columnWriter->writeColumn($subject);

        } elseif ($subject instanceof Select) {

            $selectWriter = WriterFactory::createSelectWriter($this->writer, $this->placeholderWriter);
            $str          = '(' . $selectWriter->writeSelect($subject) . ')';

        } else {
            $str = $this->writer->writePlaceholderValue($subject);

        }

        return $str;
    }

    /**
     * @param Where $where
     *
     * @return array
     */
    protected function writeWhereIsNulls(Where $where)
    {
        $isNulls = $where->getNull();

        array_walk(
            $isNulls,
            function (&$isNull) {
                $isNull = "("
                    . $this->columnWriter->writeColumn($isNull["subject"])
                    . $this->writer->writeIsNull()
                    . ")";
            }
        );

        return $isNulls;
    }

    /**
     * @param Where $where
     *
     * @return array
     */
    protected function writeWhereIsNotNulls(Where $where)
    {
        $isNotNulls = $where->getNotNull();

        array_walk(
            $isNotNulls,
            function (&$isNotNull) {
                $isNotNull =
                    "(" . $this->columnWriter->writeColumn($isNotNull["subject"])
                    . $this->writer->writeIsNotNull() . ")";
            }
        );

        return $isNotNulls;
    }

    /**
     * @param Where $where
     *
     * @return array
     */
    protected function writeWhereBooleans(Where $where)
    {
        $booleans          = $where->getBooleans();
        $placeholderWriter = $this->placeholderWriter;

        array_walk(
            $booleans,
            function (&$boolean) use (&$placeholderWriter) {
                $column = $this->columnWriter->writeColumn($boolean["subject"]);
                $value  = $this->placeholderWriter->add($boolean["value"]);

                $boolean = "(ISNULL(" . $column . ", 0) = " . $value . ")";
            }
        );

        return $booleans;
    }
}

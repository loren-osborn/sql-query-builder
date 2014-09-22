<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 9/12/14
 * Time: 7:11 PM
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\SqlQueryBuilder\Manipulation;

use NilPortugues\SqlQueryBuilder\Syntax\QueryPartInterface;

/**
 * Class Intersect
 * @package NilPortugues\SqlQueryBuilder\Manipulation
 */
class Intersect implements QueryInterface
{
    const INTERSECT = 'INTERSECT';

    /**
     * @var array
     */
    private $intersect = array();

    /**
     * @return string
     */
    public function partName()
    {
        return 'INTERSECT';
    }

    /**
     * @param Select $select
     *
     * @return $this
     */
    public function add(Select $select)
    {
        $this->intersect[] = $select;

        return $this;
    }

    /**
     * @return array
     */
    public function getIntersects()
    {
        return $this->intersect;
    }

    /**
     * @throws QueryException
     * @return \NilPortugues\SqlQueryBuilder\Syntax\Table
     */
    public function getTable()
    {
        throw new QueryException('INTERSECT does not support tables');
    }

    /**
     * @throws QueryException
     * @return \NilPortugues\SqlQueryBuilder\Syntax\Where
     */
    public function getWhere()
    {
        throw new QueryException('INTERSECT does not support WHERE.');
    }

    /**
     * @throws QueryException
     * @return \NilPortugues\SqlQueryBuilder\Syntax\Where
     */
    public function where()
    {
        throw new QueryException('INTERSECT does not support the WHERE statement.');
    }
}

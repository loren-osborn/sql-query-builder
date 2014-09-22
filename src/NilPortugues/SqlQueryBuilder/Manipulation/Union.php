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
 * Class Union
 * @package NilPortugues\SqlQueryBuilder\Manipulation
 */
class Union implements QueryInterface
{
    const UNION = 'UNION';

    /**
     * @var array
     */
    private $union = array();

    /**
     * @return string
     */
    public function partName()
    {
        return 'UNION';
    }

    /**
     * @param Select $select
     *
     * @return $this
     */
    public function add(Select $select)
    {
        $this->union[] = $select;

        return $this;
    }

    /**
     * @return array
     */
    public function getUnions()
    {
        return $this->union;
    }

    /**
     * @throws QueryException
     * @return \NilPortugues\SqlQueryBuilder\Syntax\Table
     */
    public function getTable()
    {
        throw new QueryException('UNION does not support tables');
    }

    /**
     * @throws QueryException
     * @return \NilPortugues\SqlQueryBuilder\Syntax\Where
     */
    public function getWhere()
    {
        throw new QueryException('UNION does not support WHERE.');
    }

    /**
     * @throws QueryException
     * @return \NilPortugues\SqlQueryBuilder\Syntax\Where
     */
    public function where()
    {
        throw new QueryException('UNION does not support the WHERE statement.');
    }
}

<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 6/8/14
 * Time: 5:32 PM
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\SqlQueryBuilder\Syntax;

/**
 * Interface QueryPart
 * @package NilPortugues\SqlQueryBuilder\Syntax
 */
interface QueryPart
{
    /**
     * @return string
     */
    public function partName();
}

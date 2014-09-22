<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 9/12/14
 * Time: 7:15 PM
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\SqlQueryBuilder\Builder\Syntax;

use NilPortugues\SqlQueryBuilder\Builder\GenericBuilder;
use NilPortugues\SqlQueryBuilder\Manipulation\Intersect;

/**
 * Class IntersectWriter
 * @package NilPortugues\SqlQueryBuilder\Builder\Syntax
 */
class IntersectWriter
{
    /**
     * @var GenericBuilder
     */
    private $writer;

    /**
     * @param GenericBuilder $writer
     */
    public function __construct(GenericBuilder $writer)
    {
        $this->writer = $writer;
    }

    /**
     * @param Intersect $intersect
     *
     * @return string
     */
    public function writeIntersect(Intersect $intersect)
    {
        $intersectSelects = array();

        foreach ($intersect->getIntersects() as $select) {
            $intersectSelects[] = $this->writer->write($select);
        }

        return implode("\n".Intersect::INTERSECT."\n", $intersectSelects);
    }
}

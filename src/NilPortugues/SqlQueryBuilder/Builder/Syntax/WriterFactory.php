<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 6/12/14
 * Time: 2:11 AM
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\SqlQueryBuilder\Builder\Syntax;

use NilPortugues\SqlQueryBuilder\Builder\GenericBuilder;

/**
 * Class WriterFactory
 * @package NilPortugues\SqlQueryBuilder\Builder\Syntax
 */
final class WriterFactory
{
    /**
     * @param GenericBuilder    $writer
     * @param PlaceholderWriter $placeholderWriter
     *
     * @return ColumnWriter
     */
    public static function createColumnWriter(GenericBuilder $writer, PlaceholderWriter $placeholderWriter)
    {
        return new ColumnWriter($writer, $placeholderWriter);
    }

    /**
     * @param GenericBuilder    $writer
     * @param PlaceholderWriter $placeholderWriter
     *
     * @return WhereWriter
     */
    public static function createWhereWriter(GenericBuilder $writer, PlaceholderWriter $placeholderWriter)
    {
        return new WhereWriter($writer, $placeholderWriter);
    }

    /**
     * @param GenericBuilder    $writer
     * @param PlaceholderWriter $placeholderWriter
     *
     * @return SelectWriter
     */
    public static function createSelectWriter(GenericBuilder $writer, PlaceholderWriter $placeholderWriter)
    {
        return new SelectWriter($writer, $placeholderWriter);
    }

    /**
     * @param GenericBuilder    $writer
     * @param PlaceholderWriter $placeholderWriter
     *
     * @return InsertWriter
     */
    public static function createInsertWriter(GenericBuilder $writer, PlaceholderWriter $placeholderWriter)
    {
        return new InsertWriter($writer, $placeholderWriter);
    }

    /**
     * @param GenericBuilder    $writer
     * @param PlaceholderWriter $placeholderWriter
     *
     * @return UpdateWriter
     */
    public static function createUpdateWriter(GenericBuilder $writer, PlaceholderWriter $placeholderWriter)
    {
        return new UpdateWriter($writer, $placeholderWriter);
    }

    /**
     * @param GenericBuilder    $writer
     * @param PlaceholderWriter $placeholderWriter
     *
     * @return DeleteWriter
     */
    public static function createDeleteWriter(GenericBuilder $writer, PlaceholderWriter $placeholderWriter)
    {
        return new DeleteWriter($writer, $placeholderWriter);
    }

    /**
     * @return PlaceholderWriter
     */
    public static function createPlaceholderWriter()
    {
        return new PlaceholderWriter();
    }
}
